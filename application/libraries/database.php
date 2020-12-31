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
 * A class for working with databases
 *
 * @method libraries\database::reset();
 * @method libraries\database::connect();
 * @method libraries\database::disconnect();
 * @method libraries\database::row();
 * @method libraries\database::result();
 * @method libraries\database::result_array();
 * @method libraries\database::result_clear();
 * @method libraries\database::cursor();
 * @method libraries\database::num_rows();
 * @method libraries\database::num_fields();
 * @method libraries\database::fields();
 * @method libraries\database::last();
 * @method libraries\database::affected();
 * @method libraries\database::execute();
 * @method libraries\database::status();
 * @method libraries\database::table();
 * @method libraries\database::custom();
 * @method libraries\database::count();
 * @method libraries\database::select();
 * @method libraries\database::insert();
 * @method libraries\database::replace();
 * @method libraries\database::update();
 * @method libraries\database::delete();
 * @method libraries\database::truncate();
 * @method libraries\database::join();
 * @method libraries\database::where();
 * @method libraries\database::or_where();
 * @method libraries\database::like();
 * @method libraries\database::or_like();
 * @method libraries\database::not_like();
 * @method libraries\database::or_not_like();
 * @method libraries\database::lt();
 * @method libraries\database::or_lte();
 * @method libraries\database::lte();
 * @method libraries\database::or_lte();
 * @method libraries\database::gt();
 * @method libraries\database::or_gt();
 * @method libraries\database::gte();
 * @method libraries\database::or_gte();
 * @method libraries\database::between();
 * @method libraries\database::not_between();
 * @method libraries\database::is_null();
 * @method libraries\database::or_null();
 * @method libraries\database::not_null();
 * @method libraries\database::or_not_null();
 * @method libraries\database::ne();
 * @method libraries\database::or_ne();
 * @method libraries\database::group();
 * @method libraries\database::having();
 * @method libraries\database::or_having();
 * @method libraries\database::order();
 * @method libraries\database::limit();
 * @method libraries\database::offset();
 * @method libraries\database::statement();
 * @method libraries\database::queries();
 *
 *	Initialize class ...
 *
 *	$db = new libraries\database([
 *		'driver'	=> 'mysql',
 *		'dbname'	=> 'schema',
 *		'host'		=> 'localhost',
 *		'port'		=> 3303,
 *		'user'		=> 'usr',
 *		'password'	=> 'pwd',
 *	]);
 *
 *	Alternative DSN ...
 *
 *	$db = libraries\database([
 *		'dsn'		=> 'mysql:dbname=schema;host=localhost;port=3303;'
 *		'user'		=> 'usr',
 *		'password'	=> 'pwd',
 *	]);
 */
namespace libraries;

class database
{
    // Framework traits
    use __aliases;

    // Alternative method names
    public static $__aliases = [
        'results'      => ['result', 'rslt', 'result_arr', 'result_array', 'results_array', 'as_arr', 'as_array', 'as_arrays', 'fetchall'],
        'results_obj'  => ['as_obj', 'as_object', 'as_objects', 'fetchall_obj'],
        'result_clear' => ['clear', 'clean', 'results_clear', 'result_clean', 'results_clean'],
        'row'          => ['row_arr', 'row_array', 'row_as_arr', 'row_as_array'],
        'row_obj'      => ['row_obj', 'row_object', 'row_as_obj', 'row_as_object'],
        'fields'       => ['columns', 'get_fields', 'get_columns'],
        'last'         => ['last_id', 'last_insert', 'last_insert_id', 'id'],
        'affected'     => ['rows_affected', 'rows_changed', 'rows_updated', 'rows_impacted', 'impact', 'impacted'],
        'statement'    => ['query', 'stmt'],
        'between'      => ['within', 'range'],
        'not_between'  => ['not_within', 'outside'],
        'lt'           => ['less', 'less_than'],
        'lte'          => ['less_than_or_equals', 'less_than_equals'],
        'or_lt'        => ['or_less', 'or_less_than'],
        'or_lte'       => ['or_less_than_or_equals', 'or_less_than_equals'],
        'gt'           => ['more', 'more_than'],
        'gte'          => ['more_than_or_equals', 'more_than_equals'],
        'or_gt'        => ['or_more', 'or_more_than'],
        'or_gte'       => ['or_more_than_or_equals', 'or_more_than_equals'],
        'is_null'      => ['null'],
        'or_null'      => ['or_is_null'],
        'not_null'     => ['is_not_null'],
        'or_not_null'  => ['or_is_not_null'],
        'ne'           => ['not', 'not_equal', 'not_equals', 'not_equal_to'],
        'or_ne'        => ['or_not', 'or_not_equal', 'or_not_equals', 'or_not_equal_to'],
    ];

    /**
     * Inernal storage array.
     *
     * @var string
     */
    private $data;

    /**
     * Initialize the database class.
     *
     * @param 	array
     *
     * @return object
     */
    public function __construct($args)
    {
        // Checks connection args
        $this->configure($args);

        // Set defaults
        $this->defaults();
    }

