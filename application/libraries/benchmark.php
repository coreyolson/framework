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
 * A class for benchmarking application performance
 *
 * @method libraries\benchmark::mem();
 * @method libraries\benchmark::system();
 * @method libraries\benchmark::peak();
 * @method libraries\benchmark::mark();
 * @method libraries\benchmark::first_mark();
 * @method libraries\benchmark::last_mark();
 * @method libraries\benchmark::since();
 * @method libraries\benchmark::diff();
 * @method libraries\benchmark::all_marks();
 * @method libraries\benchmark::nice_mark();
 */
namespace libraries;

class benchmark
{
    // Framework traits
    use __aliases, __singleton;

    // Alternative method names
    public static $__aliases = [
        'mark'       => ['record', 'break', 'measure', 'point', 'bench'],
        'system'     => ['sys', 'load', 'proc'],
        'diff'       => ['between', 'difference'],
        'memory'     => ['mem', 'memory_usage', 'mem_usage'],
        'peak'       => ['peak_mem', 'peak_memory_usage', 'peak_mem_usage'],
        'nice_mark'  => ['friendly', 'human'],
        'first_mark' => ['first'],
        'last_mark'  => ['last'],
        'all_marks'  => ['all'],
    ];

    /**
     * Benchmark data points.
     *
     * @var string
     */
    private static $points = array();

    /**
     * Setup and configure the benchmark library class.
     */
    public function __construct()
    {
        // Run the first benchmark
        self::mark('init');
    }

    /**
     * Get all benchmark data points.
     *
     * @return array
     */
    public static function all_marks($friendly = false)
    {
        // Create a local copy
        $tmp = self::$points;

        // Beautification
        if ($friendly) {

            // Iterate through benchmarks
            foreach ($tmp as $name => $benchmark) {

                // Beautify the memory usage into human readable
                $tmp[$name] = self::nice_mark($benchmark);
            }
        }

        // Return all benchmarks
        return $tmp;
    }

    /**
     * Get runtime since init.
     *
     * @return array
     */
    public static function runtime($precision = 4)
    {
        // Get runtime since initializing
        return round(microtime(true) - reset(self::$points)['time'], $precision);
    }

    /**
     * Get the first benchmark data.
     *
     * @return array
     */
    public static function first_mark()
    {
        // Get the first benchmark
        return reset(self::$points);
    }

    /**
     * Get the last benchmark data.
     *
     * @return array
     */
    public static function last_mark()
    {
        // Last benchmark
        return end(self::$points);
    }

    /**
     * Get benchmark since last or X benchmark.
     *
     * @return array
     */
    public static function since($mark = 0)
    {
        // Since last
        if (!$mark) {

            // Difference since last benchmark
            $compare = self::last_mark();
        }

        // Check benchmark exists
        elseif (!isset(self::$points[$mark])) {

            // Benchmark does not exist
            return false;
        } else {
            // Difference since named benchmark
            $compare = $mark;
        }

        // Create the new benchmark
        $recent = self::mark('since_'.count(self::$points));

        // Pass to self::diff() method for calculation
        return self::diff($compare, $recent);
    }

    /**
     * Get difference between two benchmark points.
     *
     * @return array
     */
    public static function diff($first = 0, $second = 0, $precision = 4)
    {
        // Check validity
        if (!$first) {

            // Unexpected parameters
            return false;
        }

        // Check if benchmark array
        elseif (!is_array($first)) {

            // Check benchmark has been set
            if (isset(self::$points[$first])) {

                // Get the first benchmark data point
                $first = self::mark($first);
            } else {
                // Benchmark has not been set
                return false;
            }
        }

        // Second benchmark check
        if (!$second) {

            // Create a second benchmark
            $second = self::mark('diff_'.count(self::$points));
        }

        // Check if benchmark array
        elseif (!is_array($second)) {

            // Get the second benchmark data point
            $second = self::mark($second);
        }

        return array(
            'memory' => $second['memory'] - $first['memory'],
            'time'   => round($second['time'] - $first['time'], $precision),
        );
    }

    /**
     * Record this data point.
     *
     * @return array
     */
    public static function mark($key = 0)
    {
        // Require name
        if (!$key) {

            // Create a random key name
            $key = count(self::$points);
        }

        // Check benchmark has been set
        if (isset(self::$points[$key])) {
            return self::$points[$key];
        }

        // Record and return the benchmark
        return self::$points[$key] = array(
            'memory' => memory_get_usage(),
            'time'   => microtime(true),
        );
    }

    /**
     * Get the current application memory usage.
     *
     * @return int
     */
    public static function memory($real_usage = false, $friendly = false)
    {
        // Beautify?
        if ($friendly) {

            // Return human friendly memory usage
            return self::bytes(memory_get_usage($real_usage));
        }

        // Memory usage
        return memory_get_usage($real_usage);
    }

    /**
     * Get the peak application memory usage.
     *
     * @return int
     */
    public static function peak($real_usage = false, $friendly = true)
    {
        // Beautify?
        if ($friendly) {

            // Return human friendly memory usage
            return self::bytes(memory_get_peak_usage($real_usage));
        }

        // Memory usage
        return memory_get_peak_usage($real_usage);
    }

    /**
     * Beautify bytes into manageble units and make time relative.
     *
     * @return array
     */
    public static function nice_mark($arg = false)
    {
        // Has a benchmark been provided
        if (!$arg) {

            // Use the last benchmark
            $tmp = self::last_mark();
        } elseif (is_array($arg)) {

            // User provided
            $tmp = $arg;
        } else {
            // Unexpected argument
            return false;
        }

        // Convert memory usage to human readable
        $tmp['memory'] = self::bytes($tmp['memory']);

        // Retrun human readable version
        return $tmp;
    }

    /**
     * Return the system load.
     *
     * @return array
     */
    public static function system()
    {
        // Function not available on windows
        if (defined('PHP_WINDOWS_VERSION_MAJOR')) {
            return false;
        }

        // Get the system load
        $load = sys_getloadavg();

        // Return system load
        return array(
            '1min'  => $load[0],
            '5min'  => $load[1],
            '15min' => $load[2],
        );
    }

    /**
     * Converts bytes to a human readable format.
     *
     * @param 	int
     * @param 	string
     *
     * @return string
     */
    private static function bytes($bytes = 0)
    {
        // Division by zero
        if (!$bytes) {
            return '0 B';
        }

        // Array of sizes
        $sizeArr = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB');

        // Gets the exponential size
        $exponent = floor(log($bytes, 1024));

        // Perform calculation of bytes to larger sizes and return
        return round($bytes / pow(1024, $exponent), 2).' '.$sizeArr[$exponent];
    }
}
