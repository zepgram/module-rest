# Zepgram Rest

## Overview
Zepgram Rest is a technical module designed to streamline the development of REST API integrations in Magento 2 projects.
Utilizing the Guzzle HTTP client for dependency injection, this module offers a robust set of features aimed at reducing 
boilerplate code, improving performance, and enhancing debugging capabilities. By centralizing REST API interactions and 
leveraging Magento's built-in systems, Zepgram Rest simplifies the implementation process for developers.

## Features
Zepgram Rest provides several key features to aid Magento developers in creating and managing RESTful services:
- <b>Avoid Code Duplication:</b> Minimize repetitive code with a straightforward setup in di.xml. Implement your REST API integrations with just one class creation, streamlining the development process.
- <b>Centralized Configuration:</b> Manage all your REST web services configurations in one place, ensuring consistency and ease of maintenance.
- <b>Built-in Registry and Cache:</b> Take advantage of Magento's native cache mechanisms and dedicated registry to boost your API's performance and security. This feature helps in efficiently managing data retrieval and storage, reducing the load on your server.
- <b>Generic Logger:</b> Debugging is made effortless with an inclusive logging system. Enable the debug mode to log detailed information about your API calls, including parameters, requests, and responses, facilitating easier troubleshooting.
- <b>Data Serialization:</b> Declare whether your requests and results should be JSON serialized or not. This flexibility prevents the need for multiple serializer implementations, accommodating various API requirements with ease.

## Installation

```
composer require zepgram/module-rest
bin/magento module:enable Zepgram_Rest
bin/magento setup:upgrade
```

## Guideline with ApiPool

1. Create a RequestAdapter class for your service extending abstract class `Zepgram\Rest\Model\RequestAdapter`,
   this class represent your service contract adapter:
   - **public const SERVICE_ENDPOINT**: define the service endpoint
   - **dispatch(DataObject $rawData)**: initialize data that you will adapt to request the web service
   - **getBody()**: implement body request
   - **getHeaders()**: implement headers
   - **getUri()**: implement uri endpoint (used to handle dynamic values)
   - **getCacheKey()**: implement cache key for your specific request (you must define a unique key)
1. Create a system.xml, and a config.xml with a dedicated **configName**:
   - **section**: `rest_api`
   - **group_id**: `$configName`
   - **fields**:
      - `base_uri`
      - `timeout`
      - `is_debug`
      - `cache_ttl`
