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
 * A cron class for creating cron jobs (Linux based hosts only)
 *
 * @method helpers\cron::tasks();
 * @method helpers\cron::flush();
 * @method helpers\cron::schedule();
 * @method helpers\cron::unschedule();
 * @method helpers\cron::framework();
 * @method helpers\cron::reset();
 * @method helpers\cron::reset_all();
 */
namespace helpers;

class cron extends framework
{
    // Framework traits
    use __aliases, __singleton;

    // Alternative method names
    public static $__aliases = [
        'schedule'   => ['job', 'new_job', 'add_job', 'new_cron', 'create_cron', 'add_cron', 'register'],
        'unschedule' => ['unjob', 'remove_job', 'remove_cron', 'delete_job', 'delete_cron', 'unregister'],
    ];

    /**
     * Internal class storage.
     *
     * @var array
     */
    private static $data = array();

    /**
     * Time frequency ranges.
     *
     * @var array
     */
    private static $frequencies = array(

        // Common Ranges
        'minute'    => 60,
        'hourly'    => 3600,
        'daily'     => 86400,
        'weekly'    => 86400*7,
        'biweekly'  => 86400*7*2,
        'monthly'   => 86400*365/12,
        'quarterly' => 86400*365/4,
        'yearly'    => 86400*365,

        // Hourly Options
        'everyFiveMinutes'    => 300,
        'everyTenMinutes'     => 600,
        'everyFifteenMinutes' => 900,
        'everyThirtyMinutes'  => 1800,
        'everyHalfHour'       => 1800,

        // Daily Options
        'twiceDaily'  => 43200,
        'thriceDaily' => 28800,

        // Weekly Options
        'twiceWeekly'  => 86400*7/2,
        'thriceWeekly' => 86400*7/3,

        // Monthly Options
        'twiceMonthly'  => 86400*30/2,
        'thriceMonthly' => 86400*30/3,

        // Yearly Options
        'semiAnnual'   => 86400*365/2,
        'twiceYearly'  => 86400*365/2,
        'thriceYearly' => 86400*365/3,
    );

    /**
     * Initialize cron helper class.
     *
     * @return object
     */
    public function __construct()
    {
        // Initialize defaults
        self::$data['jobs'] = array();
        self::$data['crontab'] = '/usr/bin/crontab';

        // Reads the crontab file into a string
        $crontab = stream_get_contents(popen(self::$data['crontab'].' -l', 'r'));

        // Iterates through all non-empty lines from crontab file
        foreach (array_filter(explode(PHP_EOL, $crontab)) as $line) {

            // Ignore comment lines
            if (trim($line)[0] != '#') {

                // Parse jobs into a developer friendly format
                self::$data['jobs'][md5($line)] = self::parse($line);
            }
        }
    }

    /**
     * Get the crontab (all cron jobs) as an array.
     *
     * @return array
     */
    public static function tasks()
    {
        // Scheduled cron jobs
        return self::$data['jobs'];
    }

    /**
     * Clear the crontab by removing all cron jobs.
     */
    public static function flush()
    {
        // Create a blank array
        self::$data['jobs'] = array();

        // Save to crontab
        self::write();
    }

    /**
     * Create a new cron job and insert the task into the crontab.
     *
     * @param string
     *
     * @return array
     */
    public static function schedule($job)
    {
        // Creates a new cron job
        $new = self::$data['jobs'][md5($job)] = self::parse($job);

        // Save to crontab
        self::write();

        // Return a friendly cron job array
        return $new;
    }

    /**
     * Remove a cron job and remove the task from the crontab.
     *
     * @param string
     */
    public static function unschedule($job)
    {
        // Removes a scheduled cron job
        unset(self::$data['jobs'][$job]);

        // Save to crontab
        self::write();
    }

