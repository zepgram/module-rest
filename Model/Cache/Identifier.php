<?php
/**
 * This file is part of Zepgram\Rest\Model\Cache
 *
 * @package    Zepgram\Rest\Model\Cache
 * @file       Identifier.php
 * @date       04 11 2021 23:28
 *
 * @author     Benjamin Calef <zepgram@gmail.com>
 * @copyright  2021 Zepgram Copyright (c) (https://github.com/zepgram)
 * @license    MIT License
 **/

declare(strict_types=1);

namespace Zepgram\Rest\Model\Cache;

use Magento\Framework\Encryption\Encryptor;
use Zepgram\Rest\Model\ParametersInterface;

class Identifier
{
    /** @var Encryptor */
    private $encryptor;

    /**
     * @param Encryptor $encryptor
     */
    public function __construct(Encryptor $encryptor)
    {
        $this->encryptor = $encryptor;
    }

    /**
     * @param ParametersInterface $parameters
     * @return string
     */
    public function getCacheKey(ParametersInterface $parameters): string
    {
        return $this->encryptor->hash($parameters->getServiceName() . '_' . $parameters->getCacheKey());
    }

    /**
     * @param string $serviceName
     * @param $data
     * @return string
     */
    public function getRegistryKey(string $serviceName, $data): string
    {
        return $this->encryptor->hash($serviceName . '_' . $data);
    }
}
