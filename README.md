PHP cUrl client
===============

[![Latest Version](https://img.shields.io/github/v/release/sb15/php-curl-client.svg)](https://github.com/sb15/php-curl-client/releases)
[![Build Status](https://github.com/sb15/php-curl-client/workflows/Main%20workflow/badge.svg)](https://github.com/sb15/php-curl-client/actions?query=workflow%3AMain%20workflow)
[![Total Downloads](https://img.shields.io/packagist/dt/sb15/php-curl-client.svg)](https://packagist.org/packages/sb15/php-curl-client)
[![License](https://img.shields.io/github/license/sb15/php-curl-client)](https://github.com/sb15/php-curl-client/LICENSE)
![PHP Versions Supported](https://img.shields.io/packagist/php-v/sb15/php-curl-client/v1.0.0)


```php
$client = new \Sb\Client();

$client->setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:71.0) Gecko/20100101 Firefox/71.0');
$client->setDebug();

$response = $client->get('https://httpbin.org/get');
echo $client->getResponseCode() . "\n";
echo $response . "\n";
echo $client->getTrace() . "\n";
```

## 💽 Installing

```bash
composer require sb15/php-curl-client
```
