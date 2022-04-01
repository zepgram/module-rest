<?php
/**
 * This file is part of Zepgram\Rest\Service
 *
 * @package    Zepgram\Rest\Service
 * @file       ApiFactory.php
 * @date       04 11 2021 23:40
 *
 * @author     Benjamin Calef <zepgram@gmail.com>
 * @copyright  2021 Zepgram Copyright (c) (https://github.com/zepgram)
 * @license    MIT License
 **/

declare(strict_types=1);

namespace Zepgram\Rest\Service;

use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\ObjectManagerInterface;
use Zepgram\Rest\Exception\Technical\LogicException;
use Zepgram\Rest\Model\Cache\Identifier;

class ApiFactory
{
    private array $apiRequestRegistry;

    public function __construct(
        private ObjectManagerInterface $objectManager,
        private ApiPoolInterface $apiPool,
        private Identifier $identifier,
        private DataObjectFactory $dataObjectFactory
    ) {}

    /**
     * @param string $serviceName
     * @param array $rawData
     * @return ApiRequestInterface
     * @throws LogicException
     */
    public function get(string $serviceName, array $rawData = []): ApiRequestInterface
    {
        $registryKey = $this->identifier->getRegistryKey($serviceName, $rawData);
        if (isset($this->apiRequestRegistry[$registryKey])) {
            return $this->apiRequestRegistry[$registryKey];
        }

        return $this->apiRequestRegistry[$registryKey] = $this->create($serviceName, $rawData);
    }

    /**
     * @param string $serviceName
     * @param mixed $rawData
     * @return ApiRequestInterface
     * @throws LogicException
     */
    private function create(string $serviceName, array $rawData = []): ApiRequestInterface
    {
        return $this->objectManager->create(ApiRequestInterface::class, [
            'apiProvider' => $this->apiPool->getApiProvider($serviceName),
            'rawData' => $this->dataObjectFactory->create($rawData),
            'serviceName' => $serviceName
        ]);
    }
}
