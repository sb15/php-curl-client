<?php
declare(strict_types=1);

namespace Sb\Curl;

use Sb\Curl\Exception\Exception as ClientException;

abstract class ClientCore
{

    public const METHOD_GET = 'GET';
    public const METHOD_POST = 'POST';
    public const METHOD_PUT = 'PUT';
    public const METHOD_DELETE = 'DELETE';

    /**
     * @var array<string, string>
     */
    protected $headers = [];

    /**
     * @var string
     */
    protected $userAgent = 'Curl client';

    /**
     * @var bool
     */
    protected $verifySSL = true;

    /**
     * @var bool
     */
    protected $followLocation = true;

    /**
     * @var string|false
     */
    protected $cookiesJar = false;

    /**
     * @var bool
     */
    protected $saveSessionCookies = false;

    /**
     * @var bool
     */
    protected $useCompression = true;

    /**
     * @var int
     */
    protected $connectTimeout = 10;

    /**
     * @var int
     */
    protected $timeout = 60;

    /**
     * @var string|null
     */
    protected $proxy;

    /**
     * @var string|null
     */
    protected $preProxy;

    /**
     * @var integer|null
     */
    protected $responseCode;

    /**
     * @var array<string, string>
     */
    protected $responseHeaders = [];

    /**
     * @var array<string, string>
     */
    protected $responseInfo = [];

    /**
     * @var bool
     */
    protected $debug = false;

    /**
     * @var string|null
     */
    protected $trace;

    /**
     * @var resource|false
     */
    protected $verboseStreamResource = false;

    /**
     * @var resource|false
     */
    protected $headerStreamResource = false;

    /**
     * @var resource|false
     */
    private $ch = false;

    private function beforeRequest(): void
    {
        $this->trace = null;
        $this->responseCode = null;
        $this->responseInfo = [];
        $this->responseHeaders = [];
    }

    /**
     * @param string $url
     * @param string $type
     * @param array<array>|string|null $payload
     * @param array<string, string> $headers
     * @param resource|false $outputStreamResource
     * @return string
     * @throws ClientException
     */
    protected function request(string $url, string $type, $payload = null, array $headers = [], $outputStreamResource = false): string
    {
        $this->beforeRequest();

        $this->ch = curl_init();
        if (!is_resource($this->ch)) {
            throw new ClientException('cURL init error');
        }

        $clientHeader = $this->headers;
        foreach ($headers as $k => $v) {
            $clientHeader[$k] = $v;
        }
        $httpHeaders = $this->getHeadersForCurl($clientHeader);

        $curlParams = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_USERAGENT => $this->userAgent,
        ];

        switch ($type) {
            case self::METHOD_POST:
                $curlParams[CURLOPT_POST] = true;
                $curlParams[CURLOPT_POSTFIELDS] = $payload;
                break;
            case self::METHOD_PUT:
                $curlParams[CURLOPT_CUSTOMREQUEST] = 'PUT';
                $curlParams[CURLOPT_POSTFIELDS] = $payload;
                break;
            case self::METHOD_DELETE:
                $curlParams[CURLOPT_CUSTOMREQUEST] = 'DELETE';
                break;
        }

        if (!$this->verifySSL) {
            $curlParams[CURLOPT_SSL_VERIFYHOST] = false;
            $curlParams[CURLOPT_SSL_VERIFYPEER] = false;
        }

        if ($this->cookiesJar) {
            $curlParams[CURLOPT_COOKIEJAR] = $this->cookiesJar;
            $curlParams[CURLOPT_COOKIEFILE] = $this->cookiesJar;
            if ($this->saveSessionCookies) {
                $curlParams[CURLOPT_COOKIESESSION] = true;
            }
        }

        if ($this->followLocation) {
            $curlParams[CURLOPT_FOLLOWLOCATION] = true;
            $curlParams[CURLOPT_MAXREDIRS] = 10;
            $curlParams[CURLOPT_AUTOREFERER] = true;
        }

