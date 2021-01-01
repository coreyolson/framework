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
 * Framework handles controller/method routing.
 *
 * @method framework::index()
 * @method framework::hook()
 * @method framework::autoloader()
 *
 */
class framework
{
    /**
     * Settings, variables and notes for developers.
     *
     * @var array
     */
    protected static $_ = ['version' => '0.9.9'];

    /**
     * Ignore files.
     *
     * @var array
     */
    protected static $_ignore = ['.DS_Store', 'Thumbs.db'];

    /**
     * Standard routing and initialization.
     */
    public static function index($map = 'home/index', $process = 'before/after', $cron = '_cron', $view = '~')
    {
        // Configuration
        self::$_['config'] = [

            // Default Controller::Action
            'mapping' => list(self::$_['controller'], self::$_['action']) = explode('/', $map),

            // Default Before/After Methods
            'process' => list($before, $after) = explode('/', $process),

            // Default Cron Task Location
            'cron' => $cron,

            // Prefix for views without controllers
            'view' => $view,
        ];

        // Parse request and separate on query string
        self::$_['route'] = explode('?', str_replace($_SERVER['SCRIPT_NAME'], '', $_SERVER['REQUEST_URI']));

        // Removes bad characters except ":" (colon), "~" (tilde), "/" (slash) and "." (period)
        self::$_['route'] = preg_replace('/[^a-zA-Z0-9:~\/\.\-\_]|:{2,}|\.{2,}/', '', self::$_['route'][0]);

        // Run Cron Schedule?
        (!isset($_REQUEST[$cron]))?:[helpers\cron::framework(),exit()];

        // Search backwards through class namespace
        for ($rte = self::$_['route']; false !== $pos = strrpos($rte, '/');) {

            // Iterate through file routing options (check default controller)
            foreach ([$rte, $rte.'/'.self::$_['controller']] as $attempt) {

                // Able to locate the file yet?
                (!($pos !== false AND file_exists($f = __DIR__.'/controllers'.$attempt.'.php')))

                    // Setting the controller to run based on routing
                    ?: [$discovered = include $f, $pos = 0, self::$_['controller'] = $attempt];
            }

            // Iterate to next slash
            $rte = substr($rte, 0, $pos);
        }

        // Ensure controller is not in parameters array
        self::$_['params'] = array_diff(

            // Parse parameters and normalize
            array_filter(explode('/', self::$_['route'])),

            // Parse controller and normalize
            array_filter(explode('/', self::$_['controller']))
        );

        // Default to the home controller if no file was routed
        (isset($discovered))?:[include __DIR__.'/controllers/'.explode('/', $map)[0].'.php'];

        // Get the controller that was just loaded
        self::$_['classes'] = get_declared_classes();

        // Get the actual controller name
        self::$_['controller'] = end(self::$_['classes']);

        // Check alignment of parameters and shift if the controller was not specified
        (!method_exists(self::$_['controller'], $_SERVER['REQUEST_METHOD'].'_'.reset(self::$_['params'])))

            // Realign by pushing the first parameter as action
            ?: self::$_['action'] = array_shift(self::$_['params']);

        // Only iterate existing methods
        $actions = array_intersect([

            // e.g., get(), post(), put(), delete()
            $method = strtolower($_SERVER['REQUEST_METHOD']),

            // e.g., before()
            $before,

            // e.g., get_before(), post_before(), put_before(), delete_before()
            $method.'_'.$before,

            // e.g., before_index(), before_action()
            $before.'_'.self::$_['action'],

            // e.g., before_get_index(), before_post_index()
            $method.'_'.$before.'_'.self::$_['action'],

            // e.g., get_index(), post_index(), put_index(), delete_index()
            $method.'_'.self::$_['action'],

            // e.g., after_get_index(), after_post_index(),
            $method.'_'.$after.'_'.self::$_['action'],

            // e.g., after_index(), after_action()
            $after.'_'.self::$_['action'],

            // e.g., get_after(), post_after(), put_after(), delete_after()
            $method.'_'.$after,

            // e.g., after()
            $after,

        // Gets Methods (actions) in Controller
        ], $methods = get_class_methods(self::$_['controller']));

        // Ensures parameters are returned orderly
        self::$_['params'] = array_values(self::$_['params']);

        // Preprocessing hooks
        (!isset(self::$_[$before]))?:framework::hook($before);

        // Does the method exist within controller?
        (isset($actions[5]))?:helpers\web::error_404();

        // When using parameters in URL, Action must also be specified in URL
        if ( isset(self::$_['params'][0]) AND stripos(self::$_['route'], self::$_['action']) === false ) {

            // Detect view
            if ( file_exists(__DIR__.'/views/'.self::$_['config']['view'].self::$_['params'][0].'.php') ) {

                // Allow "headless" views; i.e., views without controllers
                view::echo(self::$_['config']['view'].self::$_['params'][0]);

            // 404
            } else {

                // Action Not Found
                helpers\web::error_404();
            }

        // Controller
        } else {

            // Imitate __contstruct() or __destruct() on actions
            foreach ($actions as $key => $method) {

                // Execute method within the routed controller
                self::$_['controller']::{$method}();
            }
        }

        // Post-processing Hooks
        (!isset(self::$_[$after]))?:framework::hook($after);
    }

