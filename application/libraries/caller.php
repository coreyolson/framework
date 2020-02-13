<?php
/**
 * Framework
 * ------------------------------------------------
 * A minimalist PHP framework.
 *
 * @copyright   Copyright (c) 2010 - 2020 Corey Olson
 * @license     https://opensource.org/licenses/MIT
 * @link        https://github.com/coreyolson/framework
 *
 * // ########################################################################################
 *
 * A class for making API calls.
 *
 * @method libraries\caller::get();
 * @method libraries\caller::post();
 * @method libraries\caller::put();
 * @method libraries\caller::delete();
 */
namespace libraries;

class caller
{
    /**
     * API Agent.
     *
     * @var object
     */
    private $agent;

    /**
     * API Endpoint.
     *
     * @var object
     */
    private $endpoint;

    /**
     * JSON Encoding.
     *
     * @var boolean
     */
    private $json = true;

    /**
     * Rate Limiting.
     *
     * @var object
     */
    private $rate = 1;

    /**
    * Configure connection for API access.
     *
     * @return void
     */
    public function __construct($endpoint, $headers, $json = null, $rate = null)
    {
        // Framework Agent
        $this->agent = new libraries\agent;

        // Set API Endpoint
        $this->endpoint = $endpoint;

        // Iterate over Available Options
        foreach(['json', 'rate'] as $option) {

            // Detect Option Settings
            if ( !is_null(${$option}) ) {

                // Set Option
                $this->{$option} = $option;
            }
        }

        // Update headers
        $this->agent->http_headers($headers);
    }

    /**
     * Digests API Status Code
     *
     * @return int
     */
    public function code()
    {
        // API Response Code
        return $this->agent->code();
    }

    /**
     * Detects 2XX Response Codes
     *
     * @return boolean
     */
    public function success()
    {
        // Success Status Codes
        return (boolean) (substr($this->agent->code(),0,1)==2);
    }

    /**
     * Set API Rate Limit
     *
     * @var int
     *
     * @return void
     */
    public function rate($rate)
    {
        // Update API Rate Limiter
        $this->rate = $rate;
    }

    /**
     * Set JSON Encoding
     *
     * @var boolean
     *
     * @return void
     */
    public function json($boolean)
    {
        // Update JSON Encoding Status
        $this->json = $boolean;
    }

    /**
     * Wrapper for GET, POST, PUT and DELETE requests.
     *
     * @return mixed
     */
    public function __call($method, $args = array())
    {
        // Request Path
        $path = $args[0];

        // Fields? Default empty
        $data = $args[1] ?? null;

        // Rate Limiting
        usleep($this->rate);

        // Detect option to encode data payload
        $post = ($this->json) ? json_encode($data) : $data;

        // Submit API request
        $this->agent->{$method}($this->endpoint.$path, $post);

        // Return API message
        return ($this->json)

            // Automatic JSON Decoding
            ? json_decode($this->agent->body())

            // Raw Response
            : $this->agent->body();
    }
}
