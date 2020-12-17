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
 * A class for handling pages and layouts
 *
 * @method \libraries\page::theme( $view );
 * @method \libraries\page::title( $title );
 * @method \libraries\page::optimize( $enabled );
 * @method \libraries\page::append( $site_title );
 * @method \libraries\page::description( $description );
 * @method \libraries\page::disable();
 * @method \libraries\page::end( [$view] );
 * @method \libraries\page::nav( $path, $str );
 * @method \libraries\page::asset( $path );
 * @method \libraries\page::css( $path );
 * @method \libraries\page::js( $path );
 * @method \libraries\page::set( $mixed );
 * @method \libraries\page::get( $mixed );
 * @method \libraries\page::isset( $mixed );
 * @method \libraries\page::unset( $mixed );
 * @method \libraries\page::setting( $mixed );
 */
namespace libraries;

class page
{
    // Framework traits
    use __aliases, __singleton;

    // Alternative method names
    public static $__aliases = [
        'nav'     => ['navigation'],
        'setting' => ['settings'],
    ];

    /**
     * Internal class variables.
     *
     * @var array
     */
    private static $data;

    /**
     * Initialize the page.
     *
     * @return void
     */
    public function __construct()
    {
        // Encapsulates output
        ob_start();

        // Set defaults for a page
        self::$data = [

            // Sets a default page title
            'title' => ($_SERVER['REQUEST_URI']=='/')
                ? ucwords(\helpers\web::domain())
                : implode('::', explode('/', ltrim($_SERVER['REQUEST_URI'], '/'))).'() - '.ucwords(\helpers\web::domain()),

            // Sets a default page description
            'description' => false,

            // Optimization is disabled by default
            'optimize' => false,

            // Appended to page title
            'append' => false,

            // Variables
            'vars' => [],
        ];
    }

    /**
     * Sets a theme for rendering.
     *
     * @param string
     *
     * @return void
     */
    public static function theme($view)
    {
        // Clean output
        ob_end_clean();

        // Set the page design
        self::$data['theme'] = $view;

        // Encapsulates output
        ob_start();

        // Allow chaining
        return self::instance();
    }

    /**
     * Optimize page by stripping whitespace.
     *
     * @param string
     *
     * @return void
     */
    public static function optimize($enabled = true)
    {
        // Set the optimization value
        self::$data['optimize'] = $enabled;
    }

    /**
     * Appended to the page title.
     *
     * @param string
     *
     * @return void
     */
    public static function append($append)
    {
        // Append to the page title
        self::$data['append'] = $append;
    }

    /**
     * Get, or Set title for page theme.
     *
     * @param string
     *
     * @return mixed
     */
    public static function title($title = false)
    {
        // Get page title
        if (!$title)
            return self::$data['title'];

        // Set page title
        self::$data['title'] = $title;
    }

    /**
     * Set description for page theme.
     *
     * @param string
     *
     * @return void
     */
    public static function description($description)
    {
        // Set page description
        self::$data['description'] = $description;
    }

    /**
     * Disable a page theme.
     *
     * @return void
     */
    public static function disable()
    {
        // Disable the page theme
        unset(self::$data['theme']);

        // Disable the optimizer
        self::$data['optimize'] = false;
    }

    /**
     * Disable a page theme and immediately end.
     *
     * @param string
     *
     * @return void
     */
    public static function end($view = false)
    {
        // Disable page
        self::disable();

        // Display optional view?
        ($view === false)?: view::echo($view);

        // Stop processing
        exit();
    }

    /**
     * Returns a string if controller / action is matched.
     *
     * @return mixed
     */
    public static function nav($path, $str)
    {
        // Remove namespace from controllers if it exists
        $controller = \str_replace('controllers\\', '', f::info()['controller']);

        // Check if the path is an alternative route
        if ($controller == $path) {

            // Return the provided string
            return $str;
        }

        // Check if the path equals the controller plus a wildcard
        if ($path == explode('/', $controller)[0].'/*') {

            // Return the provided string
            return $str;
        }
        // Check if the path equals the controller action
        else if ($path == $controller.'/'.f::info()['action']) {

            // Return the provided string
            return $str;
        }

        // Check if the path is equal to the REQUEST_URI
        if ($_SERVER['REQUEST_URI'] == $path) {

            // Return the provided string
            return $str;
        }

        // Does not match
        return false;
    }

