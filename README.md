Thelia API client
===

[![Build Status](https://travis-ci.org/20uf/thelia-api-client.svg?branch=2.0)](https://travis-ci.org/20uf/thelia-api-client)

What is this ?
---
This is a PHP client for [Thelia](https://github.com/thelia/thelia) API.

How to use it ?
---
First, add ```thelia/api-client``` to your composer.json

```json
{
    "require": {
        # ...
        "thelia/api-client": "~2.0"
    }
}
```

Then, create an instance of ```Thelia\Api\Client\Client``` with the following parameters:

```php
    use Thelia\Api\Client\Client;

    $client = new Client("my api token", "my api key", "http://mysite.tld");
    
    // Create a client with a base URI
    $client = new Client("my api token", "my api key", "http://mysite.tld/api/");
    
    // Send a request to https://mysite.tld/api/test
    $response = $client->request('GET', 'test');
    
    // Send a request to https://mysite.tld/root
    $response = $client->request('GET', '/root');
```

You can access to your resources by using the methods

```php
    list($status, $data) = $client->get("products");
    list($status, $data) = $client->get("products/1/image", 1);
    list($status, $data) = $client->post("products", ["myData"]);
    list($status, $data) = $client->put("products", ["myData"]);
    list($status, $data) = $client->delete("products", 1);
```

Or you can use magic methods that are composed like that: ```methodEntity```

```php
    list($status, $data) = $client->listProducts();
    list($status, $data) = $client->getTaxes(42);
    list($status, $data) = $client->postPse($data);
    list($status, $data) = $client->putTaxRules($data);
    list($status, $data) = $client->deleteAttributeAvs(42);
```

Tests
---
To run the tests, edit the file tests/server.txt and place your thelia address