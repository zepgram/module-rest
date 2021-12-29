<?php
/**
 * This file is part of Zepgram\Rest\Model
 *
 * @package    Zepgram\Rest\Model
 * @file       RequestAdapter.php
 * @date       04 11 2021 23:28
 *
 * @author     Benjamin Calef <zepgram@gmail.com>
 * @copyright  2021 Zepgram Copyright (c) (https://github.com/zepgram)
 * @license    MIT License
 **/

declare(strict_types=1);

namespace Zepgram\Rest\Model;

use Magento\Framework\DataObject;

abstract class RequestAdapter
{
    /** @var string */
    protected const HEADER_ACCEPT = 'Accept';

    /** @var string */
    protected const HEADER_CONTENT_TYPE = 'Content-Type';

    /** @var string */
    protected const CONTENT_JSON = 'application/json';

    /** @var string */
    protected const HEADER_ACCEPT_LANGUAGE = 'Accept-Language';

    /** @var string */
    protected const SERVICE_ENDPOINT = '';

    /**
     * @param DataObject $rawRawData
     */
    public function dispatch(DataObject $rawRawData): void //@codingStandardsIgnoreLine
    {
    }

    /**
     * @return array
     */
    public function getBody(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return [
            self::HEADER_ACCEPT => self::CONTENT_JSON,
            self::HEADER_CONTENT_TYPE => self::CONTENT_JSON
        ];
    }

    /**
     * @return string
     */
    public function getUri(): string
    {
        return static::SERVICE_ENDPOINT;
    }

    /**
     * @return string|null
     */
    public function getCacheKey(): ?string
    {
        return null;
    }
}