    /**
     * Add a processing hook to Framework
     */
    public static function hook($location, $closure = false)
    {
        // Run?
        if (!$closure) {

            // Iterate through instructions
            foreach(self::$_[$location] ?? [] as $instruction) {

                // Execute
                $instruction();
            }

        // Must be a valid hook type; disallow overwriting Framework internals
        } else if (!in_array($location, ['config', 'route', 'params', 'classes', 'controller', 'action'])) {

            // Add closure to instructions
            self::$_[$location][] = $closure;
        }
    }

    /**
     * Autoload controllers, models, libraries and helpers
     */
    public static function autoloader($class)
    {
        // Easily discoverable files; e.g., fully-qualified class names
        (!file_exists($file = __DIR__.'/'.str_replace('\\', '/', $class).'.php'))?:$i = include_once $file;

        // Search backwards through class namespace
        for ($ns = $class;!isset($i) AND false !== $pos = strrpos($ns, '\\');) {

            // Able to locate the file yet?
            (!file_exists($file = __DIR__.'/'.str_replace('\\', '/', substr($class, $pos)).'.php'))

                // Load the file and alias class when discovered
                ?:[include_once $file, class_alias(substr($class, $pos), $class)];

            // Remove trailing namespace for iteration
            $ns = rtrim(substr($class, 0, $pos + 1), '\\');
        }

        // Executes singleton methods
        (!method_exists($class, 'instance'))?:$class::instance();
    }
}

// ------------------------------------------------------------------------------------------------

/**
 * Provides convenient access for developers
 *
 * @method f::info()
 * @method f::view()
 */
class f extends framework
{
    /**
     * Returns standardized Framework information.
     *
     * @param mixed
     * @param mixed
     *
     * @return mixed
     */
    public static function info($mode = false, $echo = false)
    {
        // Immediately echo developer information?
        if ($echo AND in_array($mode, ['serialize', 'json_encode'])) {

            // Perform echo (with mode selection)
            echo self::info($mode);
        }

        // Iterate over Mapping and Process info
        foreach (['mapping', 'process'] as $config) {

            // Check to see if the configuration has been set
            if (isset(self::$_['config']) AND !is_array(self::$_['config'][$config])) {

                // Set the configuration
                self::$_['config'][$config] = explode('/', self::$_['config'][$config]);
            }
        }

        // Version
        $version = [

            // As string
            framework::$_['version']

            // As array of integers
            => array_map('intval', explode('.', framework::$_['version']))
        ];

        // Developer information
        return ($mode) ? $mode(self::info(false, $echo)) : [
            'version'    => $version,
            'directory'  => dirname(__DIR__).'/',
            'config'     => framework::$_['config']       ?? null,
            'controller' => framework::$_['controller']   ?? null,
            'action'     => framework::$_['action']       ?? null,
            'params'     => framework::$_['params']       ?? null,
            'request'    => framework::$_['request'][0]   ?? explode('?', $_SERVER['REQUEST_URI'])[0] ?? null,
            'query'      => $_SERVER['QUERY_STRING']        ?? null,
            'get'        => &$GLOBALS['_GET'],
            'post'       => &$GLOBALS['_POST'],
            'cookie'     => &$GLOBALS['_COOKIE'],
            'session'    => &$GLOBALS['_SESSION'],
            'files'      => get_included_files(),
        ];
    }

