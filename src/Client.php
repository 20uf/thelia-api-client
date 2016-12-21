<?php

/*
 * This file is part of the Thelia project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * email : dev@thelia.net
 * web : http://www.thelia.net
 */

namespace Thelia\Api\Client;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Client as GuzzleClient;

/**
 * Class Client
 *
 * @author Benjamin Perche <bperche@openstudio.fr>
 * @author Michael COULLERET <michael@coulleret.pro>
 */
class Client
{
    /**
     * @var array
     */
    protected $headers;

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
    protected $uri;

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * constructor Client
     *
     * @param string $apiToken The api token
     * @param string $apiKey   The api key
     * @param string $uri      The request URI
     */
    public function __construct($apiToken, $apiKey, $uri)
    {
        $this->apiToken = $apiToken;
        $this->apiKey = ($apiKey);
        $this->uri = $uri;

        $this->client = new GuzzleClient(['base_uri' => $uri]);
    }

    /**
     * @param string $url     Url or Uri
     * @param array  $options Request options to apply.
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function get($url, array $options = array())
    {
        return $this->request("GET", $url, null, $options);
    }

    /**
     * @param string $url     Url or Uri
     * @param mixed  $body    Json or array
     * @param array  $options Request options to apply.
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function post($url, $body, array $options = array())
    {
        if (is_array($body)) {
            $body = json_encode($body);
        }

        return $this->request("POST", $url, $body, $options);
    }

    /**
     * @param string $url     Url or Uri
     * @param mixed  $body    Json or array
     * @param array  $options Request options to apply.
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function put($url, $body, array $options = array())
    {
        if (is_array($body)) {
            $body = json_encode($body);
        }

        return $this->request("PUT", $url, $body, $options);
    }

    /**
     * @param string $url     Url or Uri
     * @param array  $options Request options to apply.
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function delete($url, array $options = array())
    {
        return $this->request("DELETE", $url, null, $options);
    }

    /**
     * @param string $name
     * @param string $arguments
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function __call($name, $arguments)
    {
        $callable = null;
        if (method_exists($this, $name)) {
            $callable = [$this, $name];
        }

        foreach (ClientUtil::getKnownMethods() as $method) {
            if (0 === strpos($name, strtolower($method)) && strlen($name) > $methodLen = strlen($method)) {
                $entity = ClientUtil::pascalToSnakeCase(substr($name, $methodLen));
                $methodName = 'do'.ucfirst(strtolower($method));

                if (method_exists($this, $methodName)) {
                    $callable = [$this, $methodName];
                }

                array_unshift($arguments, $entity);

                break;
            }
        }

        if (null !== $callable) {
            return call_user_func_array($callable, $arguments);
        }

        throw new \BadMethodCallException(
            sprintf("The method %s::%s doesn't exist", __CLASS__, $name)
        );
    }

    /**
     * @param string $method
     * @param string $url
     * @param string $body
     * @param array  $options Request options to apply.
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    protected function request($method, $url, $body = '', array $options = array())
    {
        $request = $this->client->request(
            $method,
            $this->getRequestTarget($url, $body),
            array_merge(
                [
                    'headers' => $this->headers,
                    'body' => $body,
                ],
                $options
            )
        );

        return $request;
    }

    /**
     * Configures the options
     */
    protected function configure()
    {
        $this->headers["Authorization"] = "TOKEN ".$this->apiKey;
        $this->headers["content-type"] = 'application/json';
    }

    /**
     * Generate signature
     *
     * @param string $body
     *
     * @return string
     */
    protected function getSignature($body = '')
    {
        $secureKey = pack('H*', $this->apiKey);

        return hash_hmac('sha1', $body, $secureKey);
    }

    /**
     * Build a target url
     *
     * @param string $uri
     * @param string $body
     *
     * @return string
     */
    protected function getRequestTarget($uri, $body)
    {
        if (filter_var($uri, FILTER_VALIDATE_URL) === false) {
            $strUri = parse_url($this->uri, PHP_URL_QUERY);
            parse_str($strUri, $uri);
        }

        $query = http_build_query(array_merge($uri, ['sign' => $this->getSignature($body)]));

        return sprintf('%s?%s', $this->uri, $query);
    }
}
