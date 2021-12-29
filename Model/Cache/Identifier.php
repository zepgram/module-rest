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
use Magento\Framework\Serialize\SerializerInterface;
use Zepgram\Rest\Model\ParametersInterface;

class Identifier
{
    /** @var Encryptor */
    private $encryptor;

    /** @var SerializerInterface */
    private $serializer;

    /**
     * @param Encryptor $encryptor
     * @param SerializerInterface $serializer
     */
    public function __construct(
        Encryptor $encryptor,
        SerializerInterface $serializer
    ) {
        $this->encryptor = $encryptor;
        $this->serializer = $serializer;
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
     * @param array $data
     * @return string
     */
    public function getRegistryKey(string $serviceName, array $data): string
    {
        $extractedData = $this->recursiveExtract($data);

        return $this->encryptor->hash($serviceName . '_' . $this->serializer->serialize($extractedData));
    }

    /**
     * @param $value
     * @return array|mixed
     */
    private function recursiveExtract($value)
    {
        if (is_object($value) && method_exists($value, 'toArray')) {
            return $value->toArray();
        }
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = $this->recursiveExtract($v);
            }
            return $value;
        }

        return $value;
    }
}
