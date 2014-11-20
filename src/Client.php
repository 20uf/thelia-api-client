<?php
/*************************************************************************************/
/*      This file is part of the Thelia package.                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : dev@thelia.net                                                       */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace Thelia\Api\Client;

use Guzzle\Http\Client as BaseClient;
use Guzzle\Http\ClientInterface;
use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\Response;

/**
 * Class Client
 * @package Thelia\Api\Client
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class Client
{
    /**
     * @var string
     */
    protected $apiToken;

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var string
     */
    protected $baseApiRoute;

    /**
     * @param $apiToken
     * @param $apiKey
     * @param string $baseUrl
     * @param ClientInterface $client
     * @param string $baseApiRoute
     */
    public function __construct($apiToken, $apiKey, $baseUrl = '', ClientInterface $client = null, $baseApiRoute = '/api/')
    {
        $this->apiToken = $apiToken;

        $this->apiKey = ($apiKey);

        $this->baseUrl = $baseUrl;

        $this->client = $client ?: new BaseClient();

        $this->baseApiRoute = $baseApiRoute;
    }

    // Api Actions
    /**
     * @param $name
     * @param array $loopArgs
     * @param array $headers
     * @param array $options
     * @return \Guzzle\Http\EntityBodyInterface|Response|mixed|string
     */
    public function sendList($name, array $loopArgs = array(), array $headers = array(), array $options = array())
    {
        return $this->call(
            "GET",
            $this->baseApiRoute . $name,
            $loopArgs,
            '',
            $headers,
            $options
        );
    }

    public function sendGet($name, $id, array $loopArgs = array(), array $headers = array(), array $options = array())
    {
        return $this->call(
            "GET",
            $this->baseApiRoute . $name . '/' . $id,
            $loopArgs,
            '',
            $headers,
            $options
        );
    }

    public function sendPost($name, $body, array $loopArgs = array(), array $headers = array(), array $options = array())
    {
        if (is_array($body)) {
            $body = json_encode($body);
        }

        return $this->call(
            "POST",
            $this->baseApiRoute . $name,
            $loopArgs,
            $body,
            array_merge(
                [
                    "Content-Type" => "application/json"
                ],
                $headers
            ),
            $options
        );
    }

    public function sendPut($name, $body, $id = null, array $loopArgs = array(), array $headers = array(), array $options = array())
    {
        if (is_array($body)) {
            $body = json_encode($body);
        }

        if (null !== $id && '' !== $id) {
            $id = '/' . $id;
        }

        return $this->call(
            "PUT",
            $this->baseApiRoute . $name . $id,
            $loopArgs,
            $body,
            array_merge(
                [
                    "Content-Type" => "application/json"
                ],
                $headers
            ),
            $options
        );
    }

    public function sendDelete($name, $id, array $loopArgs = array(), array $headers = array(), array $options = array())
    {
        return $this->call(
            "DELETE",
            $this->baseApiRoute . $name . '/' . $id,
            $loopArgs,
            '',
            $headers,
            $options
        );
    }

    // Client Routines

    /**
     * @param $method
     * @param $pathInfo
     * @param array $queryParameters
     * @param string $body
     * @param array $headers
     * @param array $options
     * @return \Guzzle\Http\EntityBodyInterface|Response|mixed|string
     */
    public function call($method, $pathInfo, array $queryParameters = array(), $body = '', array $headers = array(),  array $options = array())
    {
        $url = $this->baseUrl . $pathInfo;

        return $this->callUrl($method, $url, $queryParameters, $body, $headers, $options);
    }

    /**
     * @param $method
     * @param $fullUrl
     * @param array $query
     * @param string $body
     * @param array $headers
     * @param array $options
     * @return \Guzzle\Http\EntityBodyInterface|Response|mixed|string
     */
    public function callUrl($method, $fullUrl, array $query = array(), $body = '', array $headers = array(),  array $options = array())
    {
        $request = $this->prepareRequest($method, $fullUrl, $query, $body, $headers, $options);

        $response = $request->send();

        if (isset($options["handle_response"]) && false === $options["handle_response"]) {
            return $response;
        }

        return $this->handleResponse($request, $request->send());
    }


    /**
     * @param RequestInterface $request
     * @param Response $response
     * @return \Guzzle\Http\EntityBodyInterface|mixed|string
     */
    public function handleResponse(RequestInterface $request, Response $response)
    {
        if (!$response->isSuccessful()) {
            throw BadResponseException::factory($request, $response);
        }

        $body = $response->getBody(true);

        switch ($response->getContentType()) {
            case "application/json":
                $body = json_decode($body, true);
                break;
        }

        return [$response->getStatusCode(), $body];
    }

    /**
     * @param $method
     * @param $fullUrl
     * @param array $query
     * @param string $body
     * @param array $headers
     * @param array $options
     * @return RequestInterface
     */
    public function prepareRequest($method, $fullUrl, array $query = array(), $body = '', array $headers = array(), array $options = array())
    {
        $query["sign"] = $this->getSignature($body);
        $headers["Authorization"] = "TOKEN " . $this->apiToken;

        $fullUrl = $this->formatUrl($fullUrl, $query);

        $request = $this->client->createRequest(
            $method,
            $fullUrl,
            $headers,
            $body,
            $options
        );

        return $request;
    }

    /**
     * @param $url
     * @param array $params
     * @return string
     */
    protected function formatUrl($url, array $params)
    {
        if (false !== strpos($url, '?')) {
            list($url, $values) = explode('?', $url, 1);

            $params = array_merge(
                $this->retrieveArrayFromUrlParameters($values),
                $params
            );
        }

        $urlParameters = $this->retrieveUrlParametersFromArray($params);

        if ($urlParameters !== '') {
            $urlParameters = '?' . $urlParameters;
        }

        return $url . $urlParameters ;
    }

    // Client helpers

    /**
     * @param array $params
     * @return string
     */
    public function retrieveUrlParametersFromArray(array $params)
    {
        $string = '';

        foreach ($params as $key => $value) {
            $string .= $key;

            if ($value !=='' && $value !== null) {
                $string .= '=' . $value;
            }

            $string .= '&';
        }

        return substr($string, 0, -1);
    }

    /**
     * @param $strParams
     * @return array
     */
    public function retrieveArrayFromUrlParameters($strParams)
    {
        $table = array();

        $len = strlen($strParams);
        $key = '';
        $value = '';
        $toggle = false;

        for ($i = 0; $i < $len; ++$i) {
            if ($strParams[$i] == '&' && $key !== '') {
                // Store current var
                $table[$key] = $value;

                // Re-init values
                $key = '';
                $value = '';
                $toggle = false;
            } elseif ($strParams[$i] == '=') {
                $toggle = true;
            } elseif ($toggle) {
                $value .= $strParams[$i];
            } else {
                $key .= $strParams[$i];
            }
        }

        return $table;
    }

    /**
     * @param string $requestContent
     * @return string
     */
    protected function getSignature($requestContent = '')
    {
        $secureKey = pack('H*', $this->apiKey);

        return hash_hmac('sha1', $requestContent, $secureKey);
    }
}
 