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
use Magento\Framework\Event\ManagerInterface;
use Zepgram\Rest\Model\HttpClient;

class ApiRequest implements ApiRequestInterface
{
    private mixed $result = false;

    public function __construct(
        private HttpClient $httpClient,
        private ApiProviderInterface $apiProvider,
        private ManagerInterface $eventManager,
        private DataObject $rawData,
        private string $serviceName
    ) {}

    /**
     * {@inheritDoc}
     */
    public function sendRequest(): string|int|bool|array|null|float
    {
        if ($result = $this->getResult()) {
            return $result;
        }

        $this->eventManager->dispatch($this->serviceName . '_send_before', ['raw_data' => $this->rawData]);
        $parameters = $this->apiProvider->build($this->rawData, $this->serviceName);
        $result = $this->httpClient->request($parameters);
        $this->eventManager->dispatch($this->serviceName . '_send_after', ['result' => $result]);
        $this->setResult($result);

        return $result;
    }

    private function getResult(): string|int|bool|array|null|float
    {
        return $this->result;
    }

    private function setResult($result): void
    {
        $this->result = $result;
    }
}
