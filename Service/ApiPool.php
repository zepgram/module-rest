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

use ReflectionClass;
use ReflectionException;
use Zepgram\Rest\Exception\Technical\LogicException;
use Zepgram\Rest\Model\RequestAdapter;

class ApiPool implements ApiPoolInterface
{
    public function __construct(
        private array $apiProviders = []
    ) {
    }

    /**
     * @inheirtDoc
     */
    public function execute(string $adapterName, array $rawData = []): mixed
    {
        try {
            $requestAdapterName = new ReflectionClass($adapterName);
        } catch (ReflectionException $e) {
            throw new LogicException(__($adapterName . ' must be an instance of RequestAdapter'), $e);
        }

        if (!$requestAdapterName->isSubclassOf(RequestAdapter::class)) {
            throw new LogicException(__($requestAdapterName . ' must be an instance of RequestAdapter'));
        }

        if (isset($this->apiProviders[$adapterName])) {
            $apiProvider = $this->apiProviders[$adapterName];
            $apiProviderName = get_class($apiProvider);
            if (!$apiProvider instanceof ApiProviderInterface) {
                throw new LogicException(__($apiProviderName . ' must be an instance of ApiProviderInterface'));
            }

            return $apiProvider->execute($rawData);
        }

        throw new LogicException(
            __('Api Provider class could not be found from request adapter ' . $adapterName)
        );
    }
}
