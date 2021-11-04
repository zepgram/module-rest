<?php
/**
 * This file is part of Zepgram\Rest\Service
 *
 * @package    Zepgram\Rest\Service
 * @file       ApiRequest.php
 * @date       04 11 2021 23:40
 *
 * @author     Benjamin Calef <zepgram@gmail.com>
 * @copyright  2021 Zepgram Copyright (c) (https://github.com/zepgram)
 * @license    MIT License
 **/

declare(strict_types=1);

namespace Zepgram\Rest\Service;

use Exception;
use Laminas\Http\Request;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\SerializerInterface;
use Zepgram\JsonSchema\Model\Validator;
use Zepgram\Rest\Exception\Technical\InvalidContractException;
use Zepgram\Rest\Exception\Technical\LogicException;
use Zepgram\Rest\Exception\Technical\MissingBaseUriException;
use Zepgram\Rest\Model\Cache\Identifier;
use Zepgram\Rest\Model\ConfigRequest;
use Zepgram\Rest\Model\ConfigRequestFactory;
use Zepgram\Rest\Model\HttpClient;
use Zepgram\Rest\Model\ParametersInterface;
use Zepgram\Rest\Model\ParametersInterfaceFactory;
use Zepgram\Rest\Model\RequestAdapter;
use Zepgram\Rest\Model\RequestAdapterInterface;

class ApiRequest implements ApiRequestInterface
{
    /** @var ParametersInterfaceFactory */
    private $parametersFactory;

    /** @var ConfigRequestFactory */
    private $configRequestFactory;

    /** @var HttpClient */
    private $httpClient;

    /** @var SerializerInterface */
    private $serializer;

    /** @var Identifier */
    private $identifier;

    /** @var RequestAdapterInterface */
    private $requestAdapter;

    /** @var DataObject */
    private $dispatchData;

    /** @var string */
    private $serviceName;

    /** @var string */
    private $method;

    /** @var bool */
    private $isVerify;

    /** @var bool */
    private $isJsonRequest;

    /** @var bool */
    private $isJsonResponse;

    /** @var Validator|null */
    private $validator;

    /** @var array */
    private $registry = [];

    /**
     * @param ParametersInterfaceFactory $parametersFactory
     * @param ConfigRequestFactory $configRequestFactory
     * @param HttpClient $httpClient
     * @param SerializerInterface $serializer
     * @param Identifier $identifier
     * @param RequestAdapter $requestAdapter
     * @param DataObject $dispatchData
     * @param string $serviceName
     * @param string $method
     * @param bool $isVerify
     * @param bool $isJsonRequest
     * @param bool $isJsonResponse
     * @param Validator|null $validator
     */
    public function __construct(
        ParametersInterfaceFactory $parametersFactory,
        ConfigRequestFactory $configRequestFactory,
        HttpClient $httpClient,
        SerializerInterface $serializer,
        Identifier $identifier,
        RequestAdapter $requestAdapter,
        DataObject $dispatchData,
        string $serviceName = ConfigRequest::REST_API_CONFIG_DEFAULT,
        string $method = Request::METHOD_GET,
        bool $isVerify = true,
        bool $isJsonRequest = true,
        bool $isJsonResponse = true,
        Validator $validator = null
    ) {
        $this->parametersFactory = $parametersFactory;
        $this->configRequestFactory = $configRequestFactory;
        $this->httpClient = $httpClient;
        $this->serializer = $serializer;
        $this->identifier = $identifier;
        $this->requestAdapter = $requestAdapter;
        $this->dispatchData = $dispatchData;
        $this->serviceName = $serviceName;
        $this->method = $method;
        $this->isVerify = $isVerify;
        $this->isJsonRequest = $isJsonRequest;
        $this->isJsonResponse = $isJsonResponse;
        $this->validator = $validator;
    }

