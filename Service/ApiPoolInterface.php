<?php
/**
 * This file is part of Zepgram\Rest\Service
 *
 * @package    Zepgram\Rest\Service
 * @file       ApiPoolInterface.php
 * @date       28 12 2021 23:15
 *
 * @author     Benjamin Calef <zepgram@gmail.com>
 * @copyright  2021 Zepgram Copyright (c) (https://github.com/zepgram)
 * @license    MIT License
 **/

declare(strict_types=1);

namespace Zepgram\Rest\Service;

use Zepgram\Rest\Exception\ExternalException;
use Zepgram\Rest\Exception\InternalException;
use Zepgram\Rest\Exception\Technical\InvalidContractException;
use Zepgram\Rest\Exception\Technical\LogicException;
use Zepgram\Rest\Exception\Technical\MissingBaseUriException;

interface ApiPoolInterface
{
    /**
     * @param string $adapterName
     * @param array $rawData
     * @return mixed
     * @throws InternalException
     * @throws InvalidContractException
     * @throws LogicException
     * @throws MissingBaseUriException
     * @throws ExternalException
     */
    public function execute(string $adapterName, array $rawData = []): mixed;
}
