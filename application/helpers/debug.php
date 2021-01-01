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
 * A class for debugging.
 *
 * @method helpers\file::dump();
 * @method helpers\file::infinite();
 * @method helpers\file::default();
 */
namespace helpers;

class debug
{
    // Framework traits
    use __aliases, __singleton;

    // Alternative method names
    public static $__aliases = [
        'dump' => ['var_dump', 'pretty', 'beautify'],
    ];

    /**
     * Alternative beautification when xdebug is not installed.
     *
     * @return void
     */
    public static function dump($var)
    {
        // Beautify a variable and display
        highlight_string("<?php\n\n".var_export($var, true).";\n\n");
    }

    /**
     * Xdebug depth infinite.
     *
     * @return void
     */
    public static function infinite()
    {
        // Set xdebug settings to infinite
        ini_set('xdebug.var_display_max_depth', -1);
        ini_set('xdebug.var_display_max_children', -1);
        ini_set('xdebug.var_display_max_data', -1);
    }

    /**
     * Xdebug depth defaults.
     *
     * @return void
     */
    public static function default()
    {
        // Set xdebug settings back to defaults
        ini_set('xdebug.var_display_max_depth', 3);
        ini_set('xdebug.var_display_max_children', 128);
        ini_set('xdebug.var_display_max_data', 512);
    }

    /**
     * Display errors?
     *
     * @var boolean
     *
     * @return void
     */
    public static function errors($display = false)
    {
        // Display all erors
        ini_set ('display_errors', ($display)?'on':'off');
        ini_set ('log_errors', ($display)?'on':'off');
        ini_set ('display_startup_errors', ($display)?'on':'off');
        ini_set ('error_reporting', ($display)?E_ALL:0);
    }
}