    /**
     * {@inheritDoc}
     */
    public function send()
    {
        if ($this->getServiceName() === ConfigRequest::REST_API_CONFIG_DEFAULT) {
            throw new LogicException(__('ServiceName must be injected in di.xml'));
        }
        $requestAdapterName = get_class($this->requestAdapter);
        if ($requestAdapterName === RequestAdapter::class) {
            throw new LogicException(__('RequestAdapter is missing and must be injected in di.xml'));
        }
        if (!$this->requestAdapter instanceof RequestAdapter) {
            throw new LogicException(__($requestAdapterName . ' must be an instance of RequestAdapter'));
        }

        $extractedData = $this->recursiveExtract($this->dispatchData);
        $registryKey = $this->identifier->getRegistryKey(
            $this->getServiceName(),
            $this->serializer->serialize($extractedData)
        );
        if (isset($this->registry[$requestAdapterName][$registryKey])) {
            return $this->registry[$requestAdapterName][$registryKey];
        }

        $this->requestAdapter->dispatch($this->dispatchData);
        $parameters = $this->getParameters();

        if ($this->validator !== null) {
            try {
                $body = $parameters->getOptions()['body'] ?? '';
                $this->validator->validate($body);
            } catch (Exception $e) {
                throw new InvalidContractException(__($e->getMessage()), $e);
            }
        }

        $result = $this->httpClient->request($parameters);
        $this->registry[$requestAdapterName][$registryKey] = $result;

        return $result;
    }

    /**
     * @return ParametersInterface
     * @throws MissingBaseUriException
     */
    private function getParameters(): ParametersInterface
    {
        /** @var ParametersInterface $request */
        return $this->parametersFactory->create([
            'data' => [
                ParametersInterface::URI => $this->requestAdapter->getUri(),
                ParametersInterface::METHOD => $this->getMethod(),
                ParametersInterface::IS_JSON_REQUEST => $this->isJsonRequest(),
                ParametersInterface::IS_JSON_RESPONSE => $this->isJsonResponse(),
                ParametersInterface::SERVICE_NAME => $this->getServiceName(),
                ParametersInterface::CACHE_KEY => $this->requestAdapter->getCacheKey(),
                ParametersInterface::OPTIONS => $this->formatOptions(
                    $this->requestAdapter->getBody(),
                    $this->requestAdapter->getHeaders(),
                    $this->isVerify(),
                    $this->getMethod()
                ),
                ParametersInterface::CONFIG => $this->getConfig()
            ]
        ]);
    }

    /**
     * @return ConfigRequest
     * @throws MissingBaseUriException
     */
    private function getConfig(): ConfigRequest
    {
        /** @var ConfigRequest $config */
        $config = $this->configRequestFactory->create([
            'serviceName' => $this->getServiceName()
        ]);

        if (empty($config->getBaseUri())) {
            throw new MissingBaseUriException(__('Base URI is missing for %1', $this->getServiceName()));
        }

        return $config;
    }

    /**
     * @param array $body
     * @param array $headers
     * @param bool $verify
     * @param string $method
     * @return array
     */
    private function formatOptions(array $body, array $headers, bool $verify, string $method): array
    {
        if ($method === Request::METHOD_GET) {
            $options['query'] = $body;
        } else {
            $options['body'] = $this->isJsonRequest() ? $this->serializer->serialize($body) : $body;
        }
        $options['headers'] = $headers;
        $options['verify'] = $verify;
        $options['http_errors'] = true;

        return $options;
    }

    /**
     * @param $value
     * @return array|mixed
     */
    private function recursiveExtract($value)
    {
        if (is_object($value) && method_exists($value, 'toArray')) {
            return $this->recursiveExtract($value->toArray());
        }
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = $this->recursiveExtract($v);
            }
            return $value;
        }

        return $value;
    }

    /**
     * @return string
     */
    private function getServiceName(): string
    {
        return $this->serviceName;
    }

    /**
     * @return string
     */
    private function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return bool
     */
    private function isJsonRequest(): bool
    {
        return $this->isJsonRequest;
    }

    /**
     * @return bool
     */
    private function isJsonResponse(): bool
    {
        return $this->isJsonResponse;
    }

    /**
     * @return bool
     */
    private function isVerify(): bool
    {
        return $this->isVerify;
    }
}
