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

/**
 * Class ClientUtil
 *
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class ClientUtil
{
    /**
     * @const string snake separator
     */
    const SNAKE_SEPARATOR = '-';

    /**
     * @var array known methods
     */
    private static $knownMethods = array(
        "GET",
        "POST",
        "PUT",
        "DELETE"
    );

    /**
     * Get known methods
     *
     * @return array
     */
    public static function getKnownMethods()
    {
        return self::$knownMethods;
    }

    /**
     * Snake to camel case
     *
     * @param $value
     *
     * @return string
     */
    public static function snakeToCamelCase($value)
    {
        $separator = self::SNAKE_SEPARATOR;

        return preg_replace_callback(
            "/\\{$separator}([a-z])/i",
            function($match) {
                return strtoupper($match[1]);
            },
            $value
        );
    }

    /**
     * Camel to snake case
     *
     * @param string $value
     *
     * @return string
     */
    public static function camelToSnakeCase($value)
    {
        return preg_replace_callback(
            "/([A-Z])/",
            function($match) {
                return self::SNAKE_SEPARATOR.strtolower($match[1]);
            },
            $value
        );
    }

    /**
     * Pascal to snake case
     *
     * @param string $value
     *
     * @return string
     */
    public static function pascalToSnakeCase($value)
    {
        if (strlen($value) === 0) {
            return $value;
        }

        $value = strtolower($value[0]) . substr($value, 1);

        return self::camelToSnakeCase($value);
    }
}