    /**
     * Shortcuts to framework objects using f::object()
     *
     * @param string
     * @param mixed
     *
     * @return object
     */
    public static function __callStatic($called, $args = array())
    {
        // Internal caching
        static $mapping = [];

        // Known aliases
        $aliases = array(
            'm' => ['models', 'm', 'model'],
            'v' => ['views', 'v', 'view'],
            'c' => ['controllers', 'c', 'controller'],
            'l' => ['libraries', 'l', 'library'],
            'h' => ['helpers', 'h', 'helper'],
        );

        // Generate class name; return objects already mapped (for performance)
        if ( isset($aliases[$called[0]]) AND isset( $mapping[$class = $aliases[$called[0]][0].'\\'.$args[0]] ) ) {

            // Object is mapped
            return $mapping[$class];
        }

        // Direct class reference?
        else if ( !isset($args[0]) ) {

            // Direct reference helpers or libraries
            foreach (['helpers', 'libraries'] as $type) {

                // Check for a cached mapping
                if ( isset($mapping[$type.'\\'.$called]) ) {

                    // Object is mapped
                    return $mapping[$type.'\\'.$called];
                }

                // Check for an existing file
                if ( in_array($called.'.php', scandir(__DIR__.'/'.$type)) ) {

                    // Dynamic call [alias] f::object()
                    if (method_exists($class = $type.'\\'.$called, 'instance')) {

                        // Instance Singleton
                        return $mapping[$class] = $class::instance();
                    }

                    // Create a new object
                    return new $class($args[1] ?? null);
                }
            }
        }

        // Check called against accepted alias list
        else if (in_array($called, $aliases[$called[0]])) {

            // Shortcut call to load a view
            if ( $aliases[$called[0]][0] == 'views' ) {

                // Provide the requested view
                return view::return($args[0], $args[1]);
            }

            // Dynamic call [alias] f::object()
            if (method_exists($class = str_replace('/', '\\', $class), 'instance')) {

                // Instance Singleton
                return $mapping[$class] = $class::instance();
            }

            // Create a new object
            return (isset($args[1])) ? new $class($args[1]) : new $class();
        }

        // Class or method could not be found in aliases or methods
        throw new \Exception('Call to ' . $called . '() could not be resolved.');
    }
}

// ------------------------------------------------------------------------------------------------

/**
 * Basic templates / views.
 *
 * @method view::echo()
 * @method view::return()
 *
 * @return closure
 */
class view extends framework
{
    // Framework traits
    use __aliases, __singleton;

    // Alternative method names
    public static $__aliases = [
        'return' => ['get'],
    ];

    /**
     * Wrapper method for quickly echo'ing content.
     *
     * @param string
     * @param array
     * @param boolean
     *
     * @return mixed
     */
    public static function echo($__name, $__args = array(), $__caching = false)
    {
        // Detect if singular view or an array of views
        if (is_array($__mixed = self::return($__name, $__args, $__caching))) {

            // Iterate through array
            foreach ($__mixed as $__view) {

                // Detect recursion
                if (is_array($__view)) {

                    // Flatten subdirectory (recursive) views
                    foreach (helpers\arr::flatten($__view) as $__flatview) {

                        // Display
                        echo $__flatview;
                    }

                } else {

                    // Display
                    echo $__view;
                }
            }

            // Finished
            return $__mixed;
        }

        // Display the view
        echo $__view = self::return($__name, $__args, $__caching);

        // Finished
        return $__view;
    }

    /**
     * Views load .php files by default, and extracts $args for templating.
     *
     * @param string
     * @param array
     * @param boolean
     *
     * @return string
     */
    public static function return($__name, $__args = array(), $__caching = false)
    {
        // Caching views
        static $__cache = [];

        // Detect directories
        if (is_dir($__path = __DIR__.'/views/'.$__name)) {

            // Get files within a directory
            foreach (array_diff(scandir($__path), array_merge(['.', '..'], self::$_ignore)) as $__file) {

                // Create an array of views
                $__viewArr[] = self::return($__name.'/'.explode('.php', $__file)[0], $__args);
            }

            // Finished
            return $__viewArr ?? null;
        }

        // Use caching?
        if ( $__caching ) {

            // Use cached string if available
            $__cache[$__name] = ($__cache[$__name])

                // Load string from file into static cache
                ?? file_get_contents(__DIR__.'/views/'.$__name.'.php');

            // Iterate over arguments supplied
            foreach ($__args as $__key => $__value) {

                // Keys to search for in cached string
                $__keys[] = '/\<\?(php)?=(\s+)?\$'.$__key.';(\s+)?\?\>/';

                // Values to replace
                $__values[] = $__value;
            }

            // Perform variable replacements on cached string
            return preg_replace($__keys, $__values, $__cache[$__name]);
        }

        // Encapsulates output
        ob_start();

        // Arrays passed to the view become $key => $variables for templating
        (!count($__args)) ?: extract($__args, EXTR_PREFIX_SAME, '_conflict_');

        // Either (a) includes the file or (b) exits on failure; get contents of buffer
        $__view = (include __DIR__.'/views/'.$__name.'.php') ? ob_get_contents() : exit();

        // Cleaning everything done here
        ob_end_clean();

        // Views sent back as strings
        return $__view;
    }
}

// ------------------------------------------------------------------------------------------------

