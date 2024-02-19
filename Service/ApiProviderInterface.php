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

use Zepgram\JsonSchema\Model\Validator;
use Zepgram\Rest\Exception\ExternalException;
use Zepgram\Rest\Exception\InternalException;
use Zepgram\Rest\Exception\Technical\InvalidContractException;
use Zepgram\Rest\Exception\Technical\LogicException;
use Zepgram\Rest\Exception\Technical\MissingBaseUriException;
use Zepgram\Rest\Model\RequestAdapter;

interface ApiProviderInterface
{
    /**
     * @param array $rawData
     * @return mixed
     * @throws ExternalException
     * @throws InternalException
     * @throws InvalidContractException
     * @throws LogicException
     * @throws MissingBaseUriException
     */
    public function execute(array $rawData = []): mixed;

    /**
     * @return RequestAdapter
     */
    public function getRequestAdapter(): RequestAdapter;

    /**
     * @return string
     */
    public function getConfigName(): string;

    /**
     * @return string
     */
    public function getMethod(): string;

    /**
     * @return bool
     */
    public function isVerify(): bool;

    /**
     * @return bool
     */
    public function isJsonRequest(): bool;

    /**
     * @return bool
     */
    public function isJsonResponse(): bool;

    /**
     * @return Validator|null
     */
    public function getValidator(): ?Validator;
}
