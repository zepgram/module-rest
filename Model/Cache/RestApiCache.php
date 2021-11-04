<?php
/**
 * This file is part of Zepgram\Rest\Model\Cache
 *
 * @package    Zepgram\Rest\Model\Cache
 * @file       RestApiCache.php
 * @date       04 11 2021 23:28
 *
 * @author     Benjamin Calef <zepgram@gmail.com>
 * @copyright  2021 Zepgram Copyright (c) (https://github.com/zepgram)
 * @license    MIT License
 **/

declare(strict_types=1);

namespace Zepgram\Rest\Model\Cache;

use Magento\Framework\App\Cache\Type\FrontendPool;
use Magento\Framework\Cache\Frontend\Decorator\TagScope;

class RestApiCache extends TagScope
{
    /** @var string */
    public const TYPE_IDENTIFIER = 'rest_api_result';

    /** @var string */
    public const CACHE_TAG = 'REST_API_RESULT';

    /**
     * @param FrontendPool $cacheFrontendPool
     */
    public function __construct(FrontendPool $cacheFrontendPool)
    {
        parent::__construct($cacheFrontendPool->get(self::TYPE_IDENTIFIER), self::CACHE_TAG);
    }
}
