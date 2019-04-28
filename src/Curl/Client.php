<?php

namespace Sb\Curl;

use Sb\Curl\Traits\Core;
use Sb\Curl\Traits\Main;

class Client
{
    use Core;
    use Main;

    public const METHOD_GET = 'GET';
    public const METHOD_POST = 'POST';
    public const METHOD_PUT = 'PUT';
    public const METHOD_DELETE = 'DELETE';

    private $headers = [];
    private $verifySSL = true;
    private $followLocation = true;
    private $cookiesJar = false;
    private $saveSessionCookies = false;
    private $connectTimeout = 10;
    private $timeout = 60;
    private $proxy;

    private $responseCode;
    private $responseHeaders = [];

    private $debug = false;
    private $trace;

}