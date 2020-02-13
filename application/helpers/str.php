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
 * A helper class for assisting with text and string manipulation
 *
 * @method helpers\str::studly( $str );
 * @method helpers\str::camel( $str );
 * @method helpers\str::snake( $str );
 * @method helpers\str::underscore( $str );
 * @method helpers\str::slug( $str );
 * @method helpers\str::hiphen( $str );
 * @method helpers\str::human( $str );
 * @method helpers\str::sentence( $str );
 * @method helpers\str::alternator( $str [, $str2] [, $str3] );
 * @method helpers\str::reduce_slashes( $str );
 * @method helpers\str::reduce_multiples( $str, $multi );
 * @method helpers\str::code( $str );
 * @method helpers\str::begins( $str, $begins );
 * @method helpers\str::ends( $str, $ends );
 * @method helpers\str::contains( $str, $contains );
 * @method helpers\str::finish( $str, $ending );
 * @method helpers\str::singular( $str );
 * @method helpers\str::plural( $str );
 * @method helpers\str::random( $str );
 * @method helpers\str::word_random( $str );
 * @method helpers\str::limit( $str, $limit);
 * @method helpers\str::ellipsis( $str, $limit);
 * @method helpers\str::word_limit( $str, $limit );
 * @method helpers\str::word_ellipsis( $str, $limit );
 * @method helpers\str::word_boundary( $str, $limit );
 * @method helpers\str::word_boundary_ellipsis( $str, $limit );
 * @method helpers\str::ascii( $str );
 * @method helpers\str::alphanumeric( $str );
 * @method helpers\str::unbracket( $str );
 * @method helpers\str::between( $str, $start, $end );
 * @method helpers\str::generate( [$length] [, $case] );
 */
namespace helpers;

class str
{
    // Framework traits
    use __aliases, __singleton;

    // Alternative method names
    public static $__aliases = [
        'generate'    => ['str_random'],
        'ends'        => ['ends_with', 'ending_with', 'with_ending', 'with_end'],
        'begins'      => ['begins_with', 'beginning_with', 'with_beginning', 'with_begins'],
        'word_random' => ['random_word', 'rand_word', 'word_rand'],
        'word_limit'  => ['limit_word'],
    ];

    /**
     * Initialize str helper class.
     *
     * @return object
     */
    public function __construct()
    {
        // Private methods to exclude from shortcuts
        $excluded = array('casespace');
    }

    /**
     * Internal function for casing and spacing strings.
     *
     * @param string
     *
     * @return array
     */
    private static function casespace($str, $tolower = true)
    {
        // Explode on various types of separators
        $sArr = preg_split('/(-|_|\.|\s)/', $str);

        // Mode selection
        if ($tolower) {

            // Iterate through words
            foreach ($sArr as $k => $word) {

                // Perform a string to lower
                $sArr[$k] = strtolower($word);
            }
        }

        // Return
        return $sArr;
    }

    /**
     * Convert a string to StudlyCase.
     *
     * @param string
     *
     * @return string
     */
    public static function studly($str)
    {
        // Get a usuable array
        $sArr = self::casespace($str);

        // Iterate through words
        foreach ($sArr as $k => $words) {

            // Uppercase first letter
            $sArr[$k] = ucfirst($words);
        }

        // Return StudlyCase string
        return trim(implode('', $sArr));
    }

    /**
     * Convert a string to camelCase.
     *
     * @param string
     *
     * @return string
     */
    public static function camel($str)
    {
        // Return the snake case
        return lcfirst(self::studly($str));
    }

    /**
     * Convert a string to snake_case (removes capitalization).
     *
     * @param string
     *
     * @return string
     */
    public static function snake($str)
    {
        // Return the snake case
        return trim(implode('_', self::casespace($str)));
    }

    /**
     * Convert a string to underscore_spacing (keeps capitalization).
     *
     * @param string
     *
     * @return string
     */
    public static function underscore($str)
    {
        // Return the snake case
        return trim(implode('_', self::casespace($str, false)));
    }

    /**
     * Convert a string to title-slug (removes capitalization).
     *
     * @param string
     *
     * @return string
     */
    public static function slug($str)
    {
        // Return the snake case
        return trim(implode('-', self::casespace($str)));
    }

