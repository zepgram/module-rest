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

class ApiFactory
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var DataObjectFactory */
    private $dataObjectFactory;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param DataObjectFactory $dataObjectFactory
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        DataObjectFactory $dataObjectFactory
    ) {
        $this->objectManager = $objectManager;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * @param string $className
     * @param mixed $data
     * @return ApiRequestInterface
     * @throws LogicException
     */
    public function create(string $className, array $data): ApiRequestInterface
    {
        $apiRequest = $this->objectManager->create($className, [
            'dispatchData' => $this->dataObjectFactory->create($data)
        ]);
        if (!$apiRequest instanceof ApiRequestInterface) {
            throw new LogicException(
                __('ApiRequest not instance of interface ' . ApiRequestInterface::class)
            );
        }

        return $apiRequest;
    }
}
