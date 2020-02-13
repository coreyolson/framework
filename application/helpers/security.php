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
 * A security class with helper security functions
 *
 * @method helpers\security::cost( $cost, $target );
 * @method helpers\security::password( $password );
 * @method helpers\security::verify( $password, $hash );
 * @method helpers\security::authenticate( $view );
 * @method helpers\security::login();
 * @method helpers\security::logout();
 * @method helpers\security::username( [$prefix] [, $length] );
 */
namespace helpers;

class security
{
    // Framework traits
    use __aliases, __singleton;

    // Alternative method names
    public static $__aliases = [
        'login'        => ['signin'],
        'logout'       => ['logoff', 'signoff', 'signout'],
        'authenticate' => ['auth', 'authentication'],
        'password'     => ['hash', 'hash_pwd', 'hash_password'],
        'verify'       => ['verify_pwd', 'verify_pass', 'verify_password'],
    ];

    /**
     * Default security difficulty.
     *
     * @var int
     */
    private static $cost = 12;

    /**
     * View for authentication.
     *
     * @var boolean
     */
    private static $view = false;

    /**
     * Sets the password hashing cost.
     *
     * @return mixed
     */
    public static function cost($cost = false, $target = 0.618)
    {
        // Generate a dynamic cost?
        if (!$cost) {

            do {
                // Mark starting time
                $start = microtime(true);

                // Generate a test password
                self::password('', ++self::$cost);

            // Continue until processing time is reached
            } while ((microtime(true) - $start) < $target);

            // Calculated
            return self::$cost;
        }

        // Update the cost
        self::$cost = $cost;
    }

    /**
     * Hash a password using the default PHP hashing algo.
     *
     * @return string
     */
    public static function password($pwd, $cost = false)
    {
        // Generate a password of certain cost
        return password_hash($pwd, PASSWORD_DEFAULT, ['cost' => (($cost)?:self::$cost)]);
    }

    /**
     * Verify a password against a hash.
     *
     * @return boolean
     */
    public static function verify($pwd, $hash)
    {
        // Verifies password against a stored hash
        return password_verify($pwd, $hash);
    }

    /**
     * Require secure user authentication.
     *
     * @param string
     *
     * @return void
     */
    public static function secure_authenticate($view = false, $port = false, $hook = 'before')
    {
        // Upgrade security if necessary
        (helpers\web::is_self())?:helpers\web::upgrade();

        // Wrapper for authenticate method
        self::authenticate($view, $port, $hook);
    }

    /**
     * Require user authentication.
     *
     * @param string
     *
     * @return void
     */
    public static function authenticate($view = false, $port = false, $hook = 'before')
    {
        // Authorization view
        self::$view = $view;

        // Allow port to login
        (!$port) ?:route::port($port);

        // Check for authentication
        libraries\session::get('__authorized') ?:framework::hook($hook, function(){

            // Authorization required
            libraries\page::end(self::$view);
        });
    }

    /**
     * User login.
     *
     * @return
     */
    public static function login($tracking = true)
    {
        // Basic authorization system for simple sites
        libraries\session::set('__authorized', true);

        // Basic page and statistical tracking
        libraries\session::set('__tracking', $tracking);
    }

    /**
     * User logoff.
     *
     * @return
     */
    public static function logout()
    {
        // Basic session is no longer authorized
        libraries\session::remove('__authorized');
    }

    /**
     * Generate a temporary username.
     *
     * @return string
     */
    public static function username($prefix = 'user', $length = 5)
    {
        // Temporary username
        $username = $prefix;

        // Randomly add numbers to prefix
        for ($i = 0; $i < $length; $i++) {

            // Add random character
            $username .= mt_rand(0,9);
        }

        // Random username for new users
        return $username;
    }
}
