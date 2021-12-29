<?php
/**
 * This file is part of Zepgram\Rest\Service
 *
 * @package    Zepgram\Rest\Service
 * @file       ApiProviderInterface.php
 * @date       28 12 2021 23:15
 *
 * @author     Benjamin Calef <zepgram@gmail.com>
 * @copyright  2021 Zepgram Copyright (c) (https://github.com/zepgram)
 * @license    MIT License
 **/

declare(strict_types=1);

namespace Zepgram\Rest\Service;

use Magento\Framework\DataObject;
use Zepgram\Rest\Exception\Technical\InvalidContractException;
use Zepgram\Rest\Exception\Technical\LogicException;
use Zepgram\Rest\Exception\Technical\MissingBaseUriException;
use Zepgram\Rest\Model\ParametersInterface;

interface ApiProviderInterface
{
    /**
     * @param DataObject $rawData
     * @param string $serviceName
     * @return ParametersInterface
     * @throws InvalidContractException
     * @throws LogicException
     * @throws MissingBaseUriException
     */
    public function build(DataObject $rawData, string $serviceName): ParametersInterface;
}
