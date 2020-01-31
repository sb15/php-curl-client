<?php
declare(strict_types=1);

namespace Sb\Curl;

use Sb\Curl\Exception\Exception as ClientException;

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
    public function setCookiesJar(string $cookiesJar): self
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
     * @param string $proxy
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
     * @param string $name
     * @param string $value
     * @return Client
     */
    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @return string|null
     */
    public function getResponseUrl(): ?string
    {
        if (array_key_exists('url', $this->responseInfo)) {
            return $this->responseInfo['url'];
        }

        return null;
    }

    /**
     * @param string $url
     * @return string
     * @throws Exception\Exception
     */
    public function get(string $url): string
    {
        return $this->request($url, self::METHOD_GET);
    }

    /**
     * @param string $url
     * @param array<array>|string|null $payload
     * @return string
     * @throws Exception\Exception
     */
    public function post(string $url, $payload = null): string
    {
        return $this->request($url, self::METHOD_POST, $payload);
    }

    /**
     * @param string $url
     * @param string|null $json
     * @return string
     * @throws Exception\Exception
     */
    public function postJSON(string $url, ?string $json = null): string
    {
        return $this->request($url, self::METHOD_POST, $json, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @param string $url
     * @param array<array>|string|null $payload
     * @return string
     * @throws Exception\Exception
     */
    public function put(string $url, $payload = null): string
    {
        return $this->request($url, self::METHOD_PUT, $payload);
    }

    /**
     * @param string $url
     * @return string
     * @throws Exception\Exception
     */
    public function delete(string $url): string
    {
        return $this->request($url, self::METHOD_DELETE);
    }

    /**
     * @param string $url
     * @param string $filename
     * @param string $type
     * @param array<array>|string|null $payload
     * @throws Exception\Exception
     */
    public function download(string $url, string $filename, string $type = self::METHOD_GET, $payload = null): void
    {
        $file = fopen($filename, 'wb+');
        if (!is_resource($file)) {
            throw new ClientException('Create destination file failed');
        }

        $this->request($url, $type, $payload, [], $file);
        fclose($file);
    }

    /**
     * @return array<string, string>
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
     * @return array<string, string>
     */
    public function getResponseInfo(): array
    {
        return $this->responseInfo;
    }

    /**
     * @param string $name
     * @return string|null
     */
    public function getResponseHeader(string $name): ?string
    {
        foreach ($this->responseHeaders as $responseHeaderName => $responseHeaderValue) {
            if (strtolower($responseHeaderName) === strtolower($name)) {
                return $responseHeaderValue;
            }
        }

        return null;
    }

}