<?php
/**
 * This file is part of Zepgram\Rest\Service
 *
 * @package    Zepgram\Rest\Service
 * @file       ApiRequestInterface.php
 * @date       04 11 2021 23:40
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

interface ApiRequestInterface
{
    /**
     * @throws InvalidContractException
     * @throws LogicException
     * @throws MissingBaseUriException
     * @throws ExternalException
     * @throws InternalException
     * @return mixed
     */
    public function sendRequest(): mixed;
}
