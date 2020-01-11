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

    protected $headers = [];
    protected $userAgent = 'Curl client';
    protected $verifySSL = true;
    protected $followLocation = true;
    protected $cookiesJar = false;
    protected $saveSessionCookies = false;
    protected $useCompression = true;
    protected $connectTimeout = 10;
    protected $timeout = 60;
    protected $proxy;

    protected $responseCode;
    protected $responseHeaders = [];
    protected $responseInfo = [];

    protected $debug = false;
    protected $trace;

    private $ch;

    private function beforeRequest(): void
    {
        $this->trace = null;
        $this->responseCode = null;
        $this->responseHeaders = [];
    }

    /**
     * @param $url
     * @param $type
     * @param null $payload
     * @param array $headers
     * @return bool|string
     * @throws ClientException
     */
    protected function request($url, $type, $payload = null, array $headers = [])
    {
        $this->beforeRequest();

        $this->ch = curl_init();
        if (!$this->ch) {
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
            CURLOPT_HEADER => true,
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
            $verbose = fopen('php://temp', 'wb+');
            $curlParams[CURLOPT_VERBOSE] = true;
            $curlParams[CURLOPT_STDERR] = $verbose;
        }

        if ($this->proxy) {
            $curlParams[CURLOPT_PROXY] = $this->proxy;
        }

        if ($this->useCompression) {
            $curlParams[CURLOPT_ENCODING] = '';
        }

        $curlParams[CURLOPT_HTTPHEADER] = $httpHeaders;

        curl_setopt_array($this->ch, $curlParams);

        $response = curl_exec($this->ch);
        $curlInfo = curl_getinfo($this->ch);

        if ($this->debug) {

            rewind($verbose);
            $trace = '';

            $curlInfoPretty = json_encode($curlInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            $curlInfoPretty = str_replace(['}', '{', '    '], ['', '', '* '], $curlInfoPretty);
            $curlInfoPretty = trim($curlInfoPretty);

            $trace .= stream_get_contents($verbose);
            $trace .= "* Connection info\n";
            $trace .= $curlInfoPretty;

            $this->trace = $trace;

            fclose($verbose);
        }

        $httpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
        $error = curl_error($this->ch);
        curl_close($this->ch);

        $this->responseCode = $httpCode;
        $this->responseInfo = $curlInfo;

        if ($error) {
            throw new ClientException('Connection error to '. $url . ' Return code: ' . $httpCode . ' Message: ' . $error);
        }

        $this->setResponseHeadersFromString(substr($response, 0, $headerSize));

        return substr($response, $headerSize);
    }

    private function getHeadersForCurl($headers): array
    {
        $result = [];
        foreach ($headers as $headerName => $headerValue) {
            $result[] = "{$headerName}: {$headerValue}";
        }
        return $result;
    }

    private function setResponseHeadersFromString($headers): void
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

    public function getTrace()
    {
        return $this->trace;
    }
}