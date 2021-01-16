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
 * A helper for updating the framework version across sites or servers.
 *
 * @method helpers\update::raw();
 * @method helpers\update::remap();
 * @method helpers\update::version();
 * @method helpers\update::verify();
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
    private static $_version = [];

    /**
     * Initialize update class.
     */
    public function __construct()
    {
        // Shown for example purposes
        $ignore_files = ['readme.md'];

        // Framework uses these by default
        $ignore_folders = ['storage', 'volume'];

        // Recursively discover files and hash
        self::mapper( f::info()['directory'], $ignore_folders, $ignore_files);
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

    /**
     * Remap directory
     *
     * @return void
     */
    public static function remap($dir, $ignore_folders, $ignore_files)
    {
        // Recursively map
        self::mapper( $dir );
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
     * Verify hash
     *
     * @var string
     * @var string
     *
     * @return boolean
     */
    public static function verify($content, $hash)
    {
        // Check contents against provided hash
        return $hash == md5($content).sha1($content);
    }

    /**
     * Recursively discovers files
     *
     * @return mixed
     */
    private static function mapper($dir = null, $ignore_folders = [], $ignore_files = [])
    {
        // Iterate through extensions
        foreach (helpers\file::contents($dir) as $file) {

            // Extension path
            $path = $dir.$file;

            // Create a "relative" file path
            $relative = str_replace(f::info()['directory'], '', $path);

            // Ignore these files
            if (in_array($file, $ignore_files)) {

                // Ignored
                continue;
            }

            // Check directory
            if ( is_dir($path) ) {

                // Ignore these files
                if (in_array($relative, $ignore_folders)) {

                    // Ignored
                    continue;
                }

                // Recursively
                self::mapper($path.'/', $ignore_folders, $ignore_files);

            // File
            } else {

                // Create file hashes for file path to denote changes
                self::$_version[$relative] = md5_file($path).sha1_file($path);
            }
        }
    }
}
