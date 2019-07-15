<?php
/**
 * php-guard/curl <https://github.com/php-guard/curl>
 * Copyright (C) ${YEAR} by Alexandre Le Borgne <alexandre.leborgne.83@gmail.com>.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace PhpGuard\Curl;

use PhpGuard\Curl\Collection\CurlOptions;
use PhpGuard\Curl\Collection\Headers;

class CurlRequest
{
    /**
     * @var string
     */
    protected $url;
    /**
     * @var string
     */
    protected $method = 'GET';
    /**
     * @var mixed
     */
    protected $data;
    /**
     * @var Headers
     */
    protected $headers;
    /**
     * @var Curl
     */
    private $curl;
    /**
     * @var CurlOptions
     */
    private $curlOptions;

    /**
     * CurlRequest constructor.
     *
     * @param Curl       $curl
     * @param string     $url
     * @param string     $method
     * @param array|null $data
     * @param array      $headers
     * @param array      $curlOptions
     */
    public function __construct(Curl $curl, string $url, string $method = 'GET', $data = null, array $headers = [],
                                array $curlOptions = [])
    {
        $this->curl = $curl;
        $this->url = $url;
        $this->method = $method;
        $this->data = $data;
        $this->headers = new Headers($headers);
        $this->curlOptions = new CurlOptions($curlOptions);
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param string $method
     */
    public function setMethod(string $method): void
    {
        $this->method = $method;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data): void
    {
        $this->data = $data;
    }

    /**
     * @return Headers
     */
    public function getHeaders(): Headers
    {
        return $this->headers;
    }

    public function setHeaderContentType(string $contentType)
    {
        $this->headers['Content-Type'] = $contentType;
    }

    /**
     * @return CurlOptions
     */
    public function getCurlOptions(): CurlOptions
    {
        return $this->curlOptions;
    }

    /**
     * @param bool $throwExceptionOnHttpError
     *
     * @return CurlResponse
     *
     * @throws CurlError
     */
    public function execute(bool $throwExceptionOnHttpError = false): CurlResponse
    {
        $response = $this->curl->execute($this);

        if ($throwExceptionOnHttpError && $response->isError()) {
            throw new CurlError($response->raw(), $response->statusCode());
        }

        return $response;
    }

    public function resource()
    {
        // create curl resource
        $ch = curl_init();

        $data = $this->getData();
        if(is_array($data || is_object($data))) {
            if($this->getHeaders()->offsetGet(Headers::CONTENT_TYPE) === Headers::CONTENT_TYPE_JSON) {
                $data = json_encode($data);
            }
        }

        curl_setopt($ch, CURLOPT_URL, $this->getUrl());
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->getMethod());
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getHeaders()->toHttp());

        foreach ($this->getCurlOptions()->all() as $key => $value) {
            curl_setopt($ch, $key, $value);
        }

        return $ch;
    }
}
