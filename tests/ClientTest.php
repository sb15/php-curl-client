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
        $this->client
            ->setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:71.0) Gecko/20100101 Firefox/71.0')
            ->setHeader('Accept-Language', 'ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3')
            ->setHeader('Upgrade-Insecure-Requests', '1');

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

    public function testPostMultipartFormData()
    {
        $response = $this->client->post('https://httpbin.org/post', ['a' => 1]);
        $this->assertSame(200, $this->client->getResponseCode());

        $response = json_decode($response, true);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('form', $response);
        $this->assertArrayHasKey('a', $response['form']);
        $this->assertEquals('1', $response['form']['a']);
        $this->assertRegExp('#multipart/form-data#', $response['headers']['Content-Type']);
    }

    public function testPostJSON()
    {
        $response = $this->client->postJSON('https://httpbin.org/post', json_encode(['a' => 1]));
        $this->assertSame(200, $this->client->getResponseCode());

        $response = json_decode($response, true);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('json', $response);
        $this->assertArrayHasKey('a', $response['json']);
        $this->assertEquals('1', $response['json']['a']);
    }

    public function testFileUpload()
    {
        $payload = [
            'filename' => '@' . realpath(__DIR__ . '/data/upload.txt')
        ];

        $response = $this->client->post('https://httpbin.org/post', $payload);
        $this->assertSame(200, $this->client->getResponseCode());

        $response = json_decode($response, true);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('files', $response);
        $this->assertIsArray($response['files']);
    }

    public function testDownload()
    {
        $this->client->setHeader('accept', 'image/jpeg');
        $this->client->download('https://httpbin.org/image', 'php://temp');
        $this->assertSame(200, $this->client->getResponseCode());
    }

    public function testHeaders()
    {
        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:71.0) Gecko/20100101 Firefox/71.0';
        $acceptLanguage = 'ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3';
        $upgradeInsecureRequests = '1';

        $this->client
            ->setUserAgent($userAgent)
            ->setHeader('Accept-Language', $acceptLanguage)
            ->setHeader('Upgrade-Insecure-Requests', $upgradeInsecureRequests);

        $response = $this->client->get('https://httpbin.org/get');

        $this->assertSame(200, $this->client->getResponseCode());
        $this->assertNotEmpty($response);

        $response = json_decode($response, true);

        $this->assertEquals($response['headers']['User-Agent'], $userAgent);
        $this->assertEquals($response['headers']['Accept-Language'], $acceptLanguage);
        $this->assertEquals($response['headers']['Upgrade-Insecure-Requests'], $upgradeInsecureRequests);
    }

    public function testDebug()
    {
        $this->client->setDebug();
        $this->client->get('https://httpbin.org/get');

        $this->assertSame(200, $this->client->getResponseCode());
        $this->assertIsString($this->client->getTrace());
    }

}