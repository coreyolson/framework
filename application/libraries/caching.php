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
 * A class for handling Server-side PHP caching
 *
 * @method placeholder class
 */
namespace libraries;

class caching
{
    // Framework traits
    use __aliases, __singleton;

    /**
     * Internal object.
     *
     * @var object
     */
    private $data;

    /**
     * Cache type.
     *
     * @var object
     */
    private $type;

    // Alternative method names
    public static $__aliases = [
        'memcached' => ['memcache'],
    ];

    /**
     * Initialize cache type.
     *
     * @return void
     */
    public function __construct($type = false)
    {
        // Provided?
        if ( $type ) {

            // Remember cache type
            $this->type = $type;

            // Cache type
            return $this->{$type}();
        }
    }

    /**
     * Create a memcache server.
     *
     * @return void
     */
    public function memcached($server = null, $port = 11211)
    {
        // Detect if memcache object exists
        if ( ! is_object($this->data) ) {

            // Create object
            $this->data = new \Memcached();

            // Add localhost as server by default
            $this->data->addServer($server ?? '127.0.0.1', $port);
        }

        /**
         * Detect Failures
         * @link https://www.php.net/manual/en/memcached.getresultcode.php
         */
        if ( $this->data->getResultCode() != 0 ) {

            // Error connecting with Memcache Server
            throw new \Exception('Memcached provided an unexpected code: '.$this->data->getResultCode().'.');
        }

        // Object
        return $this->data;
    }

    /**
     * Get variable by key.
     *
     * @return void
     */
    public function get($key)
    {
        // Translate to internal method
        return $this->{$this->type.'_get'}($key);
    }

    /**
     * Set variable by key.
     *
     * @return void
     */
    public function set($key, $var)
    {
        // Translate to internal method
        return $this->{$this->type.'_set'}($key, $var);
    }

    /**
     * Remove variable by key.
     *
     * @return void
     */
    public function unset($key)
    {
        // Translate to internal method
        return $this->{$this->type.'_unset'}($key);
    }

    /**
     * Get memcached variable by key.
     *
     * @return void
     */
    private function memcached_get($key)
    {
        // Return memcached variable
        return $this->data->get($key);
    }

    /**
     * Set memcached variable by key.
     *
     * @return void
     */
    private function memcached_set($key, $var)
    {
        // Return memcached variable
        return $this->data->set($key, $var);
    }

    /**
     * Remove memcached variable by key.
     *
     * @return void
     */
    private function memcached_unset($key)
    {
        // Return memcached variable
        return $this->data->delete($key);
    }
}