    /**
     * Upon initialization configures the database handle (PDO link).
     *
     * @param mixed
     */
    private function configure($args)
    {
        // Check if user provided
        if (isset($args['user'])) {

            // Set the database user
            $this->data['user'] = $args['user'];
        }

        // Check if password provided
        if (isset($args['password'])) {

            // Set the database password
            $this->data['password'] = $args['password'];
        }

        // Allow custom connection (web sockets, etc.)
        if (isset($args['dsn'])) {

            // Not instantiated yet...
            return $this->data['dsn'] = $args['dsn'];
        }

        // Check if this driver exists
        elseif (!in_array($args['driver'], \PDO::getAvailableDrivers())) {

            // Driver does not exist on this installation
            throw new \Exception('PDO Driver for "'.$args['driver'].'" not installed.');
        }

        // Check for required parameters
        foreach (['user', 'password', 'dbname', 'host'] as $setting) {

            // Ensure username and password have been provided
            if (!array_key_exists($setting, $args)) {

                // Driver does not exist on this installation
                throw new \Exception('Required PDO connection value "'.$setting.'" not set.');
            }
        }

        // Set database (for table reference)
        $this->data['dbname'] = $args['dbname'];

        // Create dsn string
        $this->data['dsn'] = $args['driver'].':';

        // Iterate through PDO settings
        foreach ($args as $setting => $value) {

            // Create the DSN from these settings
            if (in_array($setting, ['dbname', 'host', 'port'])) {

                // Concatenate the DSN string
                $this->data['dsn'] .= $setting.'='.$value.';';
            }
        }

        // Set database type
        $this->data['driver'] = strtolower($args['driver']);

        // Set database column separator
        $this->data['column'] = ( $this->data['driver'] == 'mysql' ) ? '`': '"';
    }

    /**
     * Configures the default query statement parameters.
     */
    private function defaults()
    {
        // Reset internals
        $this->reset();

        // Determines database
        $this->dbname();

        // Set connection information
        $this->data['connection'] = false;
    }

    /**
     * Resolves the database name.
     */
    private function dbname()
    {
        // Getter or Configure
        if (isset($this->data['dbname'])) {

            // Return schema name
            return $this->data['dbname'];
        }

        // Iterate through the DSN provided
        foreach (preg_split('/(:|;)/', $this->data['dsn']) as $item) {

            // Separate on key / value pairs
            if ($keypair = explode('=', $item)) {

                // Place into a temporary array
                $tArr[$keypair[0]] = (!isset($keypair[1])) ?: $keypair[1];
            }
        }

        // Check for a scheme name
        if (isset($tArr['dbname'])) {

            // Set the database name
            return $this->data['dbname'] = $tArr['dbname'];
        }

        // Database name not provided
        throw new \Exception('No database name specified in DSN string');
    }

    /**
     * Deeper (HARD) resets of internals for new queries.
     *
     * @param bool
     *
     * @return object
     */
    private function hard()
    {
        // Default statement table
        $this->data['table'] = false;

        // Default query type
        $this->data['type'] = 'SELECT';

        // Default query parameters
        $this->select('*');
    }

    /**
     * Resets the internals for new queries.
     *
     * @param bool
     *
     * @return object
     */
    public function reset($hard = true)
    {
        // Set result information
        $this->data['result'] = false;

        // Set affected information
        $this->data['affected'] = false;

        // Set fetch information
        $this->data['fetched'] = false;

        // Default parameters
        $this->data['custom'] = false;

        // Default normal query
        $this->data['count'] = false;

        // Default parameters
        $this->data['params'] = false;

        // Soft or hard reset
        if ($hard) {

            // Deep reset
            $this->hard();
        }

        // Default join clause statement
        $this->data['join'] = false;

        // Default where clause statement
        $this->data['where'] = false;

        // Default group by statement
        $this->data['group'] = false;

        // Default having statement
        $this->data['having'] = false;

        // Default order for queries
        $this->data['order'] = false;

        // Default limit for queries
        $this->data['limit'] = false;

        // Default offset for queries
        $this->data['offset'] = false;

        // Allow chaining
        return $this;
    }

    /**
     * Connect to the database or return database link.
     *
     * @return object
     */
    public function connect()
    {
        // Check for existing connection
        if (isset($this->data['dbh'])) {

            // Return the existing connection handle
            return $this->data['dbh'];
        }

        // Set connection information
        $this->data['connection'] = true;

        // Create and return the new database connection
        return $this->data['dbh'] = new \PDO($this->data['dsn'], $this->data['user'], $this->data['password']);
    }

    /**
     * Disconnect to the database handle.
     */
    public function disconnect()
    {
        // Set connection information
        $this->data['connection'] = false;

        // Destroy PDO and disconnect
        return $this->data['dbh'] = null;
    }

