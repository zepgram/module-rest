<?php
/**
 * This file is part of Zepgram\Rest\Service
 *
 * @package    Zepgram\Rest\Service
 * @file       ApiProvider.php
 * @date       28 12 2021 23:15
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
use Zepgram\Rest\Model\ConfigRequest;
use Zepgram\Rest\Model\ConfigRequestFactory;
use Zepgram\Rest\Model\Parameters;
use Zepgram\Rest\Model\ParametersInterface;
use Zepgram\Rest\Model\ParametersInterfaceFactory;
use Zepgram\Rest\Model\RequestAdapter;

class ApiProvider implements ApiProviderInterface
{
    public function __construct(
        private ParametersInterfaceFactory $parametersFactory,
        private ConfigRequestFactory $configRequestFactory,
        private SerializerInterface $serializer,
        private RequestAdapter $requestAdapter,
        private string $configName = ConfigRequest::REST_API_CONFIG_DEFAULT,
        private string $method = Request::METHOD_GET,
        private bool $isVerify = true,
        private bool $isJsonRequest = true,
        private bool $isJsonResponse = true,
        private ?Validator $validator = null
    ) {}

    /**
     * {@inheritDoc}
     */
    public function build(DataObject $rawData, string $serviceName): ParametersInterface
    {
        $requestAdapterName = get_class($this->requestAdapter);
        if ($requestAdapterName === RequestAdapter::class) {
            throw new LogicException(__('RequestAdapter is missing and must be injected'));
        }
        if (!$this->requestAdapter instanceof RequestAdapter) {
            throw new LogicException(__($requestAdapterName . ' must be an instance of RequestAdapter'));
        }

        $this->requestAdapter->dispatch($rawData);
        $parameters = $this->buildParameters($serviceName);
        $this->validate($parameters);

        return $parameters;
    }

    /**
     * @param string $serviceName
     * @return ParametersInterface
     * @throws LogicException
     * @throws MissingBaseUriException
     */
    protected function buildParameters(string $serviceName): ParametersInterface
    {
        /** @var ParametersInterface $request */
        return $this->parametersFactory->create([
            'data' => [
                ParametersInterface::SERVICE_NAME => $serviceName,
                ParametersInterface::URI => $this->requestAdapter->getUri(),
                ParametersInterface::METHOD => $this->getMethod(),
                ParametersInterface::IS_JSON_REQUEST => $this->isJsonRequest(),
                ParametersInterface::IS_JSON_RESPONSE => $this->isJsonResponse(),
                ParametersInterface::CACHE_KEY => $this->requestAdapter->getCacheKey(),
                ParametersInterface::OPTIONS => $this->formatOptions(
                    $this->requestAdapter->getBody(),
                    $this->requestAdapter->getHeaders(),
                    $this->isVerify(),
                    $this->getMethod()
                ),
                ParametersInterface::CONFIG => $this->buildConfigRequest()
            ]
        ]);
    }

    /**
     * @return ConfigRequest
     * @throws LogicException
     * @throws MissingBaseUriException
     */
    protected function buildConfigRequest(): ConfigRequest
    {
        if ($this->getConfigName() === ConfigRequest::REST_API_CONFIG_DEFAULT) {
            throw new LogicException(__('ConfigName parameter is missing and must be injected'));
        }

        /** @var ConfigRequest $config */
        $config = $this->configRequestFactory->create([
            'configName' => $this->getConfigName()
        ]);

        if (empty($config->getBaseUri())) {
            throw new MissingBaseUriException(__('Base URI is missing for %1', $this->getConfigName()));
        }

        return $config;
    }

    /**
     * @param Parameters $parameters
     * @return void
     * @throws InvalidContractException
     */
    protected function validate(Parameters $parameters): void
    {
        if ($this->validator !== null) {
            try {
                $body = $parameters->getOptions()['body'] ?? '';
                $this->validator->validate($body);
            } catch (Exception $e) {
                throw new InvalidContractException(__($e->getMessage()), $e);
            }
        }
    }

    /**
     * @return string
     */
    private function getConfigName(): string
    {
        return $this->configName;
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
    private function isVerify(): bool
    {
        return $this->isVerify;
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
}
