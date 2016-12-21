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

use Thelia\Api\Client\ClientUtil;

/**
 * Class ClientTest
 * @package Thelia\Api\Client\Tests
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class ClientUtilTest extends \PHPUnit_Framework_TestCase
{
    public function testConvertsSnakeCaseToCamelCase()
    {
        $this->assertEquals(
            "helloWorld",
            ClientUtil::snakeToCamelCase("hello-world")
        );

        $this->assertEquals(
            "thisIsALongSentence",
            ClientUtil::snakeToCamelCase("this-is-a-long-sentence")
        );
    }

    public function testConvertsCamelCaseToSnakeCase()
    {
        $this->assertEquals(
            "hello-world",
            ClientUtil::camelToSnakeCase("helloWorld")
        );

        $this->assertEquals(
            "this-is-a-long-sentence",
            ClientUtil::camelToSnakeCase("thisIsALongSentence")
        );
    }

    public function testConvertsPascalCaseToSnakeCase()
    {
        $this->assertEquals(
            "hello-world",
            ClientUtil::pascalToSnakeCase("HelloWorld")
        );

        $this->assertEquals(
            "this-is-a-long-sentence",
            ClientUtil::pascalToSnakeCase("ThisIsALongSentence")
        );
    }
}