    /**
     * Returns results row by row in a while loop, or a specific row by number
     * or a row by a friendly name like first, last, next or previous, etc.
     *
     * @return mixed
     */
    private function _row($mixed = false)
    {
        // Check fetched status
        if (!$this->data['fetched']) {

            // Fetch the results
            $this->results_obj();
        }

        // Specific row number
        if (is_int($mixed)) {

            // Check if specific row exists
            if (isset($this->data['fetched'][($mixed - 1)])) {

                // Return specific row
                return $this->data['fetched'][($mixed - 1)];
            } else {
                // Row does not exist
                return false;
            }
        }

        // Check for returning the first row
        if (in_array($mixed, ['first', 'first_row', 'reset', 'beginning'])) {

            // Returns the first row
            return reset($this->data['fetched']);
        }

        // Check for returning the last row
        elseif (in_array($mixed, ['last', 'last_row', 'end', 'ending'])) {

            // Returns the first row
            return end($this->data['fetched']);
        }

        // Check for returning the next row
        elseif (in_array($mixed, ['next', 'next_row'])) {

            // Returns the first row
            return next($this->data['fetched']);
        }

        // Check for returning the previous row
        elseif (in_array($mixed, ['prev', 'prev_row'])) {

            // Returns the first row
            return prev($this->data['fetched']);
        }

        // Get current row using the pointer
        $row = current($this->data['fetched']);

        // Move the cursor
        next($this->data['fetched']);

        // Return the row result
        return $row;
    }

    /**
     * Gets a row as an array.
     *
     * @return mixed
     */
    public function row($mixed = false)
    {
        // Pass to $this->_row();
        $row = $this->_row($mixed);

        // False check
        if (!$row) {

            // Invalid or end of rows
            return false;
        }

        // Cast as an array
        return (array) $row;
    }

    /**
     * Gets a row as an object.
     *
     * @return mixed
     */
    public function row_obj($mixed = false)
    {
        // Pass to $this->_row();
        return $this->_row($mixed);
    }

    /**
     * Gets the results of a query as an array of arrays.
     *
     * @return mixed
     */
    public function results()
    {
        // Run query
        $this->execute();

        // Statuc checking
        if (!$this->status()) {

            // Query failed
            return false;
        }

        // Check fetched status
        if ($this->data['fetched']) {

            // Rewind the array
            if (reset($this->data['fetched'])) {

                // Check to not mix object / array types
                if (is_array($this->data['fetched'][0])) {

                    // Return the cached results
                    return $this->data['fetched'];

                } else {
                    // Do not allow type mixing
                    return false;
                }
            }
        }

        // Return results as an array of arrays
        return $this->data['fetched'] = $this->data['result']->fetchAll();
    }

    /**
     * Gets the results of a query as an array of objects.
     *
     * @return object
     */
    public function results_obj()
    {
        // Run query
        $this->execute();

        // Statuc checking
        if (!$this->status()) {

            // Query failed
            return false;
        }

        // Check fetched status
        if ($this->data['fetched']) {

            // Rewind the array
            if (reset($this->data['fetched'])) {

                // Check to not mix object / array types
                if (is_object($this->data['fetched'][0])) {

                    // Return the cached results
                    return $this->data['fetched'];

                } else {
                    // Do not allow type mixing
                    return false;
                }
            }
        }

        // Return results as an array of object
        return $this->data['fetched'] = $this->data['result']->fetchAll(\PDO::FETCH_OBJ);
    }

    /**
     * Clears the database cursor, fetched results and result.
     */
    public function result_clear()
    {
        // Clear the fetched results
        $this->data['fetched'] = false;

        // Close the PDO cursor
        $this->data['result']->closeCursor();

        // Clear the result
        $this->data['result'] = false;

        // Set affected information
        $this->data['affected'] = false;
    }

    /**
     * Gets the number of rows that have been fetched.
     *
     * @return int
     */
    public function num_rows()
    {
        // Check fetched status
        if (!$this->data['fetched']) {

            // Fetch the results
            $this->results();
        }

        // Detect count param
        if ($this->data['count']) {

            // Get result as a number
            return $this->data['fetched'][0][0];
        }

        // Get the number of rows
        return count($this->data['fetched']);
    }

    /**
     * Gets the number of fields from the table.
     *
     * @return int
     */
    public function num_fields()
    {
        // Check fetched status
        if (!$this->data['fetched']) {

            // Fetch the results
            $this->results();
        }

        // Get the number of rows
        return count($this->row('first')) / 2;
    }

    /**
     * Gets fields from the table.
     *
     * @return array
     */
    public function fields()
    {
        // Check fetched status
        if (!$this->data['fetched']) {

            // Fetch the results
            $this->results();
        }

        // Iterate through fields
        foreach (array_keys($this->row('first')) as $key) {

            // Remove numberic keys
            if (!is_numeric($key)) {

                // Add to array
                $fArr[$key] = $key;
            }
        }

        // Return field array
        return $fArr;
    }

    /**
     * Returns the last insert id.
     *
     * @return int
     */
    public function last()
    {
        // Return last insert ID from database
        return $this->data['dbh']->lastInsertId();
    }

    /**
     * Returns number of rows affected by a query statement.
     *
     * @return int
     */
    public function affected()
    {
        // Return number of affected rows
        return $this->data['affected'];
    }

