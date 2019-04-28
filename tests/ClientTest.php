<?php

namespace Sb\Curl\Tests;

use PHPUnit\Framework\TestCase;
use Sb\Curl\Client;

class ClientTest extends TestCase
{

    /** @var Client */
    private $client;

    public function setUp(): void
    {
        $this->client = new Client();
    }

    public function testGet()
    {
        $response = $this->client->get('https://httpbin.org/get');

        $this->assertSame(200, $this->client->getResponseCode());
        $this->assertNotEmpty($response);
    }

    public function testPost()
    {
        $response = $this->client->post('https://httpbin.org/post', 'a=1');
        $this->assertSame(200, $this->client->getResponseCode());

        $response = json_decode($response, true);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('form', $response);
        $this->assertArrayHasKey('a', $response['form']);
        $this->assertEquals('1', $response['form']['a']);
    }

    public function testHeaders()
    {
        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:66.0) Gecko/20100101 Firefox/66.0';

        $this->client->setHeader('User-Agent', $userAgent);
        $response = $this->client->get('https://httpbin.org/get');

        $this->assertSame(200, $this->client->getResponseCode());
        $this->assertNotEmpty($response);

        $response = json_decode($response, true);

        $this->assertEquals($response['headers']['User-Agent'], $userAgent);
    }

    public function testDebug()
    {
        $this->client->setDebug();
        $this->client->get('https://httpbin.org/get');

        $this->assertSame(200, $this->client->getResponseCode());
        $this->assertIsString($this->client->getTrace());
    }
}