    /**
     * Convert a string to hiphen-spacing (keeps capitalization).
     *
     * @param string
     *
     * @return string
     */
    public static function hiphen($str)
    {
        // Return the snake case
        return trim(implode('-', self::casespace($str, false)));
    }

    /**
     * Convert a string to Human readable.
     *
     * @param string
     *
     * @return string
     */
    public static function human($str)
    {
        // Explode on capital letters
        $cArr = preg_split('/(?=\p{Lu})/u', $str);

        // Explode on various types of separators
        $sArr = preg_split('/(-|_|\.)/', implode(' ', $cArr));

        // Return human readable
        return trim(implode(' ', $sArr));
    }

    /**
     * Convert a string to a proper sentence.
     *
     * @param string
     *
     * @return string
     */
    public static function sentence($str)
    {
        // Return the snake case
        return self::finish(ucfirst(strtolower(self::human($str))), '.');
    }

    /**
     * Alternates between an arbitrary number of strings (use with a loop).
     *
     * @param splat
     *
     * @return string
     */
    public static function alternator(...$arr)
    {
        // Static variable
        static $i = 0;

        // Return an alternating value
        return $arr[($i++ % count($arr))];
    }

    /**
     * Removed double slashes from a string.
     *
     * @param splat
     *
     * @return string
     */
    public static function reduce_slashes($str)
    {
        // Wrapper function for some regex
        return preg_replace('/\/{2,}/', '/', $str);
    }

    /**
     * Removes multiple instances of strings immediately after one another.
     *
     * @param string
	 * @param string
     *
     * @return string
     */
    public static function reduce_multiples($str, $multi)
    {
        // Wrapper function for some regex
        return preg_replace('/'.preg_quote($multi, '/').'{2,}/', $multi, $str);
    }

    /**
     * Wrapper for calling htmlentities() on the string.
     *
     * @param string
     *
     * @return string
     */
    public static function code($str)
    {
        // Wrapper function for htmlentities
        return htmlentities($str);
    }

    /**
     * Returns a boolean if a string begins with a matching string.
     *
     * @param string
     *
     * @return bool
     */
    public static function begins($str, $begins)
    {
        // Wrapper for preg_match string search
        return preg_match('/^'.preg_quote($begins, '/').'/', $str);
    }

    /**
     * Returns a boolean if a string ends with a matching string.
     *
     * @param string
	 * @param string
     *
     * @return bool
     */
    public static function ends($str, $ends)
    {
        // Wrapper for preg_match string search
        return preg_match('/'.preg_quote($ends, '/').'$/', $str);
    }

    /**
     * Returns a boolean if a string contrains another string.
     *
     * @param string
	 * @param string
     *
     * @return bool
     */
    public static function contains($str, $contains)
    {
        // Wrapper for preg_match string search
        return preg_match('/'.preg_quote($contains, '/').'/', $str);
    }

    /**
     * Ensures that a string ends with a specific character.
     *
     * @param string
	 * @param string
     *
     * @return string
     */
    public static function finish($str, $ending)
    {
        // Check if a string ends with a character
        if (substr($str, -1) == $ending) {

            // Already ends with $ending
            return $str;
        }

        // Add $ending to string
        return $str.$ending;
    }

    /**
     * Wrapper function for inflector pluralization.
     *
     * @param string
     *
     * @return string
     */
    public static function singular($str)
    {
        // Wrapper for inflector method
        $inflector = new \helpers\inflector;

        // Get the singular English noun
        return $inflector->single($str);
    }

    /**
     * Wrapper function for inflector pluralization.
     *
     * @param string
     *
     * @return string
     */
    public static function plural($str)
    {
        // Wrapper for inflector method
        $inflector = new \helpers\inflector;

        // Get the plural English noun
        return $inflector->plural($str);
    }

    /**
     * Returns a random sub string of a string.
     *
     * @param string
     *
     * @return string
     */
    public static function random($str)
    {
        // Count the letters
        $length = strlen($str);

        // Return a random sub string
        return substr($str, rand(0, $length), rand(0, $length));
    }

    /**
     * Returns a random word from a string.
     *
     * @param string
     *
     * @return string
     */
    public static function word_random($str)
    {
        // Separate words
        $words = explode(' ', $str);

        // Returns a random word from the string
        return trim(implode(' ', array_slice($words, rand(0, count($words)), 1)));
    }

