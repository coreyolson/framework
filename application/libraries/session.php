<?php
/**
 * Framework
 * ------------------------------------------------
 * A minimalist PHP framework.
 *
 * @copyright   Copyright (c) 2010 - 2021 Corey Olson
 * @license     https://opensource.org/licenses/MIT
 * @link        https://github.com/coreyolson/framework
 *
 * // ########################################################################################
 *
 * A session library.
 *
 * @method libraries\session::session_id( $new );
 * @method libraries\session::regenerate();
 * @method libraries\session::get_all();
 * @method libraries\session::get( $var );
 * @method libraries\session::set( $var, $val );
 * @method libraries\session::remove( $var );
 * @method libraries\session::remove_all();
 * @method libraries\session::destroy();
 * @method libraries\session::id();
 * @method libraries\session::flashdata( $var );
 * @method libraries\session::isset_flashdata( $var );
 * @method libraries\session::set_flashdata( $var, $value [, $persist] );
 * @method libraries\session::keep_flashdata( [$var] [, $persist] );
 * @method libraries\session::clean_flashdata();
 */
namespace libraries;

class session
{
    // Framework traits
    use __aliases, __singleton;

    // Alternative method names
    public static $__aliases = [
        'get_all'   => ['all'],
    ];

    /**
     * Session identifier.
     *
     * @var string
     */
    private static $session;

    /**
     * Number of seconds to keep users logged in.
     *
     * @var int
     */
    private static $session_length = 3600;

    /**
     * Number of seconds to renew session identifier.
     *
     * @var int
     */
    private static $session_renew = 300;

    /**
     * Cookie name for session identifier
     *
     * @var int
     */
    private static $name = 'id_app';

    /**
     * Setup the session environment, check session exists, runs security.
     */
    public function __construct()
    {
        // Cookie name
        session_name(self::$name);

        // Cookie age
        session_set_cookie_params(self::$session_length);

        // Check for a valid session cookie
        if (!isset($_COOKIE[self::$name]) OR !isset($_COOKIE[self::$name][63])) {

            // Generate a new session id
            session_id(self::session_id(true));

        } else {

            // Use the user provided session
            session_id($_COOKIE[self::$name]);
        }

        // Starts the session
        session_start();

        // Security measures
        self::security();

        // Flashdata cleanup
        self::clean_flashdata();

        // User tracking
        self::tracking();
    }

    /**
     * Getter and setter method for session identifiers.
     *
     * @return string
     */
    public static function session_id($new = false)
    {
        // Get the current session identifier
        if (!$new) {

            // Session Identifier
            return self::$session;
        }

        // Create a new session identifier
        $id = sha1($_SERVER['REMOTE_ADDR']);

        do {
            // Add some randomness
            $id .= mt_rand(0, mt_getrandmax());

        // Keep going
        } while (strlen($id) < 64);

        // Additional entropy
        $entropy = (isset($_SERVER['HTTP_USER_AGENT']))

            // Some firewalls remove this
            ? $_SERVER['HTTP_USER_AGENT']

            // Generate if needed
            : helpers\str::generate(255);

        // Should be enough entropy to prevent collisions
        return self::$session = hash_hmac('sha256', uniqid($id, true), $entropy);
    }

    /**
     * Implements some safety measures for sessions.
     */
    private static function security()
    {
        // This is a new sessions
        if (!isset($_SESSION['__last'])) {

            // Timestamps
            $_SESSION = array(
                '__last' => time(),
                '__regeneration' => time(),
            );
        }

        // Logout old sessions
        if ($_SESSION['__last'] <= (time() - self::$session_length)) {

            // Destroy the data
            unset($_SESSION);

            // Sends back an invalid cookie
            setcookie(session_name(), '', time() - self::$session_length - 3600);

            // Ends the session
            session_destroy();

        } else {

            // Check for regeneration
            if ($_SESSION['__regeneration'] <= (time() - self::$session_renew)) {

                // Recreate session
                self::regenerate();
            }

            // Update last active time
            $_SESSION['__last'] = time();
        }
    }

    /**
     * Regenerate the session key.
     */
    public static function regenerate()
    {
        // Prevent changes
        session_write_close();

        // Store the data session
        $temp = $_SESSION;

        // Destroy this session
        unset($_SESSION);

        // Generate a new session id
        session_id(self::session_id(true));

        // Open the new session
        session_start();

        // Restore session data
        $_SESSION = $temp;

        // Update regeneration timestamp
        $_SESSION['__regeneration'] = time();

        // Add session security
        self::security();
    }

    /**
     * Retrieves All Session Data.
     *
     * @return array
     */
    public static function get_all()
    {
        // All data
        return $_SESSION;
    }

    /**
     * Get: Retrieve Session Data.
     *
     * @param string
     *
     * @return string
     */
    public static function get($var)
    {
        // Check that this exists
        if (!isset($_SESSION[$var])) {

            // Does not exist
            return false;
        }

        return $_SESSION[$var];
    }

    /**
     * Set: Store Session Data.
     *
     * @param string
     * @param mixed
     *
     * @return mixed
     */
    public static function set($var, $val)
    {
        return $_SESSION[$var] = $val;
    }

    /**
     * Unset: Stored Session Data.
     *
     * @param string
     *
     * @return void
     */
    public static function remove($var)
    {
        // Forget a session variable
        unset($_SESSION[$var]);
    }

    /**
     * Unset all Session Data.
     *
     * @return void
     */
    public static function remove_all()
    {
        // Iterate through all variables
        foreach($_SESSION as $key => $var) {

            // Remove
            unset($_SESSION[$key]);
        }
    }

