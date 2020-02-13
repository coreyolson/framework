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
 * A helper class for working with arrays
 *
 * @method helpers\arr::add( $array, $keypair, [notfound] );
 * @method helpers\arr::add( $array, $keypair [, 'notfound'] )
 * @method helpers\arr::merge( $array1, $array2 [, $array3...] );
 * @method helpers\arr::combine( $keyArray, $valueArray );
 * @method helpers\arr::divide( $array );
 * @method helpers\arr::keys( $array );
 * @method helpers\arr::values( $array );
 * @method helpers\arr::first( $array );
 * @method helpers\arr::last( $array );
 * @method helpers\arr::rand( $array );
 * @method helpers\arr::eq( $array, $itemNumber );
 * @method helpers\arr::odd( $array );
 * @method helpers\arr::even( $array );
 * @method helpers\arr::nth( $array [, $modulus] );
 * @method helpers\arr::mod( $array [, $modulus] [, $operator] );
 * @method helpers\arr::element( $array, $key [, 'notfound'] );
 * @method helpers\arr::fetch( $array, $key [, 'notfound'] );
 * @method helpers\arr::pull( $array, $key [, 'notfound'] );
 * @method helpers\arr::keep( $array, $keyArray );
 * @method helpers\arr::sort( $array, $sortMethod [, $makeCopy] );
 * @method helpers\arr::flatten( $array [, boolean] );
 * @method helpers\arr::dot( $array [, boolean] );
 * @method helpers\arr::undot( $array [, boolean] [, boolean] );
 * @method helpers\arr::select( $array, $dotPath );
 * @method helpers\arr::exists( $array, $dotPath );
 * @method helpers\arr::write( $array, $dotPath, $value );
 * @method helpers\arr::insert( $array, $dotPath, $value );
 * @method helpers\arr::update( $array, $dotPath, $value );
 * @method helpers\arr::remove( $array, $dotPath );
 */
namespace helpers;

class arr
{
    // Framework traits
    use __aliases, __singleton;

    // Alternative method names
    public static $__aliases = [
        'merge'   => ['collapse'],
        'combine' => ['unsplit'],
        'divide'  => ['split'],
        'values'  => ['reindex', 'index', 'number'],
        'fetch'   => ['elements', 'specific', 'isolate', 'only'],
        'rand'    => ['random', 'any', 'anything'],
        'nth'     => ['nitem', 'nitems', 'n'],
        'eq'      => ['xitem', 'xitems', 'x'],
        'mod'     => ['modulus'],
        'find'    => ['where', 'search', 'filter'],
        'sort'    => ['organize', 'sorting', 'sorter', 'order'],
        'pull'    => ['remove', 'rm'],
        'keep'    => ['except'],
        'dot'     => ['to_dot', 'dotted'],
        'undot'   => ['to_multi', 'undotted'],
        'write'   => ['overwrite', 'force', 'forceinsert'],
        'select'  => ['get', 'getdot', 'getbydot', 'dotget'],
        'insert'  => ['set', 'setdot', 'setbydot', 'dotset'],
        'update'  => ['change', 'updatedot', 'updatebydot', 'dotupdate'],
        'remove'  => ['forget', 'removedot', 'removebydot', 'dotremove', 'delete'],
        'exists'  => ['has', 'exist', 'dotexist', 'dotexists'],
    ];

    /**
     * Add an element to an array if it does not exist.
     *
     * @param array
     * @param array
     * @param string
     *
     * @return mixed
     */
    public static function add($arr, $keypair, $notfound = false)
    {
        // Check if $keypair is an array
        if (!is_array($keypair)) {

            // Transform $keypair into an array
            $keypair = array(count($arr) => $keypair);
        }

        // Check the key does not exists
        if (!isset($arr[key($keypair)])) {

            // Add the $keypair to the array
            $arr[key($keypair)] = end($keypair);

        } else {

            // Key already exists
            return $notfound;
        }

        return true;
    }

    /**
     * A wrapper function for native array_merge() function.
     *
     * @param array
     *
     * @return array
     */
    public static function merge(...$params)
    {
        // Iterate through parameters
        foreach ($params as $key => $param) {

            // Check validity
            if (!is_array($param)) {

                // Remove parameter
                unset($params[$key]);
            }
        }

        // Provie an arbitrary number of arrays to merge
        return call_user_func_array('array_merge', $params);
    }

    /**
     * A wrapper for array_combine() that slices for matching lengths.
     *
     * @param array
     * @param array
     *
     * @return mixed
     */
    public static function combine($keyArr, $valArr)
    {
        // Validity check
        if (!is_array($keyArr) or !is_array($valArr)) {

            // Unexpected input
            return false;
        }

        // Slice values based on key array
        $valArr = array_slice($valArr, 0, count($keyArr));

        // Slice keys based on value array
        $keyArr = array_slice($keyArr, 0, count($valArr));

        // Call native function and return
        return array_combine($keyArr, $valArr);
    }

