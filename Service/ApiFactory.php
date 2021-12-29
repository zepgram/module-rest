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
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var ApiPoolInterface */
    private $apiPool;

    /** @var Identifier */
    private $identifier;

    /** @var DataObjectFactory */
    private $dataObjectFactory;

    /** @var array */
    private $apiRequestRegistry;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param ApiPoolInterface $apiPool
     * @param Identifier $identifier
     * @param DataObjectFactory $dataObjectFactory
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ApiPoolInterface $apiPool,
        Identifier $identifier,
        DataObjectFactory $dataObjectFactory
    ) {
        $this->objectManager = $objectManager;
        $this->apiPool = $apiPool;
        $this->identifier = $identifier;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * @param string $serviceName
     * @param mixed $rawData
     * @return ApiRequestInterface
     * @throws LogicException
     */
    public function get(string $serviceName, array $rawData = []): ApiRequestInterface
    {
        $registryKey = $this->identifier->getRegistryKey($serviceName, $rawData);
        if (isset($this->apiRequestRegistry[$registryKey])) {
            return $this->apiRequestRegistry[$registryKey];
        }

        $apiRequest = $this->objectManager->create(ApiRequestInterface::class, [
            'apiProvider' => $this->apiPool->getApiProvider($serviceName),
            'rawData' => $this->dataObjectFactory->create($rawData),
            'serviceName' => $serviceName
        ]);

        return $this->apiRequestRegistry[$registryKey] = $apiRequest;
    }
}
