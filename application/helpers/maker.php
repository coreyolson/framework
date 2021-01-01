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
 * A helper class for creating controllers, models and template files.
 *
 * @method helpers\maker::model();
 * @method helpers\maker::controller();
 * @method helpers\maker::cron();
 * @method helpers\maker::class();
 */
namespace helpers;

class maker
{
    // Framework traits
    use __aliases, __singleton;

    // Alternative method names
    public static $__aliases = [
        'class'      => ['create'],
        'model'      => ['models', 'm'],
        'controller' => ['controllers', 'c'],
        'cron'       => ['crons', 'cronjob', 'cron_task'],
    ];

    /**
     * Wrapper function for creating model files.
     *
     * @var mixed
     */
    public static function model($name, $args = array(), $singleton = false)
    {
        // Wrapper
        self::class($name, 'models', $args, $singleton);
    }

    /**
     * Wrapper function for creating controller files.
     *
     * @var mixed
     */
    public static function controller($name, $args = array())
    {
        // Wrapper
        self::class($name, 'controllers', $args, false);
    }

    /**
     * Wrapper function for creating cron files.
     *
     * @var mixed
     */
    public static function cron($name, $args = array())
    {
        // Wrapper
        self::class($name, 'controllers/'.f::info()['config']['cron'], $args, false);
    }

    /**
     * Creates an executable PHP file skeleton.
     *
     * @var mixed
     */
    public static function class($name, $type, $args = array(), $singleton = false)
    {
        // Any psuedo instructions?
        foreach($args as $k => $arg) {

            // Psuedo instruction to create a __construct class
            ($arg != ':_CRUD')?:list($args[], $arg) = ['__construct', ':CRUD'];

            // Psuedo instruction
            if ($arg == ':CRUD') {

                // Add Instructions from psuedo instruction
                $args = array_merge($args, ['create', 'read', 'update', 'delete']);

                // Remove Pseudo Instruction
                unset($args[$k]);
            }
        }

        // Header
        $class = '<?php'
        ."\n"
        ."\n"   .'namespace '.str_replace('/', '\\', $type).';'
                .((!$singleton)?'':"\n".'use \__singleton as __singleton;')
        ."\n"
        ."\n"   .'class '.$name
        ."\n"   .'{';

                // Framework traits
                $class .= ((!$singleton)?'':"\n".'    use __singleton;');

                // Cron options settings
                $class .= (($type!='controllers/'.f::info()['config']['cron'])?''
                :"\n"   .'    public static $options = ['
                ."\n"   ."        'frequency' => 'everyFiveMinutes',"
                ."\n"   .'    ];');


        // Iterate components
        foreach($args as $arg ) {

            // Detect $ as variables
            if ( strpos($arg, '$') !== false ) {

                // Create a variable
                $class .= "\n".'    private static '.$arg.';'."\n";

            } else {

                // Create a method
                $class .= ''
                ."\n".'    public function '.$arg.'()'
                ."\n".'    {'
                ."\n"
                ."\n".'    }';
            }
        }

        // End of class
        $class .= "\n".'}';

        // Construct file path to write to
        $path = f::info()['directory'].'application/'.$type.'/'.$name.'.php';

        // Write new class to file
        return file_put_contents($path, $class);
    }
}