    /**
     * Return two arrays; one with keys and another with values.
     *
     * @param array
     *
     * @return mixed
     */
    public static function divide($arr)
    {
        // Validity check
        if (!is_array($arr)) {

            // Unexpected input
            return false;
        }

        // Return
        return array(

            // Internal PHP function to get array keys
            'keys' => array_keys($arr),

            // Internal PHP function to get array values
            'values' => array_values($arr),
        );
    }

    /**
     * A wrapper function for array_keys() with validity check.
     *
     * @param array
     *
     * @return mixed
     */
    public static function keys($arr)
    {
        // Internal PHP function to get array keys
        return (is_array($arr)) ? array_keys($arr) : false;
    }

    /**
     * A wrapper function for array_values() with validity check.
     *
     * @param array
     *
     * @return mixed
     */
    public static function values($arr)
    {
        // Internal PHP function to get array values
        return (is_array($arr)) ? array_values($arr) : false;
    }

    /**
     * A wrapper function for reset().
     *
     * @param array
     *
     * @return mixed
     */
    public static function first($arr)
    {
        // Internal PHP function to return the first array item
        return (is_array($arr)) ? reset($arr) : false;
    }

    /**
     * A wrapper function for end().
     *
     * @param array
     *
     * @return mixed
     */
    public static function last($arr)
    {
        // Internal PHP function to return the last array item
        return (is_array($arr)) ? end($arr) : false;
    }

    /**
     * Get a random element from an array.
     *
     * @param array
     *
     * @return mixed
     */
    public static function rand($arr)
    {
        // Internal PHP function to return a random array item
        return (is_array($arr)) ? $arr[array_rand($arr)] : false;
    }

    /**
     * Get the nth item in an array if it exists.
     *
     * @param array
     * @param int
     *
     * @return mixed
     */
    public static function eq($arr, $eq = 0)
    {
        // Validity check
        if (!is_int($eq)) {

            // Unexpected input
            return false;
        }

        // Check if nth item request is outside of range
        elseif (!in_array($eq, range(1, count($arr)))) {

            // Outside array range
            return false;
        }

        // Internal PHP function to do an array slice
        return (is_array($arr)) ? array_slice($arr, ($eq - 1), 1) : false;
    }

    /**
     * Return all odd items from an array - Wrapper: self::mod($arr, 1).
     *
     * @param array
     *
     * @return mixed
     */
    public static function odd($arr)
    {
        // Pass the array to sell::mod() to get odd array items
        return (is_array($arr)) ? self::mod($arr, 2, '==') : false;
    }

    /**
     * Return all even items from an array - Wrapper: self::mod($arr, 2).
     *
     * @param array
     *
     * @return mixed
     */
    public static function even($arr)
    {
        // Pass the array to sell::mod() to get even array items
        return (is_array($arr)) ? self::mod($arr, 2, '!=') : false;
    }

    /**
     * Return an array matching every nth item.
     *
     * @param array
     * @param int
     *
     * @return mixed
     */
    public static function nth($arr, $nth = 3)
    {
        // Pass the array to sell::mod() to get odd array items
        return (is_array($arr)) ? self::mod($arr, $nth, '<>') : false;
    }

    /**
     * Modulus iteration over an array to unset values.
     *
     * @param array
     * @param int
     * @param string
     *
     * @return mixed
     */
    public static function mod($arr, $mod = 2, $operator = '==')
    {
        // Vality check
        if (!is_int($mod)) {

            // Unexpected input
            return false;
        }

        // Validity check
        elseif (!is_array($arr)) {

            // Unexpected input
            return false;

        } elseif (!in_array($operator, ['==', '<', 'lt', '<=', 'le', '>', 'gt', '>=', 'ge', '==', 'eq', '!=', '<>', 'ne'])) {

            // Not a valid operator
            return false;
        }

        // Counter for iteration
        $iterator = 0;

        // Iterate over array
        foreach ($arr as $k => $v) {

            // Performe modulus math and comparison
            if (version_compare($iterator++ % $mod, 0, $operator)) {

                // Unset aby matches
                unset($arr[$k]);
            }
        }

        // Return the ammended array
        return $arr;
    }

    /**
     * Return an array value if it exists.
     *
     * @param array
     * @param mixed
     * @param mixed
     *
     * @return mixed
     */
    public static function element($arr, $mixed, $notfound = false)
    {
        // Reroute call to self::only(), call array_values() get first element
        return array_values(self::fetch($arr, $mixed, $notfound))[0];
    }

