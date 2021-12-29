<?php
/**
 * This file is part of Zepgram\Rest\Service
 *
 * @package    Zepgram\Rest\Service
 * @file       ApiRequest.php
 * @date       04 11 2021 23:40
 *
 * @author     Benjamin Calef <zepgram@gmail.com>
 * @copyright  2021 Zepgram Copyright (c) (https://github.com/zepgram)
 * @license    MIT License
 **/

declare(strict_types=1);

namespace Zepgram\Rest\Service;

use Magento\Framework\DataObject;
use Zepgram\Rest\Model\HttpClient;

class ApiRequest implements ApiRequestInterface
{
    /** @var HttpClient */
    private $httpClient;

    /** @var ApiProviderInterface */
    private $apiProvider;

    /** @var array */
    private $rawData;

    /** @var string */
    private $serviceName;

    /** @var mixed */
    private $result = false;

    /**
     * @param HttpClient $httpClient
     * @param ApiProviderInterface $apiProvider
     * @param DataObject $rawData
     * @param string $serviceName
     */
    public function __construct(
        HttpClient $httpClient,
        ApiProviderInterface $apiProvider,
        DataObject $rawData,
        string $serviceName
    ) {
        $this->httpClient = $httpClient;
        $this->apiProvider = $apiProvider;
        $this->rawData = $rawData;
        $this->serviceName = $serviceName;
    }

    /**
     * {@inheritDoc}
     */
    public function sendRequest()
    {
        if ($result = $this->getResult()) {
            return $result;
        }

        $parameters = $this->apiProvider->build($this->rawData, $this->serviceName);
        $result = $this->httpClient->request($parameters);
        $this->setResult($result);

        return $result;
    }

    /**
     * @return mixed
     */
    private function getResult()
    {
        return $this->result;
    }

    /**
     * @param mixed $result
     */
    private function setResult($result): void
    {
        $this->result = $result;
    }
}
