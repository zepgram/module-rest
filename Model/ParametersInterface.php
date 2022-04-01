<?php
/**
 * This file is part of Zepgram\Rest\Model
 *
 * @package    Zepgram\Rest\Model
 * @file       ParametersInterface.php
 * @date       04 11 2021 23:28
 *
 * @author     Benjamin Calef <zepgram@gmail.com>
 * @copyright  2021 Zepgram Copyright (c) (https://github.com/zepgram)
 * @license    MIT License
 **/

declare(strict_types=1);

namespace Zepgram\Rest\Model;

use Magento\Framework\DataObject;

/**
 * @method array toArray()
 */
interface ParametersInterface
{
    /** @var string */
    public const URI = 'uri';

    /** @var string */
    public const METHOD = 'method';

    /** @var string */
    public const IS_JSON_REQUEST = 'is_json_request';

    /** @var string */
    public const IS_JSON_RESPONSE = 'is_json_response';

    /** @var string */
    public const SERVICE_NAME = 'service_name';

    /** @var string */
    public const OPTIONS = 'options';

    /** @var string */
    public const CACHE_KEY = 'cache_key';

    /** @var string */
    public const CONFIG = 'config';

    /**
     * @return string
     */
    public function getServiceName(): string;

    /**
     * @param string $serviceName
     * @return $this
     */
    public function setServiceName(string $serviceName): self;

    /**
     * @return string
     */
    public function getUri(): string;

    /**
     * @param string $uri
     * @return $this
     */
    public function setUri(string $uri): self;

    /**
     * @return string
     */
    public function getMethod(): string;

    /**
     * @param string $method
     * @return $this
     */
    public function setMethod(string $method): self;

    /**
     * @return bool
     */
    public function getIsJsonRequest(): bool;

    /**
     * @param bool $isJsonRequest
     * @return $this
     */
    public function setIsJsonRequest(bool $isJsonRequest): self;

    /**
     * @return bool
     */
    public function getIsJsonResponse(): bool;

    /**
     * @param bool $isJsonResponse
     * @return $this
     */
    public function setIsJsonResponse(bool $isJsonResponse): self;

    /**
     * @return array
     */
    public function getOptions(): array;

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options): self;

    /**
     * @return string|null
     */
    public function getCacheKey(): ?string;

    /**
     * @param string|null $cacheKey
     * @return $this
     */
    public function setCacheKey(?string $cacheKey): self;

    /**
     * @return ConfigRequest
     */
    public function getConfig(): ConfigRequest;

    /**
     * @param ConfigRequest $configRequest
     * @return $this
     */
    public function setConfig(ConfigRequest $configRequest): self;
}