    /**
     * Connects to database and executes query.
     *
     * @return mixed
     */
    public function execute()
    {
        // Connection
        $this->connect();

        // Prevent multiple queries
        if ($this->status()) {

            // Query already executed
            return $this;
        }

        // Count number of queries
        $this->data['queries'] = 1 + ($this->data['queries'] ?? 0);

        // Selection (Result) or Custom (Unknown) query types
        if ($this->data['type'] == 'SELECT' or $this->custom()) {

            // Set result information
            $this->data['result'] = $this->data['dbh']->query($this->statement());

        } else {
            // Altering (UPDATE, INSERT, REPLACE, DELETE) queries
            $this->data['affected'] = $this->data['dbh']->exec($this->statement());
        }

        // Allow chaining
        return $this;
    }

    /**
     * Get the query status or result in boolean form.
     *
     * @return bool
     */
    public function status()
    {
        // Converts the result into boolean status
        return ($this->data['result'] !== false OR $this->data['affected'] !== false ) ? true : false;
    }

    /**
     * Select a table for the query statement.
     *
     * @param mixed
     *
     * @return object
     */
    public function table($table)
    {
        // Hard reset
        $this->reset();

        // Update the table to use
        $this->data['table'] = $this->data['dbname'].'.'.$table;

        // Allow chaining
        return $this;
    }

    /**
     * Use a custom query statement.
     *
     * @param string
     *
     * @return object
     */
    public function custom($str = false)
    {
        // Getter or Setter
        if (!$str) {

            // Return the custom string
            return $this->data['custom'];
        }

        // Hard reset
        $this->reset();

        // Set custom query statement
        $this->data['custom'] = $str;

        // Allow chaining
        return $this;
    }

    /**
     * Count rows in table.
     *
     * @param mixed
     *
     * @return object
     */
    public function count($table)
    {
        // Select table
        $this->table($table);

        // Special parameter
        $this->data['params'] = ' count(*) as count';

        // Special flag
        $this->data['count'] = true;

        // Allow chaining
        return $this;
    }

    /**
     * Create statement parameters for select queries.
     *
     * @param mixed
     *
     * @return object
     */
    public function select(...$args)
    {
        // Soft reset
        $this->reset(false);

        // Update type
        $this->data['type'] = 'SELECT';

        // Getter or Setter
        if (!count($args)) {

            // Default to * selection
            $this->data['params'] = '*';
        }

        // Check for arrays
        if (is_array($args[0])) {

            // Expand array
            $args = $args[0];
        }

        // Set column separator
        $c = ($args[0] == '*') ? '' : $this->data['column'];

        // Set the selection parameters
        $this->data['params'] = ' '.$c.implode($c.', '.$c, $args).$c;

        // Allow chaining
        return $this;
    }

    /**
     * Create statement parameters for insert queries.
     *
     * @param mixed
     *
     * @return object
     */
    public function insert(...$args)
    {
        // Soft reset
        $this->reset(false);

        // Update type
        $this->data['type'] = 'INSERT';

        // Normalize arguments
        $this->_normalize($args);

        // Iterate through insert array
        foreach ($args as $field => $value) {

            // Add to relevant arrays
            [ $fieldArr[] = $field, $valArr[] = $value ];
        }

        // Set column separator
        $c = $this->data['column'];

        // Set the insert field name and value parameters
        $this->data['params'] = ' ('.$c.implode($c.', '.$c, $fieldArr).$c.') VALUES ('.implode(', ', $this->_wrap($valArr)).')';

        // Allow chaining
        return $this;
    }

    /**
     * Create statement parameters for replace queries.
     *
     * @param mixed
     *
     * @return object
     */
    public function replace(...$args)
    {
        // Update type
        $this->data['type'] = 'REPLACE';

        // Normalize arguments
        $this->_normalize($args);

        // Pass arguments to Insert
        $this->insert($args);

        // Update type
        $this->data['type'] = 'REPLACE';

        // Allow chaining
        return $this;
    }

    /**
     * Create statement parameters for update queries.
     *
     * @param mixed
     *
     * @return object
     */
    public function update(...$args)
    {
        // Soft reset
        $this->reset(false);

        // Update type
        $this->data['type'] = 'UPDATE';

        // Normalize arguments
        $this->_normalize($args);

        // Set column separator
        $c = $this->data['column'];

        // Iterate through insert array
        foreach ($args as $field => $value) {

            // Add to params array
            $paramArr[] = $c.$field.$c.' = '.$this->_wrap($value);
        }

        // Set the update field and value parameters
        $this->data['params'] .= implode(', ', $paramArr);

        // Allow chaining
        return $this;
    }

    /**
     * Create statement parameters for delete queries.
     *
     * @param mixed
     *
     * @return object
     */
    public function delete(...$args)
    {
        // Soft reset
        $this->reset(false);

        // Update type
        $this->data['type'] = 'DELETE';

        // Allow chaining
        return $this;
    }

    /**
     * Creates a truncate query statement.
     *
     * @param string
     *
     * @return object
     */
    public function truncate($table)
    {
        // Custom statement for truncating a table
        $this->custom('TRUNCATE '.$table);

        // Allow chaining
        return $this;
    }