    /**
     * For automatically routed cron handling.
     *
     * @return mixed
     */
    public static function framework()
    {
        // Shortcuts for local namespace
        foreach (['framework', 'f', 'route', 'view', '__singleton', '__aliases'] as $class) {

            // Iterates through base Framework classes
            class_alias('\\'.$class, 'controllers\\'.f::info()['config']['cron'].'\\'.$class);
        }

        // More shortcuts for local namespace
        foreach (['libraries', 'helpers', 'controllers', 'models'] as $namespace) {

            // Iterates through known Framework namespaces
            class_alias('\\'.$class, $namespace.'\\controllers\\'.f::info()['config']['cron']);
        }

        // Parse and filter the route into routable pieces
        self::$_['params'] = array_values(array_filter(explode('/', self::$_['route'])));

        // First piece is the controller; default if not set
        self::$_['controller'] = ( array_shift(self::$_['params']) ) ?? self::$_['controller'];

        // Second piece is the action; default if not set
        self::$_['action'] = ( array_shift(self::$_['params']) ) ?? self::$_['action'];

        // Processing
        self::dispatcher();
    }

    /**
     * Resets runtime of task(s) to zero.
     *
     * @param mixed
     *
     * @return mixed
     */
    public static function reset($args)
    {
        // Check for singular
        if ( is_string($args) ) {

            // Convert to array
            $args = [$args];
        }

        // Cron storage file for runtime information
        $file = '../storage/.'.self::$_['config']['cron'].'.json';

        // Check for runtime file
        if ( file_exists($file) ) {

            // Retrieve runtime data
            self::$data['file'] = json_decode(file_get_contents($file), true);
        }

        // Framework tasks
        $tasks = self::local();

        // Iterate through crons
        foreach ($tasks as $task) {

            // Check if should be reset
            if ( in_array($task, $args) ) {

                // Reset the last run time zero
                self::$data['file'][$task]['last'] = 0;
            }
        }

        // Store time data
        file_put_contents($file, json_encode(self::$data['file'], JSON_PRETTY_PRINT));
    }

    /**
     * Wrapper shortcut to resets all task(s) to zero.
     *
     * @return mixed
     */
    public static function reset_all()
    {
        // Reset all tasks
        self::reset(self::local());
    }

    /**
     * Cron dispatcher for scheduled tasks
     *
     * @return mixed
     */
    private static function dispatcher()
    {
        // Runtimes
        $runtime = [];

        // 1-minute limit
        set_time_limit(59);

        // Disable page
        libraries\page::disable();

        // Prevent external calls
        helpers\web::only_self(false);

        // Cron storage file for runtime information
        $file = '../storage/.'.self::$_['config']['cron'].'.json';

        // Check for runtime file
        if ( file_exists($file) ) {

            // Retrieve runtime data
            self::$data['file'] = json_decode(file_get_contents($file), true);
        }

        // Lowercase all array keys
        self::$frequencies = array_change_key_case(self::$frequencies);

        // Framework tasks
        $tasks = self::local();

        // Request to run cron script?
        if (self::$_['route'] != '/' AND in_array(self::$_['controller'], $tasks)) {

            // Add namespace prefix to cron
            self::$_['controller'] = 'controllers\\'.self::$_['config']['cron'].'\\'. self::$_['controller'];

            // Run scheduled task
            return self::$_['controller']::{self::$_['action']}();
        }

        // Create a user_agent
        $agent = new libraries\agent;

        // Iterate through cron scripts
        foreach ($tasks as $task) {

            // Set the last execution time to zero if never run
            self::$data['file'][$task]['last'] = ( self::$data['file'][$task]['last'] ) ?? 0;

            // Add namespace prefix for the cron to access
            $cron = 'controllers\\'.self::$_['config']['cron'].'\\'.$task;

            // Generate path to cron to check for file modifacations
            $path = dirname(__DIR__).'/'.str_replace('\\', '/', $cron).'.php';

            // Set the last TTL to zero if it does not exist
            $ttl_expiry = ( self::$data['file'][$task]['ttl'] ) ?? null;

            // Recheck options data for fresh scheduling data
            if ( $ttl_expiry != self::$data['file'][$task]['ttl'] = filemtime($path) ) {

                // Retrieves scheduling options set within the cron
                self::$data['file'][$task]['options'] = ($cron::$options) ?? null;
            }

            // Check task schedule
            if ( self::in_schedule($task) ) {

                // Retrieves scheduling options set within the cron
                self::$data['file'][$task]['options'] = ($cron::$options) ?? null;

                // Detect custom actions specified in within cron
                $action = ( $cron::$options['action'] ) ?? self::$_['action'];

                // Cron path
                $cron_url = [

                    // Base Domain & Protocol URL
                    helpers\web::protocol().'://'.helpers\web::domain(),

                    // Trailing Segments & Query String
                    $task.'/'.$action.'/?'.self::$_['config']['cron'],
                ];

                // Long enough to initiate connection
                $agent->timeout(100);

                // Disregard SSL validation
                $agent->secure(false);

                // Framework PORT request
                $agent->port(implode('/', $cron_url));

                // Update last execution time for this task
                self::$data['file'][$task]['last'] = time();
            }
        }

        // Cleanup data for tasks that no longer exist
        foreach (array_diff(array_keys(self::$data['file']), $tasks) as $task) {

            // Remote from data
            unset(self::$data['file'][$task]);
        }

        // Store time data
        file_put_contents($file, json_encode(self::$data['file'], JSON_PRETTY_PRINT));
    }