    /**
     * Return an array matching a list of keys.
     *
     * @param array
     * @param mixed
     * @param mixed
     *
     * @return mixed
     */
    public static function fetch($arr, $mixed, $notfound = false)
    {
        // Validity check
        if (!is_array($arr)) {

            // Unexpected input
            return false;

        } elseif (!is_array($mixed)) {

            // Valid string or int (key)
            if (is_string($mixed) or is_int($mixed)) {

                // Convert to an array
                $mixed = array($mixed);

            } else {

                // Unexpected input
                return false;
            }
        }

        // Create a tmp working array
        $tmp = array();

        // Iterate through list of items
        foreach ($mixed as $key) {

            // Check if key exists
            if (isset($arr[$key])) {

                // Add to working array
                $tmp[$key] = $arr[$key];

            } else {
                // Key does not exist
                $tmp[$key] = $notfound;
            }
        }

        // Return the matched elements
        return $tmp;
    }

    /**
     * Get a value and remove it from the array.
     *
     * @param array
     * @param mixed
     * @param mixed
     *
     * @return mixed
     */
    public static function pull($arr, $key, $notfound = false)
    {
        // Validity check
        if (!is_array($arr)) {

            // Unexpected input
            return false;
        }

        // Check if value exists
        if (!isset($arr[$key])) {

            // Does not exixt
            return $notfound;
        }

        // Store the value
        elseif ($value = $arr[$key]) {

            // Remove value from array
            unset($arr[$key]);
        }

        return $value;
    }

    /**
     * Modify an array to only keep certain keys and unset the rest.
     *
     * @param array
     * @param mixed
     *
     * @return mixed
     */
    public static function keep($arr, $mixed)
    {
        // Validity check
        if (!is_array($arr)) {

            // Unexpected input
            return false;

        } elseif (!is_array($mixed)) {

            // Valid string or int (key)
            if (is_string($mixed) or is_int($mixed)) {

                // Convert to an array
                $mixed = array($mixed);

            } else {

                // Unexpected input
                return false;
            }
        }

        $mixed = array_flip($mixed);

        // Iterate through list of items
        foreach ($arr as $key => $value) {

            // Check if key exists
            if (!array_key_exists($key, $mixed)) {

                // Add to working array
                unset($arr[$key]);
            }
        }

        // Return the matched elements
        return $arr;
    }

    /**
     * Sorts and returns the original or copy of the array.
     *
     * @param array
     * @param string
     *
     * @return mixed
     */
    public static function sort($arr, $type = 'sort', $original = true)
    {
        // Validity check
        if (!is_array($arr)) {

            // Unexpected input
            return false;
        }

        // Check if this is a valid sorting method
        elseif (!in_array($type, ['asort', 'arsort', 'krsort', 'ksort', 'rsort', 'sort', 'natsort'])) {

            // Unexpected input
            return false;
        }

        // Sort original
        if ($original) {

            // Sort and return original
            return (!$type($arr)) ?: $arr;
        }

        // Sort copy
        else {

            // Copy the array
            if ($cArr = $arr) {

                // Sort and return the copy
                return (!$type($cArr)) ?: $cArr;
            }
        }

        // End logic
        return false;
    }

    /**
     * Flatten a multi-dimensional array into a single-dimensional array.
     *
     * @param array
     *
     * @return array
     */
    public static function flatten($arr, $original = true)
    {
        // Create an array
        $flatArr = array();

        // Recursively iterate through array and get values
        array_walk_recursive($arr, function ($value, $key) use (&$flatArr) {

            // Check if key exists
            if (!isset($flatArr[$key])) {

                // Add to flattened array
                $flatArr[$key] = $value;

            } else {

                // Avoid errors on mixed dimensions
                if ( is_array($flatArr[$key]) ) {

                    // Create a unique key
                    $flatArr[$key.'_'.count($flatArr[$key])] = $value;

                } else {

                    // Create a unique key
                    $flatArr[$key.'_'.$flatArr[$key]] = $value;
                }
            }
        });

        // Original
        if ($original) {

            // Copy working array to original
            return $arr = $flatArr;
        }

        // Return flattened array
        return $flatArr;
    }

    /**
     * Resurive iterator for multi-dimensional arrays.
     */
    private static function iterator($arr)
    {
        // Create a new Resurive array iterator objectt and return
        return new \RecursiveIteratorIterator(new \RecursiveArrayIterator($arr));
    }