    /**
     * Add a JOIN to query statement.
     *
     * @param splat
     */
    public function join(...$args)
    {
        // Getter or Setter
        if (!count($args)) {

            // Called as a Getter
            return $this->data['join'];
        }

        // Valid join types
        $joinArr = array('JOIN', 'OUTER JOIN', 'INNER JOIN', 'LEFT JOIN', 'LEFT OUTER JOIN',
            'RIGHT JOIN', 'RIGHT OUTER JOIN', 'FULL JOIN', 'FULL OUTER JOIN', 'FULL INNER JOIN', );

        // Detect (Join Type) parameter
        if (isset($args[2])) {

            // Detect valid join types
            if (in_array(strtoupper($args[2]), $joinArr)) {

                // Set the join type
                $this->data['join'] .= ' '.strtoupper($args[2]).' ';

            } else {

                // Invalid join type
                throw new \Exception('Unexpected join() type used in Database call');
            }

        // Default
        } else {

            // Default to a join
            $this->data['join'] .= ' JOIN ';
        }

        // Detect table
        if (isset($args[0])) {

            // Table provided
            $table = $args[0];

        } else {

            // Invalid join type
            throw new \Exception('Table not provided for join() in Database call');
        }

        // Detect ON
        if (isset($args[1])) {

            // Table provided
            $on = $args[1];

        // Invalid
        } else {

            // Invalid join type
            throw new \Exception('ON not provided for join() in Database call');
        }

        // Add the JOIN to the query statement
        $this->data['join'] .= $table.' ON '.$on;

        // Allow chaining
        return $this;
    }

    /**
     * Add an (AND) WHERE to query statement.
     *
     * @param splat
     *
     * @return object
     */
    public function where(...$args)
    {
        // Getter or Setter
        if (!count($args)) {

            // Called as a Getter
            return $this->data['where'];
        }

        // Check for exact string
        if (is_string($args[0]) and count($args) == 1) {

            // Custom where clause statement
            if ($this->data['where'] = ' WHERE '.$args[0]) {

                // Allow chaining
                return $this;
            }
        }

        // Normalize arguments
        $this->_normalize($args, 'where');

        // Pass to $this->_wh()
        $this->_wh($args, 'WHERE', 'AND');

        // Allow chaining
        return $this;
    }

    /**
     * Add an (OR) WHERE to query statements.
     *
     * @param splat
     *
     * @return object
     */
    public function or_where(...$args)
    {
        // Normalize arguments
        $this->_normalize($args, 'or_where');

        // Pass to $this->_wh()
        $this->_wh($args, 'WHERE', 'OR');

        // Allow chaining
        return $this;
    }

    /**
     * Add an (AND) WHERE LIKE to query statement.
     *
     * @param mixed
     *
     * @return object
     */
    public function like(...$args)
    {
        // Normalize arguments
        $this->_normalize($args, 'like');

        // Pass to internal $this->_like()
        $this->_like($args);

        // Allow chaining
        return $this;
    }

    /**
     * Add an (OR) WHERE LIKE to query statement.
     *
     * @param splat
     *
     * @return object
     */
    public function or_like(...$args)
    {
        // Normalize arguments
        $this->_normalize($args, 'like');

        // Pass to internal $this->_like()
        $this->_like($args, 'OR');

        // Allow chaining
        return $this;
    }

    /**
     * Add an (AND) WHERE NOT LIKE to query statement.
     *
     * @param mixed
     *
     * @return object
     */
    public function not_like(...$args)
    {
        // Normalize arguments
        $this->_normalize($args, 'not like');

        // Pass to internal $this->_like()
        $this->_like($args, 'AND', true);

        // Allow chaining
        return $this;
    }

    /**
     * Add an (OR) WHERE NOT LIKE to query statement.
     *
     * @param splat
     *
     * @return object
     */
    public function or_not_like(...$args)
    {
        // Normalize arguments
        $this->_normalize($args, 'not like');

        // Pass to internal $this->_like()
        $this->_like($args, 'OR', true);

        // Allow chaining
        return $this;
    }

    /**
     * Add an (AND) WHERE less than to query statement.
     *
     * @param splat
     *
     * @return object
     */
    public function lt(...$args)
    {
        // Normalize arguments
        $this->_normalize($args, 'lt');

        // Pass to $this->_wh()
        $this->_wh($args, 'WHERE', 'AND', '<');

        // Allow chaining
        return $this;
    }

    /**
     * Add an (OR) WHERE less than to query statement.
     *
     * @param splat
     *
     * @return object
     */
    public function or_lt(...$args)
    {
        // Normalize arguments
        $this->_normalize($args, 'or_lt');

        // Pass to $this->_wh()
        $this->_wh($args, 'WHERE', 'OR', '<');

        // Allow chaining
        return $this;
    }

    /**
     * Add an (AND) WHERE less than or equal to query statement.
     *
     * @param splat
     *
     * @return object
     */
    public function lte(...$args)
    {
        // Normalize arguments
        $this->_normalize($args, 'lte');

        // Pass to $this->_wh()
        $this->_wh($args, 'WHERE', 'AND', '<=');

        // Allow chaining
        return $this;
    }

    /**
     * Add an (OR) WHERE less than or equal to query statement.
     *
     * @param splat
     *
     * @return object
     */
    public function or_lte(...$args)
    {
        // Normalize arguments
        $this->_normalize($args, 'or_lte');

        // Pass to $this->_wh()
        $this->_wh($args, 'WHERE', 'OR', '<=');

        // Allow chaining
        return $this;
    }