1. Declare your service in di.xml by implementing `Zepgram\Rest\Service\ApiProvider` as VirtualClass, you can configure
   it by following the [ApiProviderConfig](#configuration)
1. Declare your RequestAdapter and ApiProvider in `Zepgram\Rest\Service\ApiPoolInterface`:
    - Add a new item in `apiProviders[]`:
      - The **key** is your custom RequestAdapter full namespace
      - The **value** is your ApiProvider as a VirtualClass
1. Inject ApiPoolInterface in the class that will consume your API and use `$this->apiPool->execute(RequestAdapter::class, $rawData)` where:
    - **RequestAdapter::class** represents the request adapter declared in `apiProviders[]`
    - **$rawData** is an array of dynamic data that will be dispatch in `dispatch()` method

## Basic guideline implementation

Instead of declaring your class in `Zepgram\Rest\Service\ApiPoolInterface` you can also directly inject
your ApiProvider in a dedicated class:
```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
    <!-- rest api -->
    <virtualType name="CustomApiProvider" type="Zepgram\Rest\Service\ApiProvider">
        <arguments>
            <argument name="requestAdapter" xsi:type="object">Zepgram\Sales\Rest\FoxtrotOrderRequestAdapter</argument>
            <argument name="configName" xsi:type="string">foxtrot</argument>
        </arguments>
    </virtualType>
    <type name="My\Custom\Model\ConsumerExample">
        <arguments>
            <argument name="apiProvider" xsi:type="object">CustomApiProvider</argument>
        </arguments>
    </type>
</config>
```

```php
<?php

declare(strict_types=1);

namespace My\Custom\Model\Api;

use Zepgram\Rest\Exception\InternalException;
use Zepgram\Rest\Exception\ExternalException;
use Zepgram\Rest\Service\ApiPoolInterface;
use Zepgram\Rest\Service\ApiProviderInterface;
use Zepgram\Sales\Api\OrderRepositoryInterface;

class ConsumerExample
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private ApiProviderInterface $apiProvider
    ) {}

    /**
     * @param int $orderId
     * @return array 
     */
    public function execute(int $orderId): array
    {
        // get raw data
        $order = $this->orderRepository->get($orderId);
        // send request
        $result = $this->apiProvider->execute(['order' => $order]);
        
        return $result;
    }
}
```

## Configuration

### Store config

![562](https://user-images.githubusercontent.com/16258478/140424659-f9e1f593-c75f-40fd-aafa-935984c3ae10.png)
If you do not declare specific configuration, the request will fall back on default configuration.
To override the default config, you must follow this system config pattern: `rest_api/%configName%/base_uri`

### XML config

You can configure your service with `Zepgram\Rest\Service\ApiProvider` by creating a
VirtualClass and customize its injections for your needs by following the below configuration:

| Variable name  |  Type   | Default value  | Is Optional |                   Description                    |
|:--------------:|:-------:|:--------------:|:-----------:|:------------------------------------------------:|
|   configName   | string  |    default     |     no      |  Value to retrieve group id from system config   |
| requestAdapter | object  | RequestAdapter |     no      | Adapter class to build and customize the request |
|   validator    | object  |      null      |     yes     |          Validate the service contract           |
|     method     | string  |      GET       |     yes     |                  Request method                  |
| isJsonRequest  | boolean |      true      |     yes     |           Parse request array to json            |
| isJsonResponse | boolean |      true      |     yes     |          Parse response string to array          |
|    isVerify    | boolean |      true      |     yes     |       Enable SSL certificate verification        |

## Implementation

Here is a simple implementation example with a service called **Foxtrot** using the order object as rawData:

**FoxtrotOrderRequestAdapter.php**

```php
<?php

declare(strict_types=1);

namespace Zepgram\Sales\Rest;

use Magento\Framework\DataObject;
use Magento\Sales\Api\Data\OrderInterface;
use Zepgram\Rest\Model\RequestAdapter;

class FoxtrotOrderRequestAdapter extends RequestAdapter
{
    /** @var string */
    public const SERVICE_ENDPOINT = 'v1/order/';
    
    /** @var OrderInterface */
    private $order;

    /**
     * {@inheritDoc}
     */
    public function dispatch(DataObject $rawData): void
    {
        $this->order = $rawData->getOrder();
    }

    /**
     * {@inheritDoc}
     */
    public function getBody(): array
    {
        return [
            'orderId' => $this->order->getEntityId(),
            'customer' => $this->order->getCustomerEmail(),
            'orderTotal' => $this->order->getGrandTotal(),
            'version' => '1.0',
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getCacheKey(): ?string
    {
        return $this->order->getEntityId();
    }
}
```

**system.xml**

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Config:etc/system_file.xsd">
    <system>
        <section id="rest_api">
            <group id="foxtrot" translate="label" type="text" sortOrder="10" showInDefault="1" showInWebsite="0"
                   showInStore="0">
                <label>Foxtrot</label>
                <field id="base_uri" translate="label" type="text" sortOrder="1" showInDefault="1" showInWebsite="0"
                       showInStore="0" canRestore="1">
                    <label>Base URI</label>
                </field>
                <field id="timeout" translate="label" type="text" sortOrder="1" showInDefault="1" showInWebsite="0"
                       showInStore="0" canRestore="1">
                    <label>Timeout</label>
                </field>
                <field id="cache_ttl" translate="label" type="text" sortOrder="5" showInDefault="1" showInWebsite="0"
                       showInStore="0" canRestore="1">
                    <label>Cache TTL</label>
                </field>
                <field id="is_debug" translate="label" type="select" sortOrder="10" showInDefault="1" showInWebsite="0"
                       showInStore="0" canRestore="1">
                    <label>Enable Debug</label>
                    <source_model>Magento\Config\Model\Config\Source\Yesno</source_model>
                </field>
            </group>
        </section>
    </system>
</config>
```

**config.xml**

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Store:etc/config.xsd">
    <default>
        <rest_api>
            <foxtrot>
                <base_uri>https://foxtrot.service.io</base_uri>
                <timeout>30</timeout>
                <cache_ttl>7200</cache_ttl>
                <is_debug>1</is_debug>
            </foxtrot>
        </rest_api>
    </default>
</config>
```

**di.xml**

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
   <!-- rest api -->
   <virtualType name="FoxtrotOrderApiProvider" type="Zepgram\Rest\Service\ApiProvider">
      <arguments>
         <argument name="requestAdapter" xsi:type="object">Zepgram\Sales\Rest\FoxtrotOrderRequestAdapter</argument>
         <argument name="configName" xsi:type="string">foxtrot</argument>
      </arguments>
   </virtualType>
   <type name="Zepgram\Rest\Service\ApiPoolInterface">
      <arguments>
         <argument name="apiProviders" xsi:type="array">
            <item name="Zepgram\Sales\Rest\FoxtrotOrderRequestAdapter" xsi:type="object">FoxtrotOrderApiProvider</item>
         </argument>
      </arguments>
   </type>
</config>
```

**OrderDataExample.php**

```php
<?php

declare(strict_types=1);

namespace Zepgram\Sales\Model;

use Zepgram\Rest\Exception\InternalException;
use Zepgram\Rest\Exception\ExternalException;
use Zepgram\Rest\Service\ApiPoolInterface;
use Zepgram\Sales\Api\OrderRepositoryInterface;
use Zepgram\Sales\Rest\FoxtrotOrderRequestAdapter;

class OrderDataExample
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private ApiPoolInterface $apiPool
    ) {}

    /**
     * @param int $orderId
     * @throws MyAweSomeTechnicalException
     * @throws MyAwesomeBusinessException
     */
    public function execute(int $orderId): void
    {
        try {
            // get raw data
            $order = $this->orderRepository->get($orderId);
            // send request
            $result = $this->apiPool->execute(FoxtrotOrderRequestAdapter::class, ['order' => $order]);
            // handle result
            $order->setFoxtrotData($result);
            $this->orderRepository->save($order);
        } catch (InternalException $e) {
            $context['context_error'] = 'Magento request is wrong, foxtrot order service could not handle it'
            // service rejected request for business reason: do something (log, throw, errorMessage..)
            throw MyAwesomeBusinessException(__('Bad request error'), $e);
        } catch (ExternalException $e) {
             $context['context_error'] = 'We could not reach foxtrot order service'
             // service is unavailable due to technical reason: do something (log, throw, errorMessage..)
             $this->logger->error($e, $context);
             throw MyAwesomeTechnicalException(__('Foxtrot server error'), $e);
        }
    }
}
```

## Issues & Improvements

If you encountered an issue during installation or with usage, please report it on this github repository.<br>
If you have good ideas to improve this module, feel free to contribute.