    /**
     * Checks if a cron is within schedule; i.e., if it should run.
     *
     * @param string
     *
     * @return boolean
     */
    private static function in_schedule($task, $wait = 59)
    {
        // Limits excessive / overlapping runs; i.e., rate-limiting
        if ( time() - self::$data['file'][$task]['last'] < $wait ) {

            // Do not run
            return false;
        }

        // Check if scheduling options are set
        if ( !isset(self::$data['file'][$task]['options']) ) {

            // Running retrieves options
            return true;
        }

        // Convenience
        $options =& self::$data['file'][$task]['options'];

        // Check frequency
        if ( isset($options['frequency']) ) {

            // Convert friendly timeframes to seconds
            $frequency = ( self::$frequencies[strtolower($options['frequency'])] )

                // Allow a custom frequency in seconds
                ?? ( $options['frequency'] ) ?? $wait;

            // Limits execution to a certain frequency
            if ( time() - self::$data['file'][$task]['last'] < $frequency ) {

                // Do not run
                return false;
            }
        }

        // Check if day of the week is set
        if ( isset($options['dayOfWeek']) ) {

            // Check day of the week and current day
            if ( strtolower($options['dayOfWeek']) != strtolower(date('l')) ) {

                // Do not run
                return false;
            }
        }

        // Check if day of the month is set
        if ( isset($options['dayOfMonth']) ) {

            // Check day of the month and current day
            if ( strtolower($options['dayOfMonth']) != strtolower(date('j')) ) {

                // Do not run
                return false;
            }
        }

        // Check between times
        if ( isset($options['between']) ) {

            // Check if current time is within a minute to the specified time
            if ( strtotime($options['between'][0]) > time() OR time() > strtotime($options['between'][1]) ) {

                // Do not run
                return false;
            }
        }

        // Check specific at
        if ( isset($options['at']) ) {

            // Convert to unix timestamp
            $at = strtotime($options['at']);

            // Check if current time is within a minute to the specified time
            if ( floor(time() - ($wait/2)) > $at AND $at < ceil(time() + ($wait/2)) ) {

                // Do not run
                return false;
            }
        }

        // Check for closure setting
        if ( isset($options['closure']) ) {

            // Add namespace prefix for the cron to access
            $cron = 'controllers\\'.self::$_['config']['cron'].'\\'.$task;

            // Check that the closure exists
            if (method_exists($cron, $options['closure'])) {

                // Execute the closure and return
                return $cron::{$options['closure']}();
            }
        }

        // Process
        return true;
    }

