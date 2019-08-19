<?php

namespace Sb\Curl\Traits;

trait Main
{


    /* settings */

    public function setConnectTimeout(int $connectTimeout)
    {
        $this->connectTimeout = $connectTimeout;

        return $this;
    }

    public function setTimeout(int $timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    public function setCookiesJar($cookiesJar)
    {
        $this->cookiesJar = $cookiesJar;

        return $this;
    }


    public function setVerifySSL($isVerify)
    {
        $this->verifySSL = $isVerify;

        return $this;
    }

    public function setSaveSessionCookies($isSaveSessionCookies)
    {
        $this->saveSessionCookies = $isSaveSessionCookies;

        return $this;
    }

    public function setFollowLocation($isFollowLocation)
    {
        $this->followLocation = $isFollowLocation;

        return $this;
    }

    /**
     * Default http proxy
     * For socks 5 use prefix socks5://
     *
     * @param $proxy
     * @return $this
     */
    public function setProxy($proxy)
    {
        $this->proxy = $proxy;

        return $this;
    }

    public function clearHeaders()
    {
        $this->headers = [];

        return $this;
    }

    public function setHeader($name, $value)
    {
        $this->headers[$name] = $value;

        return $this;
    }

    public function getHeader()
    {
        return $this->headers;
    }


    /* methods */

    public function get($url)
    {
        return $this->request($url, self::METHOD_GET);
    }

    public function post($url, $rawBody = null)
    {
        return $this->request($url, self::METHOD_POST, $rawBody);
    }

    public function put($url, $rawBody = null)
    {
        return $this->request($url, self::METHOD_PUT, $rawBody);
    }

    public function delete($url)
    {
        return $this->request($url, self::METHOD_DELETE);
    }


    /* response */

    public function getResponseHeaders(): array
    {
        return $this->responseHeaders;
    }

    public function getResponseCode(): ?int
    {
        return $this->responseCode;
    }

    public function getResponseInfo(): array
    {
        return $this->responseInfo;
    }

    public function getResponseHeader($name): ?string
    {
        foreach ($this->responseHeaders as $responseHeaderName => $responseHeaderValue) {
            if (strtolower($responseHeaderName) === strtolower($name)) {
                return $responseHeaderValue;
            }
        }

        return null;
    }


    /* debug */

    public function setDebug(): void
    {
        $this->debug = true;
    }

    public function getTrace()
    {
        return $this->trace;
    }

}