    /**
     * Add an (AND) WHERE greater than to query statement.
     *
     * @param splat
     *
     * @return object
     */
    public function gt(...$args)
    {
        // Normalize arguments
        $this->_normalize($args, 'gt');

        // Pass to $this->_wh()
        $this->_wh($args, 'WHERE', 'AND', '>');

        // Allow chaining
        return $this;
    }

    /**
     * Add an (OR) WHERE greater than to query statement.
     *
     * @param splat
     *
     * @return object
     */
    public function or_gt(...$args)
    {
        // Normalize arguments
        $this->_normalize($args, 'or_gt');

        // Pass to $this->_wh()
        $this->_wh($args, 'WHERE', 'OR', '>');

        // Allow chaining
        return $this;
    }

    /**
     * Add an (AND) WHERE greater than or equal to query statement.
     *
     * @param splat
     *
     * @return object
     */
    public function gte(...$args)
    {
        // Normalize arguments
        $this->_normalize($args, 'gte');

        // Pass to $this->_wh()
        $this->_wh($args, 'WHERE', 'AND', '>=');

        // Allow chaining
        return $this;
    }

    /**
     * Add an (OR) WHERE greater than or equal to query statement.
     *
     * @param splat
     *
     * @return object
     */
    public function or_gte(...$args)
    {
        // Normalize arguments
        $this->_normalize($args, 'gte');

        // Pass to $this->_wh()
        $this->_wh($args, 'WHERE', 'OR', '>=');

        // Allow chaining
        return $this;
    }

    /**
     * Shortcut for WHERE field is $this->gt() and $this->lt().
     *
     * @param string
	 * @param int
	 * @param int
     *
     * @return object
     */
    public function between($field, $min, $max)
    {
        // Pass to $this->_wh()
        $this->_wh([$field => $min], 'WHERE', 'AND', '<');
        $this->_wh([$field => $max], 'WHERE', 'AND', '>');

        // Allow chaining
        return $this;
    }

    /**
     * Flips the range of $this->between().
     *
	 * @param string
	 * @param int
	 * @param int
     *
     * @return object
     */
    public function not_between($field, $min, $max)
    {
        // Pass to $this->between()
        $this->between($field, $max, $min);

        // Allow chaining
        return $this;
    }

    /**
     * Add an (AND) WHERE field IS NULL to query statement.
     *
     * @param splat
     *
     * @return object
     */
    public function is_null(...$args)
    {
        // Nullifier
        $nArr = $this->_nullify($args);

        // Pass to $this->_wh()
        $this->_wh($nArr, 'WHERE', 'AND', 'IS NULL');

        // Allow chaining
        return $this;
    }

    /**
     * Add an (OR) WHERE field IS NULL to query statement.
     *
     * @param splat
     *
     * @return object
     */
    public function or_null(...$args)
    {
        // Nullifier
        $nArr = $this->_nullify($args);

        // Pass to $this->_wh()
        $this->_wh($nArr, 'WHERE', 'OR', 'IS NULL');

        // Allow chaining
        return $this;
    }

    /**
     * Add an (AND) WHERE field IS NOT NULL to query statement.
     *
     * @param splat
     *
     * @return object
     */
    public function not_null(...$args)
    {
        // Nullifier
        $nArr = $this->_nullify($args);

        // Pass to $this->_wh()
        $this->_wh($nArr, 'WHERE', 'AND', 'IS NOT NULL');

        // Allow chaining
        return $this;
    }

    /**
     * Add an (OR) WHERE field IS NOT NULL to query statement.
     *
     * @param 	mixed
     *
     * @return object
     */
    public function or_not_null(...$args)
    {
        // Nullifier
        $nArr = $this->_nullify($args);

        // Pass to $this->_wh()
        $this->_wh($nArr, 'WHERE', 'OR', 'IS NOT NULL');

        // Allow chaining
        return $this;
    }

    /**
     * Add an (AND) WHERE not equal to query statement.
     *
     * @param splat
     *
     * @return object
     */
    public function ne(...$args)
    {
        // Normalize arguments
        $this->_normalize($args, 'ne');

        // Pass to $this->_wh()
        $this->_wh($args, 'WHERE', 'AND', '<>');

        // Allow chaining
        return $this;
    }

    /**
     * Add an (OR) WHERE not equal to query statement.
     *
     * @param splat
     *
     * @return object
     */
    public function or_ne(...$args)
    {
        // Normalize arguments
        $this->_normalize($args, 'or_ne');

        // Pass to $this->_wh()
        $this->_wh($args, 'WHERE', 'OR', '<>');

        // Allow chaining
        return $this;
    }

    /**
     * Add a GROUP BY to query statement.
     *
     * @param splat
     *
     * @return object
     */
    public function group(...$args)
    {
        // Getter or Setter
        if (!count($args)) {

            // Called as a Getter
            return $this->data['group'];
        }

        // Set column separator
        $c = $this->data['column'];

        // Iterate through each of the arguments
        foreach ($args as $group) {

            // Add fields to group array
            $gArr[] = $c.$group.$c;
        }

        // Add group by to query statement
        $this->data['group'] = ' GROUP BY '.implode(', ', $gArr);

        // Allow chaining
        return $this;
    }

