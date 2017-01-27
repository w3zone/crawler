# Crawler
php crawler based on service .

web pages crawlers based on multi-services .

## Installation

```bash
composer require w3zone/Crawler
```

### Requirements
* node.js
* libcurl
* php-curl
* node.js request module


## Usage

```php
// available services Services\nodejsRequest, Services\cliCurl
use w3zone\Crawler\{Crawler, Services\phpCurl};

$crawler = new Crawler(new phpCurl);

$link = 'http://www.example.com';

// return an array [statusCode, body, headers, cookies]
// get method may contain link string or an array [url, query string]
$homePage = $crawler->get($link)->dumpHeaders()->run();

$response = $crawler->get($link)->dumpHeaders()->cookies(homePage['cookies'], 'r+w')->run();
```

## Available Services
* phpCurl
* nodejsRequest
* cliCurl

## Available Methods
```php

// an array of options [url, post data]
$response = $crawler->post(['url' => 'http://www.example.com', 'data' => $postData])
  ->json() // an easy way to add json headers to the request
  ->xml() // an easy to add xml headers to the request
  ->headers($arrayOfHeaders)
  // mode can be r for only read cookies, w for only write cookies, r+w || w+r for read and write cookies
  ->cookies($cookiesString, $mode)
  ->referer($referer) // an easy way to add referer too headers
  ->dumpHeaders() // to print headers
  ->proxy($proxyArray) // request via proxy, ['ip' => '127.0.0.1', 'type' => 'socks5']
  ->initialize($array) // re-initialize your request with special options
  -> run(); // fire the service
```
