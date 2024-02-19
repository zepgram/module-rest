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
use Zepgram\Rest\Exception\Result\HttpException;
use Zepgram\Rest\Exception\Result\NotFoundException;
use Zepgram\Rest\Exception\Result\ServiceException;
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
    ) {
    }

    /**
     * @param RequestInterface $request
     * @return string|int|bool|array|null|float
     * @throws ExternalException
     * @throws InternalException
     */
    public function request(RequestInterface $request): string|int|bool|array|null|float
    {
        $config = $request->getConfig();
        $context['method'] = __METHOD__;
        $context['request'] = array_merge(['base_uri' => $config->getBaseUri()], $request->toArray());
        $adapterName = $request->getAdapterName();
        if ($config->isDebugEnabled()) {
            $this->logger->info(sprintf('[REST API] %s request', $adapterName), $context);
        }

        try {
            if ($request->getCacheKey() && $result = $this->getCache($request)) {
                if ($config->isDebugEnabled()) {
                    $context['response'] = $result;
                    $this->logger->info(sprintf('[REST API] %s cache', $adapterName), $context);
                }
                return $result;
            }

            $response = $this->guzzleRequest($request);
            $code = $response->getStatusCode();
            $body = (string)$response->getBody();
            $context['code'] = $code;
            $context['headers'] = $response->getHeaders();

            try {
                $result = $request->getIsJsonResponse() ? $this->serializer->unserialize($body) : $body;
                $context['response'] = $result;
            } catch (InvalidArgumentException $e) {
                throw new UnserializeException(__('[REST API] %1', $adapterName), $e);
            }

            if ($request->getCacheKey()) {
                $this->saveCache($request, $result);
            }

            if ($config->isDebugEnabled()) {
                $this->logger->info(sprintf('[REST API] %s response', $adapterName), $context);
            }

            return $result;
        } catch (ExternalException $e) {
            if ($e->getCode() === 404) {
                if ($config->isDebugEnabled()) {
                    $this->logger->info($e, $context);
                }
            } else {
                $this->logger->critical($e, $context);
            }
            throw $e;
        } catch (InternalException $e) {
            $this->logger->critical($e, $context);
            throw $e;
        } catch (Throwable $e) {
            $this->logger->critical($e, $context);
            throw new InternalException(__($e->getMessage()));
        }
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws ServiceException
     * @throws HttpException
     * @throws NotFoundException
     */
    private function guzzleRequest(RequestInterface $request): ResponseInterface
    {
        /** @var GuzzleClient $client */
        $client = $this->guzzleClientFactory->create([
            'config' => [
                'base_uri' => $request->getConfig()->getBaseUri(),
                'timeout' => $request->getConfig()->getTimeout()
            ]
        ]);
        $adapterName = $request->getAdapterName();

        try {
            return $client->request(
                $request->getMethod(),
                $request->getUri(),
                $request->getOptions()
            );
        } catch (RequestException $e) {
            $response = $e->getResponse();
            $body = $response && $response->getBody() ? (string)$response->getBody() : null;
            if ($e->getCode() === 404) {
                throw new NotFoundException(__('[REST API] %1 not found: %2', $adapterName, $body), $e, $e->getCode());
            }
            if (!empty($body) && $e->getCode() < 500) {
                throw new ServiceException(__('[REST API] %1 error: %2', $adapterName, $body), $e, $e->getCode());
            }
            throw new HttpException(__('[REST API] %1', $adapterName), $e, $e->getCode());
        } catch (Throwable $e) {
            throw new HttpException(__('[REST API] %1', $adapterName), $e, $e->getCode());
        }
    }

    /**
     * @param RequestInterface $request
     * @return string|int|bool|array|null|float
     */
    private function getCache(RequestInterface $request): string|int|bool|array|null|float
    {
        $cacheKey = $this->identifier->getCacheKey($request);
        $cache = $this->restApiCache->load($cacheKey);
        if (!$cache) {
            return false;
        }

        return $this->serializer->unserialize($cache);
    }

    /**
     * @param RequestInterface $request
     * @param $result
     */
    private function saveCache(RequestInterface $request, $result): void
    {
        $cacheKey = $this->identifier->getCacheKey($request);
        $data = $this->serializer->serialize($result);
        $this->restApiCache->save($data, $cacheKey, [], $request->getConfig()->getCacheLifetime());
    }
}