    /**
     * Add an (AND) HAVING to query statement.
     *
     * @param splat
     *
     * @return object
     */
    public function having(...$args)
    {
        // Getter or Setter
        if (!count($args)) {

            // Called as a Getter
            return $this->data['having'];
        }

        // Check for exact string
        if (is_string($args[0]) and count($args) == 1) {

            // Custom HAVING clause statement
            if ($this->data['where'] = ' HAVING '.$args[0]) {

                // Allow chaining
                return $this;
            }
        }

        // Normalize arguments
        $this->_normalize($args, 'having');

        // Pass to $this->_wh()
        $this->_wh($args, 'HAVING', 'AND');

        // Allow chaining
        return $this;
    }

    /**
     * Add an (OR) HAVING to query statements.
     *
     * @param splat
     *
     * @return object
     */
    public function or_having(...$args)
    {
        // Normalize arguments
        $this->_normalize($args, 'or_having');

        // Pass to $this->_wh()
        $this->_wh($args, 'HAVING', 'OR');

        // Allow chaining
        return $this;
    }

    /**
     * Add an ORDER BY to query statements.
     *
     * @param splat
     *
     * @return object
     */
    public function order(...$args)
    {
        // Getter or Setter
        if (!count($args)) {

            // Called as a Getter
            return $this->data['order'];
        }

        // Normalize arguments
        $this->_normalize($args, 'order');

        // Set column separator
        $c = $this->data['column'];

        // Iterate through insert array
        foreach ($args as $field => $order) {

            // Add to ordering arrays
            $ordArr[] = $c.$field.$c.' '.(in_array(strtoupper($order), ['ASC', 'DESC']) ? strtoupper($order) : 'ASC');
        }

        // Add ordering conditions to query statement
        $this->data['order'] = ' ORDER BY '.implode(', ', $ordArr);

        // Allow chaining
        return $this;
    }

    /**
     * Add a limit to query statements.
     *
     * @param int
     *
     * @return object
     */
    public function limit($limit = false)
    {
        // Getter or Setter
        if (!$limit) {

            // Limit checking
            if ($this->data['limit']) {

                // Detect offsetting
                if ($this->data['offset']) {

                    // Preprend the offset
                    return ' LIMIT '.$this->data['offset'].', '.$this->data['limit'];
                } else {
                    // Return the limit without an offset
                    return ' LIMIT '.$this->data['limit'];
                }
            } else {
                return false;
            }
        }

        // Set query statement limit
        $this->data['limit'] = $limit;

        // Allow chaining
        return $this;
    }

    /**
     * Adds offset to query statement.
     *
     * @param	int
     *
     * @return object
     */
    public function offset($offset)
    {
        // Set query statement offset
        $this->data['offset'] = $offset;

        // Allow chaining
        return $this;
    }

    /**
     * Query builder method constructs a statement to execute.
     *
     * @return	string
     */
    public function statement()
    {
        // Detect custom query statement
        if ($this->custom()) {

            // Custom statement
            return $this->custom();
        }

        // Query builder
        $query = $this->data['type'];

        // Add parameters
        $query .= $this->params();

        // Add join clause
        $query .= $this->join();

        // Add where clause
        $query .= $this->where();

        // Add group by clause
        $query .= $this->group();

        // Add having clause
        $query .= $this->having();

        // Add order by clause
        $query .= $this->order();

        // Add limit clause
        $query .= $this->limit();

        // Return the statement
        return $query;
    }

    /**
     * Query performance.
     *
     * @return string
     */
    public function queries()
    {
        // Return
        return $this->data['queries'] ?? 0;
    }

    /**
     * Generates the parameters for a query statement.
     *
     * @return string
     */
    private function params()
    {
        // Reference shortcuts
        $q = &$this->data['type'];
        $p = &$this->data['params'];
        $t = &$this->data['table'];

        // Param string builder
        $params = '';

        // Select formatting
        if ($q == 'SELECT') {

            // Return parameters
            return $p.' FROM '.$t;
        }

        // Delete formatting
        elseif ($q == 'DELETE') {

            // Return parameters
            return ' FROM '.$t;
        }

        // Insert or Replace formatting
        elseif (in_array($q, ['INSERT', 'REPLACE'])) {

            // Return parameters
            $params .= ' INTO '.$t.$p;
        }

        // Insert formatting
        elseif ($q == 'UPDATE') {

            // Return parameters
            $params .= ' '.$t.' SET '.$p;
        }

        // Return
        return $params;
    }

    /**
     * Internal function for calculating WHERE / HAVING clauses.
     *
     * @param mixed
     * @param string
     * @param string
	 * @param string
	 *
	 * @return void
     */
    private function _wh($args, $clause, $mode = 'AND', $operator = '=')
    {
        // Transform clause
        $clause = strtolower($clause);

        // Status of clause
        if (!$this->data[$clause]) {

            // Create a new WHERE statement
            $this->data[$clause] = ' '.strtoupper($clause).' ';

        } else {

            // Add to the WHERE statement
            $this->data[$clause] .= ' '.$mode.' ';
        }

        // Set column separator
        $c = $this->data['column'];

        // Iterate through insert array
        foreach ($args as $field => $value) {

            // Detect wrapping
            if (is_string($value)) {

                // Add (with wrapping) to conditions array
                $whArr[] = $c.$field.$c.' '.$operator.' '.$this->_wrap($value);

            } else {

                // Add (no wrapping for ints, doubles) to conditions array
                $whArr[] = $c.$field.$c.' '.$operator.' '.$value;
            }
        }

        // Add conditions to WHERE statement
        $this->data[$clause] .= implode(' '.$mode.' ', $whArr);
    }

