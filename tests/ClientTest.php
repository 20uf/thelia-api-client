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

namespace Thelia\Api\Client\Tests;

use Thelia\Api\Client\Client;

/**
 * Class ClientTest
 * @package Thelia\Api\Client\Tests
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class ClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Client
     */
    protected $client;

    protected $baseUrl;

    /**
     * @SetUp
     */
    public function setUp()
    {
        $this->client = new Client(
            "79E95BD784CADA0C9A578282E",
            "B45B9F244866F77E53255D6C0E0B60A2FA295CB0CFE25",
            $this->baseUrl = file_get_contents(__DIR__.DIRECTORY_SEPARATOR."server.txt")
        );
    }

    public function testClientReturnsAnArrayOnGetAction()
    {
        $response = $this->client->get("products");
        $body = $response->getBody()->getContents();

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertTrue(is_array($body));
        $this->assertGreaterThan(0, count($body));
    }

    public function testDoesNotThrowExceptionOnError()
    {
        $response = $this->client->get("incorrectUri");
        $body = $response->getBody()->getContents();

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertTrue(is_array($body));

        $this->assertArrayHasKey("error", $body);
    }

    public function testAcceptMagicCalls()
    {
        $response = $this->client->get("products", ["lang" => 'fr_FR']);
        $expected = $response->getBody()->getContents();
        $current = $this->client->listProducts(["lang" => "fr_FR"]);

        $this->assertEquals($expected, $current);
    }

    public function testCanCallCamelizedDashOnMagicCall()
    {
        $response = $this->client->listAttributeAvs();

        $this->assertEquals(200, $response->getStatusCode());
    }
}
