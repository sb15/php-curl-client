<?php

namespace Sb\Curl\Traits;

use Sb\Curl\Exception\Exception as ClientException;

trait Core
{

    private function getHTTPHeaders(): array
    {
        $result = [];
        foreach ($this->headers as $headerName => $headerValue) {
            $result[] = "{$headerName}: {$headerValue}";
        }
        return $result;
    }

    protected function request($url, $type, $rawBody = null)
    {
        $this->trace = null;
        $this->responseCode = null;
        $this->responseHeaders = [];

        $ch = curl_init();
        if (!$ch) {
            throw new ClientException('cURL init error');
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

        curl_setopt($ch, CURLOPT_HEADER, true);

        switch ($type) {
            case self::METHOD_POST:
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $rawBody);
                break;
            case self::METHOD_PUT:
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($ch, CURLOPT_POSTFIELDS, $rawBody);
                break;
            case self::METHOD_DELETE:
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
        }

        if (!$this->verifySSL) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        if ($this->cookiesJar) {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookiesJar);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookiesJar);

            if ($this->saveSessionCookies) {
                curl_setopt($ch, CURLOPT_COOKIESESSION, true);
            }
        }

        if ($this->followLocation) {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getHTTPHeaders());

        if ($this->debug) {
            $verbose = fopen('php://temp', 'wb+');
            curl_setopt($ch, CURLOPT_VERBOSE, 1);
            curl_setopt($ch, CURLOPT_STDERR, $verbose);
        }

        if ($this->proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
        }

        if ($this->compressRequest) {
            curl_setopt($ch, CURLOPT_ENCODING , $this->encoding);
        }

        $response = curl_exec($ch);
        $curlInfo = curl_getinfo($ch);

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
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $error = curl_error($ch);
        curl_close($ch);

        $this->responseCode = $httpCode;
        $this->responseInfo = $curlInfo;

        if ($error) {
            throw new ClientException('Connection error to '. $url . ' Return code: ' . $httpCode . ' Message: ' . $error);
        }

        $this->setResponseHeadersFromString(substr($response, 0, $headerSize));

        return substr($response, $headerSize);
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

}