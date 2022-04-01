<?php
/**
 * This file is part of Zepgram\Rest\Service
 *
 * @package    Zepgram\Rest\Service
 * @file       ApiPool.php
 * @date       28 12 2021 23:15
 *
 * @author     Benjamin Calef <zepgram@gmail.com>
 * @copyright  2021 Zepgram Copyright (c) (https://github.com/zepgram)
 * @license    MIT License
 **/

declare(strict_types=1);

namespace Zepgram\Rest\Service;

use Zepgram\Rest\Exception\Technical\LogicException;

class ApiPool implements ApiPoolInterface
{
    public function __construct(
        private array $apiProviders = []
    ) {}

    /**
     * {@inheritDoc}
     */
    public function getApiProvider(string $serviceName): ApiProviderInterface
    {
        if (isset($this->apiProviders[$serviceName])) {
            /** @var ApiProviderInterface $apiProvider */
            $apiProvider = $this->apiProviders[$serviceName];
            $apiProviderName = get_class($apiProvider);
            if (!$apiProvider instanceof ApiProviderInterface) {
                throw new LogicException(__($apiProviderName . ' must be an instance of ApiProviderInterface'));
            }
            
            return $apiProvider;
        };

        throw new LogicException(
            __('Api Provider class could not be found from service name ' . $serviceName)
        );
    }
}