    /**
     * Generate asset list based on controller / action combination..
     *
     * @return string
     */
    public static function asset($path = '/', $variants = ['.min', '-min'], $ext = '')
    {
        // Normalize Path
        $href_path = helpers\str::finish($path, '/');

        // Normalize Controller
        $controller = str_replace('\\', '/', f::info()['controller']);

        // Exists?
        $search = [

            // Include Path
            'controllers'.rtrim(f::info()['request'], '/'),

            // Include Controller.js
            $controller.'/'.str_replace('controllers/', '', $controller),

            // Include Action.js
            $controller.'/'.f::info()['action'],
        ];

        // Iterate
        foreach ($search as $file) {

            // Normalize
            $href_file = $href_path.$file;

            // Iterate through variants (.min, -min, etc.)
            foreach (array_merge($variants, ['']) as $variant) {

                // Detect if file exists
                if (file_exists(__cwd.$href_file.$variant.$ext)) {

                    // Add file to be included in HTML string
                    $html[__cwd.$href_file.$variant.$ext] = $href_file.$variant.$ext;

                    // Stop iteration
                    continue 2;
                }
            }
        }

        // Return assets as string
        return implode("\n", $html ?? []);
    }

    /**
     * Generate CSS files to include based on controller / action combination.
     *
     * @return string
     */
    public static function css($cache = false, $path = '/css/', $variants = ['.min', '-min'], $ext = '.css')
    {
        // Iterate over asset links
        foreach (array_filter(explode("\n", self::asset($path, $variants, $ext))) as $src) {

            // Cache busting string
            $cacheBuster = ($cache) ? null :'?v='.filemtime(__cwd.$src);

            // Create HTML tags for JavaScript
            $html[] = helpers\html::tag('link', '', ['rel' => 'stylesheet', 'src' => $src]);
        }

        // Return assets as string
        return implode("\n", $html ?? []);
    }

    /**
     * Generate JavaScript files to include based on controller / action combination.
     *
     * @return string
     */
    public static function js($cache = false, $path = '/js/', $variants = ['.min', '-min'], $ext = '.js')
    {
        // Iterate over asset links
        foreach (array_filter(explode("\n", self::asset($path, $variants, $ext))) as $src) {

            // Cache busting string
            $cacheBuster = ($cache) ? null :'?v='.filemtime(__cwd.$src);

            // Create HTML tags for JavaScript
            $html[] = helpers\html::tag('script', '', ['src' => $src.$cacheBuster]);
        }

        // Return assets as string
        return implode("\n", $html ?? []);
    }

    /**
     * Set page variable when loading theme.
     *
     * @param mixed
     * @param mixed
     *
     * @return void
     */
    public static function set($mixed, $val = null)
    {
        // Check for array
        if ( is_array($mixed) ) {

            // Iterate through keypairs
            foreach ($mixed as $key => $val) {

                // Recursion
                self::set($key, $val);
            }

        // Normal setter
        } else {

            // Set the keypair
            self::$data['vars'][$mixed] = $val;
        }
    }

    /**
     * Get an existing page variable.
     *
     * @param string
     *
     * @return mixed
     */
    public static function get($var)
    {
        // Retrieve value
        return self::$data['vars'][$var];
    }

    /**
     * Detect if page variable isset.
     *
     * @param string
     *
     * @return mixed
     */
    public static function isset($var)
    {
        // Retrieve value
        return isset(self::$data['vars'][$var]);
    }

    /**
     * Unset an existing page variable.
     *
     * @param string
     *
     * @return void
     */
    public static function unset($var)
    {
        // Remove existing keypair
        unset(self::$data['vars'][$var]);
    }

    /**
     * Settings are ISSET or NULL.
     *
     * @param mixed
     *
     * @return void
     */
    public static function setting($mixed)
    {
        // Check for array
        if ( is_array($mixed) ) {

            // Iterate through keypairs
            foreach ($mixed as $index => $key) {

                // Recursion
                self::set($key, true);
            }

        // Normal setter
        } else {

            // Set the keypair
            self::$data['vars'][$mixed] = null;
        }
    }

    /**
     * Generate the page.
     *
     * @return void
     */
    public function __destruct()
    {
        // Check HTTP Status Code
        (http_response_code() == 200)?:exit();

        // Ensure a theme is selected
        if (isset(self::$data['theme'])) {

            // Prepare the page with content
            $__page = view::return(self::$data['theme'], array_merge([

                // Getting the page title
                '__title' => self::$data['title'] . self::$data['append'],

                // Getting the page description
                '__description' => self::$data['description'],

                // Getting the contents from the buffer
                '__content' => ob_get_contents(),

            // User variables
        ], self::$data['vars'] ?? []));

        } else {

            // Return a page without a theme
            $__page = ob_get_contents();
        }

        // Cleaning output
        ob_end_clean();

        // Send the page to the browser (Compressed) or normally
        echo (self::$data['optimize']) ? preg_replace('~>\s+<~', '> <', $__page) : $__page;
    }
}