    /**
     * Gets crons from configured controller subfolder.
     *
     * @return array
     */
    private static function local()
    {
        // Get scheduled crons from special controller folder
        $crons = array_diff(scandir('../application/controllers/'.self::$_['config']['cron'].'/'), ['.', '..']);

        // Read tasks from directory
        return $tasks = array_values(str_replace('.php', '', $crons));
    }

    /**
     * Writes the scheduled cron jobs back to the crontab.
     *
     * @param mixed
     *
     * @return mixed
     */
    private static function write($crons = array())
    {
        // Creates a temporary file for crontab preparation
        if (!is_writable($tmp = tempnam(sys_get_temp_dir(), 'cron'))) {

            // For some reason the system cannot write a temporary file
            throw new \Exception('Unable to prepare crontab because temporary file is not writable.');
        }

        // Iterate through the cron jobs
        foreach (self::$data['jobs'] as $jobs) {

            // Get cron commands
            $crons[] = $jobs['cron'];
        }

        // Prepare temporary file for copying to crontab
        file_put_contents($tmp, implode(PHP_EOL, $crons).PHP_EOL);

        // Returns false if writing to crontab fails
        return stream_get_contents(popen(self::$data['crontab'].' '.$tmp, 'r'));
    }

    /**
     * Parses crontab lines into developer friendly arrays.
     *
     * @param string
     *
     * @return array
     */
    private static function parse($job)
    {
        // Splits cron intervals and validated the cron schedule
        if (count($piece = preg_split('@ @', $job, null, PREG_SPLIT_NO_EMPTY)) < 5) {

            // Invalid cron schedule or failure in parsing
            throw new \Exception('Invalid cron schedule provided: '.implode(' ', $piece));
        }

        // Prepare variables
        $lastRunTime = $logFile = $logSize = $errorFile = $errorSize = $comment = null;

        // Cron command without the time schedule
        $cmd = implode(' ', array_slice($piece, 5));

        // Check for comments
        if (strpos($cmd, '#')) {

            // Separates the command and comment
            list($cmd, $comment) = explode('#', $cmd);
            $comment = trim($comment);
        }

        // Check for error file
        if (strpos($cmd, '2>>')) {

            // Separates the command and error file
            list($cmd, $errorFile) = explode('2>>', $cmd);
            $errorFile = trim($errorFile);
        }

        // Check for log file
        if (strpos($cmd, '>>')) {

            // Separates the command and log file
            list($cmd, $logPart) = explode('>>', $cmd);
            $logPart = explode(' ', trim($logPart));
            $logFile = trim($logPart[0]);
        }

        // Last run time checking (1)
        if (isset($logFile) && file_exists($logFile)) {
            $lastRunTime = filemtime($logFile);
            $logSize = filesize($logFile);
        }

        // Last run time checking (2)
        if (isset($errorFile) && file_exists($errorFile)) {
            $lastRunTime = max($lastRunTime ?: 0, filemtime($errorFile));
            $errorSize = filesize($errorFile);
        }

        // Default status
        $status = 'error';

        // Try to determine the status
        if ($logSize === null && $errorSize === null) {
            $status = 'unknown';

        // Status can be determined
        } elseif ($errorSize === null || $errorSize == 0) {
            $status = 'success';
        }

        // Developer friendly cron job array
        return array(
            'id'         => md5($job),
            'cron'       => $job,
            'minute'     => $piece[0],
            'hour'       => $piece[1],
            'dayOfMonth' => $piece[2],
            'month'      => $piece[3],
            'dayOfWeek'  => $piece[4],
            'command'    => trim($cmd),
            'comments'   => $comment,
            'logFile'    => $logFile,
            'logSize'    => $logSize,
            'errorFile'  => $errorFile,
            'errorSize'  => $errorSize,
            'status'     => $status,
        );
    }
}
