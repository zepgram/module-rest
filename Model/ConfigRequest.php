<?php
/**
 * This file is part of Zepgram\Rest\Model
 *
 * @package    Zepgram\Rest\Model
 * @file       ConfigRequest.php
 * @date       04 11 2021 23:28
 *
 * @author     Benjamin Calef <zepgram@gmail.com>
 * @copyright  2021 Zepgram Copyright (c) (https://github.com/zepgram)
 * @license    MIT License
 **/

declare(strict_types=1);

namespace Zepgram\Rest\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class ConfigRequest
{
    /** @var string */
    public const REST_API_SERVICE_BASE_URI = 'rest_api/%s/base_uri';

    /** @var string */
    public const REST_API_SERVICE_TIMEOUT = 'rest_api/%s/timeout';

    /** @var string */
    public const REST_API_SERVICE_IS_DEBUG = 'rest_api/%s/is_debug';

    /** @var string */
    public const REST_API_SERVICE_CACHE_TTL = 'rest_api/%s/cache_ttl';

    /** @var string */
    public const REST_API_DEFAULT_IS_FORCE_DEBUG = 'rest_api/default/is_force_debug';

    /** @var string */
    public const REST_API_CONFIG_DEFAULT = 'default';

    /** @var ScopeConfigInterface */
    private $scopeConfig;

    /** @var string */
    private $serviceName;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param string $serviceName
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        string $serviceName = self::REST_API_CONFIG_DEFAULT
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->serviceName = $serviceName;
    }

    /**
     * @return string
     */
    public function getBaseUri(): string
    {
        return $this->scopeConfig->getValue(
            sprintf(self::REST_API_SERVICE_BASE_URI, $this->serviceName)
        );
    }

    /**
     * @return int
     */
    public function getTimeout(): int
    {
        $timeout = $this->scopeConfig->getValue(
            sprintf(self::REST_API_SERVICE_TIMEOUT, $this->serviceName)
        );
        if ($timeout === null) {
            $timeout = $this->scopeConfig->getValue(
                sprintf(self::REST_API_SERVICE_TIMEOUT, self::REST_API_CONFIG_DEFAULT)
            );
        }

        return (int) $timeout;
    }

    /**
     * @return int
     */
    public function getCacheLifetime(): int
    {
        $cacheLifetime = $this->scopeConfig->getValue(
            sprintf(self::REST_API_SERVICE_CACHE_TTL, $this->serviceName)
        );
        if ($cacheLifetime === null) {
            $cacheLifetime = $this->scopeConfig->getValue(
                sprintf(self::REST_API_SERVICE_CACHE_TTL, self::REST_API_CONFIG_DEFAULT)
            );
        }

        return (int) $cacheLifetime;
    }

    /**
     * @return bool
     */
    public function isDebugEnabled(): bool
    {
        if ($this->scopeConfig->isSetFlag(self::REST_API_DEFAULT_IS_FORCE_DEBUG)) {
            return true;
        }

        $debugEnabled = $this->scopeConfig->getValue(
            sprintf(self::REST_API_SERVICE_IS_DEBUG, $this->serviceName)
        );
        if ($debugEnabled === null) {
            $debugEnabled = $this->scopeConfig->getValue(
                sprintf(self::REST_API_SERVICE_IS_DEBUG, self::REST_API_CONFIG_DEFAULT)
            );
        }

        return (bool) $debugEnabled;
    }
}