    /**
     * Destroys data; and, session.
     *
     * @return void
     */
    public static function destroy()
    {
        // Remote data
        self::remove_all();

        // Delete session
        session_destroy();
    }

    /**
     * Get the Anonymous Session ID.
     *
     * @param bool
     *
     * @return string
     */
    public static function id($truncate = false)
    {
        // Check that $_COOKIE exists
        if (!isset($_COOKIE[self::$name])) {

            // Create a new session
            return self::session_id(true);
        }

        // Trunacte session ID
        if ($truncate !== false) {

            // Get a truncated version of the session ID
            return substr($_COOKIE[self::$name], 0, $truncate);
        }

        // Return session identifier
        return $_COOKIE[self::$name];
    }

    /**
     * Get the visitor's landing page.
     *
     * @return string
     */
    public static function landing()
    {
        // Get pages visited
        $pages = self::get('pages');

        // Page array exists
        if (!is_array($pages)) {

            // No landing page available
            return false;
        }

        // Return visitor's landing page
        return end($pages)['url'];
    }

    /**
     * Get the visitor's previous page.
     *
     * @return string
     */
    public static function back()
    {
        // Get pages visited
        $pages = self::get('pages');

        // Page array exists
        if (!is_array($pages)) {

            // No landing page available
            return false;
        }

        // Move cursor to end of pages array
        end($pages);

        // Return visitor's previous page
        return prev($pages)['url'];
    }

    /**
     * Return one-time use variable.
     *
     * @return mixed
     */
    public static function flashdata($var)
    {
        // Data lasts for one (default) request
        return $_SESSION['_flashdata'][$var]['value'];
    }

    /**
     * Checks if flashdata isset.
     *
     * @return mixed
     */
    public static function isset_flashdata($var)
    {
        // Data lasts for one (default) request
        return isset($_SESSION['_flashdata'][$var]);
    }

    /**
     * One-time use variables.
     *
     * @param 	string
     * @param 	mixed
     */
    public static function set_flashdata($var, $value, $persist = 1)
    {
        // Sets the flash data
        $_SESSION['_flashdata'][$var] = array(
            'value' => $value,
            'persist' => $persist + (($persist != 1) ? time() : 0),
            'time' => $persist != 1,
        );
    }

    /**
     * Persist the flashdata 1 more request, or X many seconds.
     *
     * @param 	string
     * @param 	int
     */
    public static function keep_flashdata($var = false, $persist = 1)
    {
        // Exit if there's no _flashdata
        if (!isset($_SESSION['_flashdata'])) {
            return false;
        }

        // Convenience
        $_flashdata = &$_SESSION['_flashdata'];

        // Keep all flashdata
        if (!$var) {

            // Iterate through flashdata
            foreach ($_flashdata as $var => $data) {

                // Request based flashdata
                if (!$_flashdata[$var]['time']) {

                    // Update request-based flashdata
                    $_flashdata[$var]['persist'] = $persist;
                }

                // Time based flashdata
                elseif ($persist > 1) {

                    // Update time-based flashdata
                    $_flashdata[$var]['persist'] = time() + $persist;
                } else {
                    // Keeps a single item
            $_flashdata[$var]['persist'] = $persist + (($persist != 1) ? time() : 0);
                }
            }
        }
    }

    /**
     * Flashdata cleanup.
     */
    public static function clean_flashdata()
    {
        // Exit if there's no _flashdata
        if (!isset($_SESSION['_flashdata'])) {
            return false;
        }

        // Convenience
        $_flashdata = &$_SESSION['_flashdata'];

        // Iterate the flashdata
        foreach ($_flashdata as $var => $data) {

            // Remove expired time-based flashdata
            if ($_flashdata[$var]['time'] and $_flashdata[$var]['persist'] < time()) {

                // Goodbye flashdata
                unset($_flashdata[$var]);
            }

            // Remove expired request-based flashdata
            elseif ($_flashdata[$var]['persist']-- <= 0) {

                // Goodbye flashdata
                unset($_flashdata[$var]);
            }
        }
    }

    /**
     * Simple user tracking.
     */
    private static function tracking()
    {
        // Check if tracking enabled
        if ( ! self::get('__tracking') ) {

            // Disabled
            return false;
        }

        // Get pages visited
        $pages = self::get('pages');

        // Page array exists
        if (!is_array($pages)) {

            // Create array
            $pages = array();
        }

        // Detect https by SERVER_PORT (80: http)
        $https = ($_SERVER['SERVER_PORT'] == 80) ? '' : 's';

        // Push browsing data to page history
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {

            // Add page to array
            $pages[] = array(
                'time' => time(),
                'url' => 'http'.$https.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],
                'page' => $_SERVER['REQUEST_URI'],
            );
        }

        // Pointer: Beginning
        reset($pages);

        // First (array) page key
        $first = key($pages);

        // Pointer: End
        end($pages);

        // Previously visited page
        $previous = count($pages) - 1;

        // Generate statistics
        $stats = array(
            'time_first' => $pages[$first]['time'],
            'time_last' => $pages[$previous]['time'],
            'time_online' => ($pages[$previous]['time'] - $pages[$first]['time']),
            'time_friendly' => round(($pages[$previous]['time'] - $pages[$first]['time']) / 60, 1).' minutes',
            'pages_visited' => count($pages),
        );

        // Record the session data
        self::set('pages', $pages);
        self::set('stats', $stats);
    }
}
