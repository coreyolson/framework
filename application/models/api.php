<?php

namespace models;
use \__singleton as __singleton;

class api
{
    // Framework traits
    use __singleton;

    /**
     * API Agent.
     *
     * @var object
     */
    private static $api;

    /**
     * Endpoint.
     *
     * @var object
     */
    private static $endpoint = 'https://';

    /**
     * Headers.
     *
     * @var object
     */
    private static $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer {Token}',
    ];

    /**
     * Wrapper for GET, POST, PUT and DELETE requests.
     *
     * @return mixed
     */
    public static function __callStatic($method, $args = array())
    {
        // Ensure Agent Exists
        if ( is_null(self::$api) ) {

            // Framework Agent; Configure endpoint & headers
            self::$api = new libraries\caller( self::$endpoint, self::$headers );
        }

        // Submit Request to API
        return call_user_func_array([self::$api, $method], $args);
    }
}
