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
use Zepgram\Rest\Model\AdapterNameResolver;
use Zepgram\Rest\Model\RequestAdapter;
use Zepgram\Rest\Model\RequestInterface;

class Identifier
{
    public function __construct(
        private Encryptor $encryptor,
        private SerializerInterface $serializer,
        private AdapterNameResolver $adapterNameResolver
    ) {
    }

    /**
     * @param RequestInterface $request
     * @return string
     */
    public function getCacheKey(RequestInterface $request): string
    {
        return $this->encryptor->hash($request->getAdapterName() . '_' . $request->getCacheKey());
    }

    /**
     * @param string $requestAdapter
     * @param string $cacheKey
     * @return string
     */
    public function getCacheKeyByRequestAdapter(string $requestAdapter, string $cacheKey): string
    {
        $adapterName = $this->adapterNameResolver->getAdapterName($requestAdapter);

        return $this->encryptor->hash($adapterName . '_' . $cacheKey);
    }

    /**
     * @param string $adapterName
     * @param array $data
     * @return string
     */
    public function getRegistryKey(string $adapterName, array $data): string
    {
        $extractedData = $this->recursiveExtract($data);

        return $this->encryptor->hash($adapterName . '_' . $this->serializer->serialize($extractedData));
    }

    /**
     * @param $value
     * @return mixed
     */
    private function recursiveExtract($value): mixed
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
