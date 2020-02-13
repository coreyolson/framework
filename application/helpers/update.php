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
 * A helper for updating the framework version across sites or servers.
 *
 * @method helpers\update::_( );
 */
namespace helpers;

class update
{
    // Framework traits
    use __aliases, __singleton;

    // Alternative method names
    public static $__aliases = [
        'framework'   => ['upgrade', 'update'],
    ];

    // Internal information
    private static $_version;

    /**
     * Initialize update class.
     */
    public function __construct()
    {
        // Developer specified version
        self::$_version['version'] = f::info()['version'];

        // Framework folder
        $dir = f::info()['directory'].'application/';

        // Check extension folders
        foreach (['helpers', 'libraries'] as $ext) {

            // Extension folder
            $subdir = $dir.$ext.'/';

            // Iterate through extensions
            foreach (helpers\file::contents($subdir) as $file) {

                // Extension path
                $path = $subdir.$file;

                // Build file version info
                self::$_version[$ext][$file] = md5_file($path).sha1_file($path);
            }
        }

        // framework framework core
        $main = $dir.'Framework.php';

        // Build file information
        self::$_version['framework'] = md5_file($main).sha1_file($main);
    }

    /**
     * Framework version
     *
     * @return array
     */
    public static function version()
    {
        // framework.php
        return self::$_version;
    }

    /**
     * Replace current framework files with latest version
     *
     * @var string
     * @var string
     *
     * @return bool
     */
    public static function framework($mode = 'all', $file = null)
    {
        // Server framework
        // TODO: Connect to Framework

        // Update framework
        if ( in_array($mode, ['all', 'framework']) ) {

            // TODO: Update framework.php
        }

        // Update libraries
        if ( in_array($mode, ['all', 'ext', 'libraries']) ) {

            // TODO: Update all libraries
        }

        // Update helpers
        if ( in_array($mode, ['all', 'ext', 'helpers']) ) {

            // TODO: Update all helpers
        }

        // Update specific file
        if ( in_array($mode, ['file']) ) {

            // TODO: Update specific file
        }

        // Sucessful update
        return true;
    }

    /**
     * Returns raw file contents
     *
     * @var string
     * @var string
     *
     * @return mixed
     */
    public static function raw($file, $human = false)
    {
        // Locate file
        if ( file_exists($file) ) {

            // File contents
            $contents = file_get_contents($file);

            // Pre-wrap for human display?
            return ( $human )

                // Displays core files as copyable code
                ? '<pre>'.htmlentities($contents).'</pre>'

                // Machines
                : $contents;
        }

        // Failure
        return false;
    }
}
