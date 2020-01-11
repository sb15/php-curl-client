<?php
declare(strict_types=1);

namespace Sb\Curl;

class Client extends ClientCore
{

    /**
     * @param int $connectTimeout
     * @return Client
     */
    public function setConnectTimeout(int $connectTimeout): self
    {
        $this->connectTimeout = $connectTimeout;

        return $this;
    }

    /**
     * @param int $timeout
     * @return Client
     */
    public function setTimeout(int $timeout): self
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * @param string $cookiesJar
     * @return Client
     */
    public function setCookiesJar(string  $cookiesJar): self
    {
        $this->cookiesJar = $cookiesJar;

        return $this;
    }

    /**
     * @param bool $isVerify
     * @return Client
     */
    public function setVerifySSL(bool $isVerify): self
    {
        $this->verifySSL = $isVerify;

        return $this;
    }

    /**
     * @param bool $isSaveSessionCookies
     * @return Client
     */
    public function setSaveSessionCookies(bool $isSaveSessionCookies): self
    {
        $this->saveSessionCookies = $isSaveSessionCookies;

        return $this;
    }

    /**
     * @param bool $isFollowLocation
     * @return Client
     */
    public function setFollowLocation(bool $isFollowLocation): self
    {
        $this->followLocation = $isFollowLocation;

        return $this;
    }

    /**
     * @param bool $useCompression
     * @return $this
     */
    public function setUseCompression(bool $useCompression): self
    {
        $this->useCompression = $useCompression;

        return $this;
    }

    /**
     * @param string $userAgent
     * @return Client
     */
    public function setUserAgent(string $userAgent): self
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    /**
     * Default http proxy
     * For socks 5 use prefix socks5://
     *
     * @param $proxy
     * @return $this
     */
    public function setProxy(string $proxy): self
    {
        $this->proxy = $proxy;

        return $this;
    }

    /**
     * @return Client
     */
    public function clearHeaders(): self
    {
        $this->headers = [];

        return $this;
    }

    /**
     * @param $name
     * @param $value
     * @return Client
     */
    public function setHeader($name, $value): self
    {
        $this->headers[$name] = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param $url
     * @return bool|string
     * @throws Exception\Exception
     */
    public function get($url)
    {
        return $this->request($url, self::METHOD_GET);
    }

    /**
     * @param $url
     * @param null $payload
     * @return bool|string
     * @throws Exception\Exception
     */
    public function post($url, $payload = null)
    {
        return $this->request($url, self::METHOD_POST, $payload);
    }

    /**
     * @param $url
     * @param null $json
     * @return bool|string
     * @throws Exception\Exception
     */
    public function postJSON($url, $json = null)
    {
        return $this->request($url, self::METHOD_POST, $json, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @param $url
     * @param null $payload
     * @return bool|string
     * @throws Exception\Exception
     */
    public function put($url, $payload = null)
    {
        return $this->request($url, self::METHOD_PUT, $payload);
    }

    /**
     * @param $url
     * @return bool|string
     * @throws Exception\Exception
     */
    public function delete($url)
    {
        return $this->request($url, self::METHOD_DELETE);
    }

    /**
     * @param $url
     * @param $filename
     * @param string $type
     * @param null $payload
     * @throws Exception\Exception
     */
    public function download($url, $filename, $type = self::METHOD_GET, $payload = null): void
    {
        $data = $this->request($url, $type, $payload);
        $file = fopen($filename, 'wb+');
        fwrite($file, $data);
        fclose($file);
    }

    /**
     * @return array
     */
    public function getResponseHeaders(): array
    {
        return $this->responseHeaders;
    }

    /**
     * @return int|null
     */
    public function getResponseCode(): ?int
    {
        return $this->responseCode;
    }

    /**
     * @return array
     */
    public function getResponseInfo(): array
    {
        return $this->responseInfo;
    }

    /**
     * @param $name
     * @return string|null
     */
    public function getResponseHeader($name): ?string
    {
        foreach ($this->responseHeaders as $responseHeaderName => $responseHeaderValue) {
            if (strtolower($responseHeaderName) === strtolower($name)) {
                return $responseHeaderValue;
            }
        }

        return null;
    }

}