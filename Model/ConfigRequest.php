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
    public const XML_CONFIG_REST_API_SERVICE_BASE_URI = 'rest_api/%s/base_uri';

    /** @var string */
    public const XML_CONFIG_REST_API_SERVICE_TIMEOUT = 'rest_api/%s/timeout';

    /** @var string */
    public const XML_CONFIG_REST_API_SERVICE_IS_DEBUG = 'rest_api/%s/is_debug';

    /** @var string */
    public const XML_CONFIG_REST_API_SERVICE_CACHE_TTL = 'rest_api/%s/cache_ttl';

    /** @var string */
    public const XML_CONFIG_REST_API_GENERAL_IS_FORCE_DEBUG = 'rest_api/general/is_force_debug';

    /** @var string */
    public const XML_CONFIG_REST_API_GROUP_GENERAL = 'general';

    public function __construct(
        private ScopeConfigInterface $scopeConfig,
        private string $configName
    ) {
    }

    /**
     * @return string
     */
    public function getBaseUri(): string
    {
        return $this->scopeConfig->getValue(
            sprintf(self::XML_CONFIG_REST_API_SERVICE_BASE_URI, $this->configName)
        );
    }

    /**
     * @return int
     */
    public function getTimeout(): int
    {
        $timeout = $this->scopeConfig->getValue(
            sprintf(self::XML_CONFIG_REST_API_SERVICE_TIMEOUT, $this->configName)
        );
        if ($timeout === null) {
            $timeout = $this->scopeConfig->getValue(
                sprintf(self::XML_CONFIG_REST_API_SERVICE_TIMEOUT, self::XML_CONFIG_REST_API_GROUP_GENERAL)
            );
        }

        return (int)$timeout;
    }

    /**
     * @return int
     */
    public function getCacheLifetime(): int
    {
        $cacheLifetime = $this->scopeConfig->getValue(
            sprintf(self::XML_CONFIG_REST_API_SERVICE_CACHE_TTL, $this->configName)
        );
        if ($cacheLifetime === null) {
            $cacheLifetime = $this->scopeConfig->getValue(
                sprintf(self::XML_CONFIG_REST_API_SERVICE_CACHE_TTL, self::XML_CONFIG_REST_API_GROUP_GENERAL)
            );
        }

        return (int)$cacheLifetime;
    }

    /**
     * @return bool
     */
    public function isDebugEnabled(): bool
    {
        if ($this->scopeConfig->isSetFlag(self::XML_CONFIG_REST_API_GENERAL_IS_FORCE_DEBUG)) {
            return true;
        }

        $debugEnabled = $this->scopeConfig->getValue(
            sprintf(self::XML_CONFIG_REST_API_SERVICE_IS_DEBUG, $this->configName)
        );
        if ($debugEnabled === null) {
            $debugEnabled = $this->scopeConfig->getValue(
                sprintf(self::XML_CONFIG_REST_API_SERVICE_IS_DEBUG, self::XML_CONFIG_REST_API_GROUP_GENERAL)
            );
        }

        return (bool)$debugEnabled;
    }
}
