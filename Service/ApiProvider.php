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

use Laminas\Http\Request;
use Zepgram\JsonSchema\Model\Validator;
use Zepgram\Rest\Model\ConfigRequest;
use Zepgram\Rest\Model\RequestAdapter;

class ApiProvider implements ApiProviderInterface
{
    public function __construct(
        private ApiBuilder $apiBuilder,
        private ?RequestAdapter $requestAdapter = null,
        private string $configName = ConfigRequest::XML_CONFIG_REST_API_GROUP_GENERAL,
        private string $method = Request::METHOD_GET,
        private bool $isVerify = true,
        private bool $isJsonRequest = true,
        private bool $isJsonResponse = true,
        private ?Validator $validator = null
    ) {
    }

    /**
     * @inheirtDoc
     */
    public function execute(array $rawData = []): mixed
    {
        return $this->apiBuilder->sendRequest($this, $rawData);
    }

    /**
     * @inheirtDoc
     */
    public function getRequestAdapter(): RequestAdapter
    {
        return $this->requestAdapter;
    }

    /**
     * @inheirtDoc
     */
    public function getConfigName(): string
    {
        return $this->configName;
    }

    /**
     * @inheirtDoc
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @inheirtDoc
     */
    public function isVerify(): bool
    {
        return $this->isVerify;
    }

    /**
     * @inheirtDoc
     */
    public function isJsonRequest(): bool
    {
        return $this->isJsonRequest;
    }

    /**
     * @inheirtDoc
     */
    public function isJsonResponse(): bool
    {
        return $this->isJsonResponse;
    }

    /**
     * @inheirtDoc
     */
    public function getValidator(): ?Validator
    {
        return $this->validator;
    }
}