/**
 * Provides route matching for custom routing.
 *
 * @method route::{request}('.*', function(){})
 *
 * @return closure
 */
class route extends framework
{
    /**
     * Route matching.
     *
     * @param string
     * @param string
     *
     * @return bool
     */
    public static function match($request, $route)
    {
        // Check REQUEST_METHOD method against route
        if (in_array($request, ['ANY', 'PORT', 'PORTS', $_SERVER['REQUEST_METHOD']])) {

            // Update the internal variables for developers
            framework::$_['request'] = explode('?', $_SERVER['REQUEST_URI']);

            // Removes bad characters except ":" (colon), "~" (tilde), "/" (slash) and "." (period)
            $url = (preg_replace('/[^a-zA-Z0-9:~\/\.\-\_]|:{2,}|\.{2,}/', '', framework::$_['request'][0])) ?: '/';

            // Route matching; Checks [1] literal matches, then [2] Regex
            if ($route == $url or preg_match('#^'.$route.'$#', $url)) {

                // Add to internal routes tracking array
                return framework::$_['route'][][$request] = $route;
            }
        }

        // No pattern matches
        return false;
    }

    /**
     * Custom REQUEST_METHOD in routes; e.g., route::{REQUEST_METHOD}('.*', function(){})
     *
     * @param string
     * @param mixed
     */
    public static function __callStatic($type, $args = array())
    {
        // Aloow multiple patterns
        if ( is_array($args[0]) ) {

            // Iterate over patterns
            foreach($args[0] as $passthrough) {

                // Attempt single pattern
                return route::$type($passthrough);
            }
        }

        // Check route against self::match()
        if (self::match(strtoupper($type), $args[0])) {

            // Run Closure if exists
            (!isset($args[1]))?:$args[1]();

            // Discontinue processing for PORTS, and immediately perform standard routing
            (!in_array(strtoupper($type), ['PORT', 'PORTS']))?: [framework::index(), exit()];
        }

        // Discontinue processing on TRUE
        (isset($args[2]) && $args[2]) ? exit() : 0;
    }
}

// ------------------------------------------------------------------------------------------------

/**
 * Provide the singleton pattern to a class
 */
trait __singleton
{
    /**
     * This is a singleton class.
     *
     * @var object
     */
    private static $instance;

    /**
     * Framework looks for an instance() method when loading a library.
     *
     * @return object
     */
    public static function instance()
    {
        // Check for an instance
        if (!isset(self::$instance)) {

            // Class name
            $class = __CLASS__;

            // Create a new instance
            self::$instance = new $class;
        }

        // Return existing instance
        return self::$instance;
    }
}

// ------------------------------------------------------------------------------------------------

/**
 * Provide method aliasing to a class
 */
trait __aliases
{
    /**
     * Method aliases. Slight performance impact when using method aliases.
     *
     * @param   string
     * @param   mixed
     *
     * @return  mixed
     */
    public static function __callStatic($called, $args = array())
    {
        // Iterate through methods
        foreach (self::$__aliases as $method => $shortcuts) {

            // Check against known aliases
            if (in_array($called, $shortcuts)) {

                // Dynamic method (alias) call with arbitrary arguments
                return call_user_func_array([__CLASS__, $method], $args);
            }
        }

        // Class or method could not be found in aliases or methods
        throw new \Exception(__CLASS__.'::'.$called.'() could not be resolved.');
    }

    /**
     * Method aliasing for objects.
     *
     * @param string
     * @param mixed
     *
     * @return mixed
     */
    public function __call($called, $args = array())
    {
        // Iterate through methods
        foreach (self::$__aliases as $method => $shortcuts) {

            // Check against known aliases
            if (in_array($called, $shortcuts)) {

                // Dynamic method (alias) call with arbitrary arguments
                return call_user_func_array([__CLASS__, $method], $args);
            }
        }

        // Class or method could not be found in aliases or methods
        throw new \Exception(__CLASS__.'->'.$called.'() could not be resolved.');
    }
}

// ------------------------------------------------------------------------------------------------

// Constants
define('__cwd', getcwd());
define('__local', strpos($_SERVER['HTTP_HOST'], '.local') !== false);

// Create the directory paths to each object type
foreach (['libraries', 'helpers', 'controllers', 'models'] as $namespace) {

    // Shortcuts for local namespace
    foreach (['framework', 'f', 'route', 'view', '__singleton', '__aliases'] as $class) {

        // Iterates through variants
        class_alias('\\'.$class, $namespace.'\\'.$class);
    }
}

// Framework autoloader
spl_autoload_register('framework::autoloader');

// Include Composer auto loading if available
(!file_exists($composer = '../vendor/autoload.php'))?:include $composer;