        if ($this->debug) {
            $this->verboseStreamResource = fopen('php://temp', 'wb+');
            $curlParams[CURLOPT_VERBOSE] = true;
            $curlParams[CURLOPT_STDERR] = $this->verboseStreamResource;
        }

        if ($this->proxy) {
            $curlParams[CURLOPT_PROXY] = $this->proxy;
        }

        if ($this->preProxy) {
            $curlParams[CURLOPT_PRE_PROXY] = $this->preProxy;
        }

        if ($this->useCompression) {
            $curlParams[CURLOPT_ENCODING] = '';
        }

        $curlParams[CURLOPT_HTTPHEADER] = $httpHeaders;

        $this->headerStreamResource = fopen('php://temp', 'wb+');
        $curlParams[CURLOPT_WRITEHEADER] = $this->headerStreamResource;

        if (is_resource($outputStreamResource)) {
            $curlParams[CURLOPT_FILE] = $outputStreamResource;
        }

        curl_setopt_array($this->ch, $curlParams);

        $response = (string) curl_exec($this->ch);
        $curlInfo = curl_getinfo($this->ch);

        if (!is_resource($this->headerStreamResource)) {
            $this->freeStreamResources();
            throw new ClientException('header stream resource failed');
        }
        rewind($this->headerStreamResource);
        $responseHeaders = (string) stream_get_contents($this->headerStreamResource);

        if ($this->debug) {

            if (!is_resource($this->verboseStreamResource)) {
                $this->freeStreamResources();
                throw new ClientException('verbose stream resource failed');
            }
            rewind($this->verboseStreamResource);
            $trace = '';

            $curlInfoPretty = json_encode($curlInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            if (false === $curlInfoPretty) {
                $this->freeStreamResources();
                throw new ClientException('json_encode failed');
            }
            $curlInfoPretty = str_replace(['}', '{', '    '], ['', '', '* '], $curlInfoPretty);
            $curlInfoPretty = trim($curlInfoPretty);

            $trace .= stream_get_contents($this->verboseStreamResource);
            $trace .= "* Connection info\n";
            $trace .= $curlInfoPretty;

            $this->trace = $trace;
        }

        $httpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        $error = curl_error($this->ch);

        $this->responseCode = $httpCode;
        $this->responseInfo = $curlInfo;

        if ($error) {
            $this->freeStreamResources();
            throw new ClientException('Connection error to '. $url . ' Return code: ' . $httpCode . ' Message: ' . $error);
        }

        $this->setResponseHeadersFromString($responseHeaders);
        $this->freeStreamResources();

        return $response;
    }

    private function freeStreamResources(): void
    {
        if (is_resource($this->headerStreamResource)) {
            fclose($this->headerStreamResource);
        }
        if (is_resource($this->verboseStreamResource)) {
            fclose($this->verboseStreamResource);
        }
        if (is_resource($this->ch)) {
            curl_close($this->ch);
        }
    }

    /**
     * @param array<string, string> $headers
     * @return array<string>
     */
    private function getHeadersForCurl(array $headers): array
    {
        $result = [];
        foreach ($headers as $headerName => $headerValue) {
            $result[] = "{$headerName}: {$headerValue}";
        }
        return $result;
    }

    /**
     * @param string $headers
     */
    private function setResponseHeadersFromString(string $headers): void
    {
        $this->responseHeaders = [];

        $headers = str_replace("\r", '', trim($headers));
        $headers = explode("\n", $headers);

        foreach ($headers as $header) {

            if ($header && strpos($header, ':') !== false) {
                $headerNameValue = explode(':', $header, 2);
                $this->responseHeaders[$headerNameValue[0]] = trim($headerNameValue[1]);
            }
        }
    }

    public function setDebug(): void
    {
        $this->debug = true;
    }

    /**
     * @return string|null
     */
    public function getTrace(): ?string
    {
        return $this->trace;
    }
}