    /**
     * Truncates a string to a given length.
     *
     * @param string
     * @param int
     *
     * @return string
     */
    public static function limit($str, $limit)
    {
        // Wrapper for substr()
        return substr($str, 0, $limit);
    }

    /**
     * Truncates a string to a given length and adds an ellipsis.
     *
     * @param string
     * @param int
     *
     * @return string
     */
    public static function ellipsis($str, $limit)
    {
        // Wrapper for self::limit()
        return self::limit($str, $limit).'...';
    }

    /**
     * Truncates a string to a given word length.
     *
     * @param string
     * @param int
     *
     * @return string
     */
    public static function word_limit($str, $limit)
    {
        // Separate words
        $words = explode(' ', $str);

        // Return the string limited by words
        return trim(implode(' ', array_slice($words, 0, $limit)));
    }

    /**
     * Truncates a string to a given word length and adds an ellipsis.
     *
     * @param string
     * @param int
     *
     * @return string
     */
    public static function word_ellipsis($str, $limit)
    {
        // Wrapper for self::word_limit()
        return self::word_limit($str, $limit).'...';
    }

    /**
     * Truncates string on word boundaries after character length.
     *
     * @param string
     * @param int
     *
     * @return string
     */
    public static function word_boundary($str, $limit)
    {
        // Split words
        $parts = preg_split('/([\s\n\r]+)/', $str, null, PREG_SPLIT_DELIM_CAPTURE);

        // Iterate through words
        for ($len = $last = 0; $last < count($parts) AND $len <= $limit;) {

            // Add/track length of string
            $len += ( $len+strlen($parts[$last+1]) >= $limit ) ? $limit : strlen($parts[$last++]);
        }

        // Return the string using the word boundaries
        return trim(implode(array_slice($parts, 0, $last)));
    }

    /**
     * Truncates string on word boundaries and adds an ellipsis.
     *
     * @param string
     * @param int
     *
     * @return string
     */
    public static function word_boundary_ellipsis($str, $limit)
    {
        // Wrapper for self::word_boundary()
        return self::word_boundary($str, $limit).'...';
    }

    /**
     * Returns ascii characters only.
     *
     * @param string
     *
     * @return string
     */
    public static function ascii($str)
    {
        // Strips non-ascii characters
        return $str = preg_replace("/[^\x20-\x7E]/", '', $str);
    }

    /**
     * Returns alphanumeric characters only.
     *
     * @param string
     *
     * @return string
     */
    public static function alphanumeric($str)
    {
        // Strips non-alphanumeric characters
        return $str = preg_replace("/[^A-Za-z0-9 ]/", '', $str);
    }

    /**
     * Returns string without any brackets, braces or parenthesis.
     *
     * @param string
     *
     * @return string
     */
    public static function unbracket($str)
    {
        // Strips brackets, braces and parenthsis characters
        return trim(preg_replace('/\(|\)|\[|\]|\{|\}/', '', $str));
    }

    /**
     * Get string between two strings.
     *
     * @param string
     * @param string
     * @param string
     *
     * @return string
     */
    public static function between($str, $start, $end)
    {
        // Check if $start exists
        if ($from = strpos($str, $start)) {

            // Trim from $start
            $from += strlen($start);
        } else {
            // Trim from beginning of $str
            $start = 0;
        }

        // Check if $end exists
        if ($to = strpos($str, $end, $from)) {

            // Trim to $end
            $to -= $from;
        } else {
            // Trim to end of $str
            return substr($str, $from);
        }

        // Return a string between two strings
        return substr($str, $from, $to);
    }

    /**
     * Generates string of arbitrary length.
     *
     * @param int
     * @param bool
     *
     * @return string
     */
    public static function generate($length = 5, $case_sensitive = true)
    {
        // Case sensitivity
        $range = ($case_sensitive)

            // Create a range of 62 characters
            ? array_merge(range('a', 'z'), range('A', 'Z'), range(0, 9))

            // Create a range of 36 characters
            : array_merge(range('a', 'z'), range(0, 9));

        // Blank
        $str = '';

        // Generate
        for ($i = 0; $i < $length; $i++) {

            // Add random character
            $str .= $range[mt_rand(0,count($range)-1)];
        }

        // String
        return $str;
    }
}
