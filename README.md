# Zepgram Rest

Technical module to industrialize API REST call with dependency injection using Guzzle library.
Provides multiple features to make your life easier while implementing a REST service as Magento developer:
- Prevent code duplication with a basic implementation in di.xml (only 1 class to create)
- Centralize all your REST web service under the same configuration or set a custom one
- Benefits built-in registry and cache system to ensure and secure performance
- Include a generic logger who provides all data with a debug mode option to retrieve parameters, request and result
- Declare your request and result as a json serialized or not, avoiding multiple implementation of the serializer

## Installation
```
composer require zepgram/module-rest
bin/magento module:enable Zepgram_Rest
bin/magento setup:upgrade
```

## Guideline

1. Create a Request Adapter class for your service extending abstract class `Zepgram\Rest\Model\RequestAdapter`, this class represent your service contract adapter:
    - **public const SERVICE_ENDPOINT**: define the service endpoint
    - **dispatch(DataObject $data)**: initialize Magento data that you must adapt to request the web service
    - **getBody()**: implement body request
    - **getHeaders()**: implement headers
    - **getUri()**: implement uri endpoint (used to handle dynamic values)
    - **getCacheKey()**: implement cache key for your specific request (you must define a unique key)
3. Create a Virtual Class from `Zepgram\Rest\Service\ApiRequest` and customize its injections for your needs 
   by following the below configuration:
  
    | Variable name | Type | Default value | Is Optional | Description |
    | :---: | :---: | :---: | :---: |  :---: |
    | serviceName | string | default | no | Value to retrieve group id from system config |
    | requestAdapter | object | RequestAdapter | no | Adapter class to build and customize the request |
    | validator | object | null | yes | Validate the service contract |
    | method | string | GET | yes | Request method |
    | isJsonRequest | boolean | true | yes | Parse request array to json |
    | isJsonResponse | boolean | true | yes | Parse response string to array |
    | isVerify | boolean | true | yes | Enable SSL certificate verification |
4. Create a system.xml, and a config.xml that must use the **serviceName** injected previously:
    - **section**: `rest_api`
    - **group_id**: `$serviceName`
    - **fields**:
        - `base_uri`
        - `timeout`
        - `is_debug`
        - `cache_ttl`
5. Go to the class who will run the web service and inject the `Zepgram\Rest\Service\ApiFactory` in his constructor.
6. Create **ApiRequest** with the **ApiFactory** `$this->apiFactory->create('VirtualClassName', $data)` where:
   - **VirtualClassName** represents the Virtual Class declared previously in di.xml
   - **$data** represents the dynamic data that you will receive in `dispatch()` method
7. Finally, the factory will return your dedicated `ApiRequest` to send the request use the method: `$apiRequest->send()`.

## Configuration

![562](https://user-images.githubusercontent.com/16258478/140424659-f9e1f593-c75f-40fd-aafa-935984c3ae10.png)
If you do not declare specific configuration, the request will fall back on default configuration.
To override the default config, you must follow this system config pattern: `rest_api/%service_name%/base_uri`

## Example

Here is a simple implementation example with a service called **Foxtrot** using the order object:

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
    public function dispatch(DataObject $data): void
    {
        $this->order = $data->getOrder();
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
    public function getHeaders(): array
    {
        return [
            self::HEADER_ACCEPT => self::CONTENT_JSON,
            self::HEADER_CONTENT_TYPE => self::CONTENT_JSON,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getUri(): ?string
    {
        return self::SERVICE_ENDPOINT;
    }

    /**
     * {@inheritDoc}
     */
    public function getCacheKey(): ?string
    {
        return null;
    }
}
```

**di.xml**
```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
    <!-- rest api -->
    <virtualType name="FoxtrotOrderApiRequest" type="Zepgram\Rest\Service\ApiRequest">
        <arguments>
            <argument name="serviceName" xsi:type="string">foxtrot</argument>
            <argument name="requestAdapter" xsi:type="object">Zepgram\Sales\Rest\FoxtrotOrderRequestAdapter</argument>
            <argument name="method" xsi:type="const">Laminas\Http\Request::METHOD_POST</argument>
            <argument name="isVerify" xsi:type="boolean">false</argument>
        </arguments>
    </virtualType>
</config>
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

**OrderDataExample.php**

```php
<?php

declare(strict_types=1);

namespace Zepgram\Sales\Model;

use Zepgram\Rest\Exception\InternalException;
use Zepgram\Rest\Exception\ExternalException;
use Zepgram\Rest\Service\ApiFactory;
use Zepgram\Sales\Api\OrderRepositoryInterface;

class OrderDataExample
{
    /** @var OrderRepositoryInterface */
    private $orderRepository;
        
    /** @var ApiFactory */
    private $apiFactory;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ApiFactory $apiFactory
    ) {
        $this->orderRepository = $orderRepository;
        $this->apiFactory = $apiFactory;
    }

    /**
     * @param int $orderId
     * @throws MyAweSomeTechnicalException
     * @throws MyAwesomeBusinessException
     */
    public function execute(int $orderId): void
    {
        try {
            $order = $this->orderRepository->get($orderId);
            // prepare request
            $foxtrotApiRequest = $this->apiFactory->create('FoxtrotOrderApiRequest', ['order' => $order]);
            // send request
            $result = $foxtrotApiRequest->send();
            // handle result
            $order->setData('foxtrot_order_result', $result);
            $this->orderRepository->save($order);
        } catch (ExternalException $e) {
             $context['context_error'] = 'We could not reach foxtrot order service'
             // service is unavailable due to technical reason: do something (log, throw, errorMessage..)
             $this->logger->error($e, $context);
             throw MyAwesomeTechnicalException(__('Foxtrot server error'), $e);
        } catch (InternalException $e) {
            $context['context_error'] = 'Magento request is wrong, foxtrot order service could not handle it'
            // service rejected request for business reason: do something (log, throw, errorMessage..)
            throw MyAwesomeBusinessException(__('Bad request error'), $e);
        }
    }
}
```

## Exceptions Bad-Practices

Please do not catch something for nothing when you use this module. For example, if you are doing this:
```php
try {
    return $apiRequest->send();
} catch (InternalException $e) {
    throw new InternalException(__('Internal error'));
} catch (ExternalException $e) {
    throw new ExternalException(__('External error'));
} catch (Throwable $e) {
    throw $e;
}
```
It has no value for the code and for the business because you are throwing the same exception and hiding the real error.
Try/catch MUST only be used when you are able to handle errors for your feature (detailed logs, retry etc...).

Also, the `Throwable` catch here will never throw.<br>The module only return `Internal` and `External` exception.<br>
Others exceptions thrown are technical exception, they are returned when you do not implement the module correctly.

To handle ALL exceptions thrown by this module you can catch `Zepgram\Rest\Exception\RestException`

## Issues

If you encountered an issue during installation or with usage, please report it on this github repository.
