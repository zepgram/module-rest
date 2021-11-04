<?php
/**
 * This file is part of Zepgram\Rest\Model
 *
 * @package    Zepgram\Rest\Model
 * @file       Parameters.php
 * @date       04 11 2021 23:28
 *
 * @author     Benjamin Calef <zepgram@gmail.com>
 * @copyright  2021 Zepgram Copyright (c) (https://github.com/zepgram)
 * @license    MIT License
 **/

declare(strict_types=1);

namespace Zepgram\Rest\Model;

use Magento\Framework\DataObject;

class Parameters extends DataObject implements ParametersInterface
{
    /**
     * {@inheritDoc}
     */
    public function getUri(): string
    {
        return $this->getData(self::URI);
    }

    /**
     * {@inheritDoc}
     */
    public function setUri(string $uri): ParametersInterface
    {
        return $this->setData(self::URI, $uri);
    }

    /**
     * {@inheritDoc}
     */
    public function getMethod(): string
    {
        return $this->getData(self::METHOD);
    }

    /**
     * {@inheritDoc}
     */
    public function setMethod(string $method): ParametersInterface
    {
        return $this->setData(self::METHOD, $method);
    }

    /**
     * {@inheritDoc}
     */
    public function getIsJsonRequest(): bool
    {
        return $this->getData(self::IS_JSON_REQUEST);
    }

    /**
     * {@inheritDoc}
     */
    public function setIsJsonRequest(bool $isJsonRequest): ParametersInterface
    {
        return $this->setData(self::IS_JSON_REQUEST, $isJsonRequest);
    }

    /**
     * {@inheritDoc}
     */
    public function getIsJsonResponse(): bool
    {
        return $this->getData(self::IS_JSON_RESPONSE);
    }

    /**
     * {@inheritDoc}
     */
    public function setIsJsonResponse(bool $isJsonResponse): ParametersInterface
    {
        return $this->setData(self::IS_JSON_RESPONSE, $isJsonResponse);
    }

    /**
     * {@inheritDoc}
     */
    public function getServiceName(): string
    {
        return $this->getData(self::SERVICE_NAME);
    }

    /**
     * {@inheritDoc}
     */
    public function setServiceName(string $serviceName): ParametersInterface
    {
        return $this->setData(self::SERVICE_NAME, $serviceName);
    }

    /**
     * {@inheritDoc}
     */
    public function getOptions(): array
    {
        return $this->getData(self::OPTIONS);
    }

    /**
     * {@inheritDoc}
     */
    public function setOptions(array $options): ParametersInterface
    {
        return $this->setData(self::OPTIONS, $options);
    }

    /**
     * {@inheritDoc}
     */
    public function getCacheKey(): ?string
    {
        return $this->getData(self::CACHE_KEY);
    }

    /**
     * {@inheritDoc}
     */
    public function setCacheKey(?string $cacheKey): ParametersInterface
    {
        return $this->setData(self::CACHE_KEY, $cacheKey);
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig(): ConfigRequest
    {
        return $this->getData(self::CONFIG);
    }

    /**
     * {@inheritDoc}
     */
    public function setConfig(ConfigRequest $configRequest): self
    {
        return $this->setData(self::CONFIG, $configRequest);
    }
}