    /**
     * Flatten a multi-dimensional array into dot notation.
     *
     * @param array
     * @param bool
     *
     * @return mixed
     */
    public static function dot($arr, $original = true)
    {
        // Validity check
        if (!is_array($arr)) {

            // Unexpected input
            return false;
        }

        // Check for single-dimensional array
        elseif (count($arr) == count($arr, COUNT_RECURSIVE)) {

            // Array is already one-dimensional
            return false;
        }

        // Iterate through each endpoint value
        foreach ($recursively = self::iterator($arr) as $value) {

            // Iterate through each dimension (depth) of the array
            foreach ($range = range(0, $recursively->getDepth()) as $depth) {

                // Create a temporary array of keys of arrays dimensions
                $keys[] = $recursively->getSubIterator($depth)->key();
            }

            // Concatenate the keys to achieve dot notation and reset $keys
            array($dotArr[ implode('.', $keys) ] = $value, $keys = array());
        }

        // Original
        if ($original) {

            // Copy working array to original
            return $arr = $dotArr;
        }

        // Return the single-dimensional dot notated array
        return $dotArr;
    }

    /**
     * Converts dot-notation single-dimensional array to multi-dimensional.
     *
     * @param array
     * @param bool
     * @param bool
     *
     * @return mixed
     */
    public static function undot($arr, $original = true, $force = false)
    {
        // Validity check
        if (!is_array($arr)) {

            // Unexpected input
            return false;
        }

        // Check for multi-dimensional array
        if (count($arr) != count($arr, !$force)) {

            // Array is multi-dimensional with no force update
            return false;
        }

        // Working array
        $tmp = array();

        // Iterate through the single-dimensional array
        foreach ($arr as $key => $value) {

            // Write values into a multi-dimensional array
            self::write($tmp, $key, $value);
        }

        // Original
        if ($original) {

            // Update the original array
            return $arr = $tmp;
        }

        // Return the working array
        return $tmp;
    }

    /**
     * Writes a value into a multi-dimensional array using dot-notation.
     *
     * @param array
     * @param string
     * @param mixed
     *
     * @return mixed
     */
    public static function write($arr, $key, $value)
    {
        // Validity check
        if (!is_array($arr)) {

            // Unexpected input
            return false;
        }

        // Iterate through the dots
        foreach (explode('.', $key) as $dot) {

            // Reference to build key
            $arr = &$arr[$dot];
        }

        // Insert the value into the array
        return $arr = $value;
    }

    /**
     * Selects a value from a multi-dimensional array using dot-notation.
     *
     * @param array
     * @param string
     * @param mixed
     *
     * @return mixed
     */
    public static function select($arr, $dotPath, $notfound = false)
    {
        // Validity check
        if (!is_array($arr)) {

            // Unexpected input
            return false;
        }

        // Check for proper input
        elseif (!is_string($dotPath)) {

            // Unexpected input
            return false;
        }

        // Allow forgiing dot-notation syntax
        $dotPath = ltrim(rtrim($dotPath, '.'), '.');

        // Recursively search for an array key by dot-notation
        foreach (explode('.', $dotPath) as $dot) {

            // Reference to build key
            $arr = &$arr[$dot];
        }

        // Notfound if reference is null value
        return (is_null($arr)) ? $notfound : $arr;
    }

    /**
     * Checks if a value exists in a multi-dimensional array using dot-notation.
     *
     * @param array
     * @param string
     *
     * @return bool
     */
    public static function exists($arr, $dotPath)
    {
        // Pass to self::select() and check value
        return (self::select($arr, $dotPath)) ? true : false;
    }

    /**
     * Inserts a value into a multi-dimensional array using dot-notation.
     *
     * @param array
     * @param string
     * @param mixed
     *
     * @return mixed
     */
    public static function insert($arr, $dotPath, $value)
    {
        // Pass to self::exists() to determine existence
        if (self::exists($arr, $dotPath)) {

            // Already exists
            return false;
        }

        return self::write($arr, ltrim(rtrim($dotPath, '.'), '.'), $value);
    }

    /**
     * Updates a keypair in a multi-dimensional array using dot-notation.
     *
     * @param array
     * @param string
     * @param mixed
     *
     * @return mixed
     */
    public static function update($arr, $dotPath, $value)
    {
        // Pass to self::exists() to determine existence
        if (self::exists($arr, $dotPath)) {

            // Does not exist
            return self::write($arr, ltrim(rtrim($dotPath, '.'), '.'), $value);
        }

        return false;
    }

    /**
     * Removes a keypair from a multi-dimensional array using dot-notation.
     *
     * @param string
     */
    public static function remove($arr, $dotPath)
    {
        // Clean dotPath (forgiving) poor dot-notation
        $dotPath = explode('.', ltrim(rtrim($dotPath, '.'), '.'));

        // Key being destroyed
        $last = array_pop($dotPath);

        // Recursively search for an array key by dot-notation
        foreach ($dotPath as $dot) {

            // Reference to build key
            $arr = &$arr[$dot];
        }

        // Removes the key regardless of existence
        unset($arr[$last]);

        return !self::exists($arr, $dotPath);
    }
}
