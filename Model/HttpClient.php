<?php
/**
 * This file is part of Zepgram\Rest\Model
 *
 * @package    Zepgram\Rest\Model
 * @file       HttpClient.php
 * @date       04 11 2021 23:28
 *
 * @author     Benjamin Calef <zepgram@gmail.com>
 * @copyright  2021 Zepgram Copyright (c) (https://github.com/zepgram)
 * @license    MIT License
 **/

declare(strict_types=1);

namespace Zepgram\Rest\Model;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientFactory as GuzzleClientFactory;
use GuzzleHttp\Exception\RequestException;
use InvalidArgumentException;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Throwable;
use Zepgram\Rest\Exception\ExternalException;
use Zepgram\Rest\Exception\InternalException;
use Zepgram\Rest\Exception\Result\BusinessException;
use Zepgram\Rest\Exception\Result\HttpException;
use Zepgram\Rest\Exception\Result\NotFoundException;
use Zepgram\Rest\Exception\Result\UnserializeException;
use Zepgram\Rest\Model\Cache\Identifier;
use Zepgram\Rest\Model\Cache\RestApiCache;

class HttpClient
{
    public function __construct(
        private GuzzleClientFactory $guzzleClientFactory,
        private SerializerInterface $serializer,
        private RestApiCache $restApiCache,
        private Identifier $identifier,
        private LoggerInterface $logger
    ) {}

    /**
     * @param ParametersInterface $parameters
     * @return string|int|bool|array|null|float
     * @throws ExternalException
     * @throws InternalException
     */
    public function request(ParametersInterface $parameters): string|int|bool|array|null|float
    {
        $config = $parameters->getConfig();
        $context['method'] = __METHOD__;
        $context['request'] = array_merge(['base_uri' => $config->getBaseUri()], $parameters->toArray());
        $serviceName = $parameters->getServiceName();
        if ($config->isDebugEnabled()) {
            $this->logger->info(sprintf('[REST API] %s request', $serviceName), $context);
        }

        try {
            if ($parameters->getCacheKey() && $result = $this->getCache($parameters)) {
                if ($config->isDebugEnabled()) {
                    $context['response'] = $result;
                    $this->logger->info(sprintf('[REST API] %s cache', $serviceName), $context);
                }
                return $result;
            }

            $response = $this->guzzleRequest($parameters);
            $code = $response->getStatusCode();
            $body = (string) $response->getBody();
            $context['code'] = $code;
            $context['headers'] = $response->getHeaders();

            try {
                $result = $parameters->getIsJsonResponse() ? $this->serializer->unserialize($body) : $body;
                $context['response'] = $result;
            } catch (InvalidArgumentException $e) {
                throw new UnserializeException(__('[REST API] %1', $serviceName), $e);
            }

            if ($parameters->getCacheKey()) {
                $this->saveCache($parameters, $result);
            }

            if ($config->isDebugEnabled()) {
                $this->logger->info(sprintf('[REST API] %s response', $serviceName), $context);
            }

            return $result;
        } catch (ExternalException $e) {
            if ($e->getCode() === 404) {
                if ($config->isDebugEnabled()) {
                    $this->logger->info($e, $context);
                }
            } else {
                $this->logger->alert($e, $context);
            }
            throw $e;
        } catch (InternalException $e) {
            $this->logger->emergency($e, $context);
            throw $e;
        } catch (Throwable $e) {
            $this->logger->emergency($e, $context);
            throw new InternalException(__($e->getMessage()));
        }
    }

    /**
     * @param ParametersInterface $parameters
     * @return ResponseInterface
     * @throws BusinessException
     * @throws HttpException
     * @throws NotFoundException
     */
    private function guzzleRequest(ParametersInterface $parameters): ResponseInterface
    {
        /** @var GuzzleClient $client */
        $client = $this->guzzleClientFactory->create([
            'config' => [
                'base_uri' => $parameters->getConfig()->getBaseUri(),
                'timeout' => $parameters->getConfig()->getTimeout()
            ]
        ]);
        $serviceName = $parameters->getServiceName();

        try {
            return $client->request(
                $parameters->getMethod(),
                $parameters->getUri(),
                $parameters->getOptions()
            );
        } catch (RequestException $e) {
            $response = $e->getResponse();
            $body = $response && $response->getBody() ? (string) $response->getBody() : null;
            if ($e->getCode() === 404) {
                throw new NotFoundException(__('[REST API] %1 not found: %2', $serviceName, $body), $e, $e->getCode());
            }
            if (!empty($body) && $e->getCode() < 500) {
                throw new BusinessException(__('[REST API] %1 error: %2', $serviceName, $body), $e, $e->getCode());
            }
            throw new HttpException(__('[REST API] %1', $serviceName), $e, $e->getCode());
        } catch (Throwable $e) {
            throw new HttpException(__('[REST API] %1', $serviceName), $e, $e->getCode());
        }
    }

    /**
     * @param ParametersInterface $parameters
     * @return string|int|bool|array|null|float
     */
    private function getCache(ParametersInterface $parameters): string|int|bool|array|null|float
    {
        $cacheKey = $this->identifier->getCacheKey($parameters);
        $cache = $this->restApiCache->load($cacheKey);
        if (!$cache) {
            return false;
        }

        return $this->serializer->unserialize($cache);
    }

    /**
     * @param ParametersInterface $parameters
     * @param $result
     */
    private function saveCache(ParametersInterface $parameters, $result): void
    {
        $cacheKey = $this->identifier->getCacheKey($parameters);
        $data = $this->serializer->serialize($result);
        $this->restApiCache->save($data, $cacheKey, [], $parameters->getConfig()->getCacheLifetime());
    }
}