    /**
     * Internal function for calculating WHERE (NOT) LIKE clauses.
     *
     * @param mixed
     * @param string
     */
    private function _like($args, $mode = 'AND', $not = false)
    {
        // Status of clause
        if (!$this->data['where']) {

            // Create a new WHERE statement
            $this->data['where'] = ' WHERE ';

        } else {

            // Add to the WHERE statement
            $this->data['where'] .= ' '.$mode.' ';
        }

        // LIKE or NOT LIKE
        $like = ($not) ? ' NOT LIKE ' : ' LIKE ';

        // Iterate through insert array
        foreach ($args as $field => $value) {

            // Add to conditions array
            if (substr($value, 0, 1) == '%' or substr($value, -1) == '%') {

                // Wildcard parameter already specified
                $whArr[] = $field.$like.$this->_wrap($value);

            } else {

                // Add wildcards to the beginning and end of value
                $whArr[] = $field.$like.$this->_wrap('%'.$value.'%');
            }
        }

        // Add conditions to WHERE statement
        $this->data['where'] .= implode(' '.$mode.' ', $whArr);
    }

    /**
     * Internal function for setting each argument to sets in an array.
     *
     * @param array
     *
     * @return object
     */
    private function _nullify($args)
    {
        // Check for array
        if (is_array($args[0])) {

            // Normalize input
            $args = $args[0];
        }

        // Iterate through arguments
        foreach ($args as $field) {

            // Add to null array
            $nArr[$field] = null;
        }

        // Return array
        return $nArr;
    }

    /**
     * Normalizes arguments into a standardized array for $this->select(),
     * $this->update and $this->insert(), etc. Accepting a variable number of
     * parameters as keypairs or a single array specifying keypairs.
     *
     * @param mixed
     */
    public function _normalize(&$args, $source = false)
    {
        // Check for arrays
        if (is_array($args[0])) {

            // Expand array
            $args = $args[0];
        }

        // Modulus params
        elseif (count($args) % 2 == 0) {

            // Transform to array
            $args = $this->_keypair($args);

        } else {

            // Unexpected input for this method
            throw new \Exception('Unexpected number of parameters in Database call to '
                .strtolower(($source === false) ? $this->data['type'] : $source).'()');
        }
    }

    /**
     * Takes an even number of parameters and converts to a keypair array.
     *
     * @param array
     *
     * @return array
     */
    private function _keypair($args)
    {
        // Iterate through arguments
        while ($key = array_shift($args)) {

            // Convert to a keypair
            $keypair[$key] = array_shift($args);
        }

        // Return array
        return $keypair;
    }

    /**
     * Internal method for padding parameters.
     *
     * @param string
     *
     * @return string
     */
    private function _pad($str)
    {
        // Return with padding
        return ' '.$str.' ';
    }

    /**
     * Internal method for wrapping values with a specified string.
     *
     * @param string
     * @param string
     *
     * @return string
     */
    private function _wrap($mixed)
    {
        // Ensure connection exists
        if (!isset($this->data['dbh'])) {

            // Connect to database
            $this->connect();
        }

        // Check for array
        if (is_array($mixed)) {

            // Iterate through items
            foreach ($mixed as $k => $val) {

                // Wrapping
                if (is_null($val)) {

                    // Do not wrap null values
                    $mixed[$k] = 'NULL';

                } else {

                    // Wrap the item with chars
                    $mixed[$k] = $this->data['dbh']->quote($val);
                }
            }
        } else {

            // Wrapping
            if (is_null($mixed)) {

                // Do not wrap null values
                $mixed = 'NULL';

            } else {

                // Wrap the item with chars
                $mixed = $this->data['dbh']->quote($mixed);
            }
        }

        // Return the wrapped item
        return $mixed;
    }

    /**
     * Prevents showing sensitive credentials in var_dump().
     *
     * @param mixed
     */
    public function __debugInfo()
    {
        // For debugging
        return ['data' => [
            '_io'        => 'user, password, dbname, dsn not shown',
            'result'     => $this->data['result'],
            'affected'   => $this->data['affected'],
            'fetched'    => $this->data['fetched'],
            'custom'     => $this->data['custom'],
            'params'     => $this->data['params'],
            'table'      => $this->data['table'],
            'type'       => $this->data['type'],
            'join'       => $this->data['join'],
            'where'      => $this->data['where'],
            'group'      => $this->data['group'],
            'having'     => $this->data['having'],
            'order'      => $this->data['order'],
            'limit'      => $this->data['limit'],
            'offset'     => $this->data['offset'],
            'connection' => $this->data['connection'],
        ]];
    }
}
