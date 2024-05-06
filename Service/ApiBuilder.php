<?php
/**
 * This file is part of Zepgram\Rest\Service
 *
 * @package    Zepgram\Rest\Service
 * @file       ApiProvider.php
 * @date       18 02 2024 09:59
 *
 * @author     Benjamin Calef <zepgram@gmail.com>
 * @copyright  2024 Zepgram Copyright (c) (https://github.com/zepgram)
 * @license    MIT License
 **/

declare(strict_types=1);

namespace Zepgram\Rest\Service;

use Exception;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Zepgram\Rest\Exception\ExternalException;
use Zepgram\Rest\Exception\InternalException;
use Zepgram\Rest\Exception\Technical\InvalidContractException;
use Zepgram\Rest\Exception\Technical\LogicException;
use Zepgram\Rest\Exception\Technical\MissingBaseUriException;
use Zepgram\Rest\Model\Cache\Identifier;
use Zepgram\Rest\Model\ConfigRequest;
use Zepgram\Rest\Model\ConfigRequestFactory;
use Zepgram\Rest\Model\HttpClient;
use Zepgram\Rest\Model\Request;
use Zepgram\Rest\Model\RequestAdapter;
use Zepgram\Rest\Model\RequestInterface;
use Zepgram\Rest\Model\RequestInterfaceFactory;
use Zepgram\Rest\Model\AdapterNameResolver;

class ApiBuilder
{
    /** @var array */
    private $apiRequestRegistry;

    public function __construct(
        private RequestInterfaceFactory $requestFactory,
        private ConfigRequestFactory $configRequestFactory,
        private SerializerInterface $serializer,
        private Identifier $identifier,
        private AdapterNameResolver $adapterNameResolver,
        private Factory $dataObjectFactory,
        private EventManagerInterface $eventManager,
        private HttpClient $httpClient
    ) {
    }

    /**
     * @param ApiProviderInterface $apiProvider
     * @param array $rawData
     * @return mixed
     * @throws ExternalException
     * @throws InternalException
     * @throws InvalidContractException
     * @throws LogicException
     * @throws MissingBaseUriException
     */
    public function sendRequest(ApiProviderInterface $apiProvider, array $rawData): mixed
    {
        $adapterName = $this->adapterNameResolver->getAdapterName($apiProvider->getRequestAdapter()::class);
        $registryKey = $this->identifier->getRegistryKey($adapterName, $rawData);
        if (isset($this->apiRequestRegistry[$registryKey])) {
            return $this->apiRequestRegistry[$registryKey];
        }

        $rawDataObject = $this->dataObjectFactory->create($rawData);

        $this->eventManager->dispatch($adapterName . '_send_before', [
            'request_adapter' => $apiProvider->getRequestAdapter(),
            'api_provider' => $apiProvider,
            'raw_data' => $rawDataObject
        ]);

        $request = $this->buildRequest($adapterName, $apiProvider, $rawDataObject);
        $this->apiRequestRegistry[$registryKey] = $this->httpClient->request($request);

        $this->eventManager->dispatch($adapterName . '_send_after', [
            'request' => $request,
            'result' => $this->apiRequestRegistry[$registryKey]
        ]);

        return $this->apiRequestRegistry[$registryKey];
    }

    /**
     * @param string $adapterName
     * @param ApiProviderInterface $apiProvider
     * @param DataObject $rawData
     * @return RequestInterface
     * @throws InvalidContractException
     * @throws LogicException
     * @throws MissingBaseUriException
     */
    private function buildRequest(
        string $adapterName,
        ApiProviderInterface $apiProvider,
        DataObject $rawData
    ): RequestInterface {
        $requestAdapter = $apiProvider->getRequestAdapter();
        $requestAdapter->dispatch($rawData);
        /** @var RequestInterface $request */
        $request = $this->requestFactory->create([
            'data' => [
                RequestInterface::ADAPTER_NAME => $adapterName,
                RequestInterface::URI => $requestAdapter->getUri(),
                RequestInterface::METHOD => $apiProvider->getMethod(),
                RequestInterface::IS_JSON_REQUEST => $apiProvider->isJsonRequest(),
                RequestInterface::IS_JSON_RESPONSE => $apiProvider->isJsonResponse(),
                RequestInterface::CACHE_KEY => $requestAdapter->getCacheKey(),
                RequestInterface::OPTIONS => $this->getOptions($apiProvider, $requestAdapter),
                RequestInterface::CONFIG => $this->getConfig($apiProvider)
            ]
        ]);
        $this->validateRequest($apiProvider, $request);

        return $request;
    }

    /**
     * @param ApiProviderInterface $apiProvider
     * @param RequestAdapter $requestAdapter
     * @return array
     */
    private function getOptions(ApiProviderInterface $apiProvider, RequestAdapter $requestAdapter): array
    {
        $body = $requestAdapter->getBody();
        if ($apiProvider->getMethod() === \Laminas\Http\Request::METHOD_GET) {
            $options['query'] = $body;
        } else {
            $options['body'] = $apiProvider->isJsonRequest() ? $this->serializer->serialize($body) : $body;
        }
        $contentType = $requestAdapter->getHeaders()['Content-Type'] ?? null;
        if ($contentType === 'application/x-www-form-urlencoded') {
            $options['form_params'] = $body;
            unset($options['body'], $options['query']);
        }
        $options['headers'] = $requestAdapter->getHeaders();
        $options['verify'] = $apiProvider->isVerify();
        $options['http_errors'] = true;

        return $options;
    }

    /**
     * @param ApiProviderInterface $apiProvider
     * @return ConfigRequest
     * @throws LogicException
     * @throws MissingBaseUriException
     */
    private function getConfig(ApiProviderInterface $apiProvider): ConfigRequest
    {
        if ($apiProvider->getConfigName() === ConfigRequest::XML_CONFIG_REST_API_GROUP_GENERAL) {
            throw new LogicException(__('ConfigName parameter is missing and must be injected'));
        }

        /** @var ConfigRequest $config */
        $config = $this->configRequestFactory->create([
            'configName' => $apiProvider->getConfigName()
        ]);

        if (empty($config->getBaseUri())) {
            throw new MissingBaseUriException(__('Base URI is missing for %1', $apiProvider->getConfigName()));
        }

        return $config;
    }

    /**
     * @param ApiProviderInterface $apiProvider
     * @param Request $request
     * @return void
     * @throws InvalidContractException
     */
    private function validateRequest(ApiProviderInterface $apiProvider, Request $request): void
    {
        if ($apiProvider->getValidator() !== null) {
            try {
                $body = $request->getOptions()['body'] ?? '';
                $apiProvider->getValidator()->validate($body);
            } catch (Exception $e) {
                throw new InvalidContractException(__($e->getMessage()), $e);
            }
        }
    }
}
