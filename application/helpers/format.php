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
 * A class for producing normalized formats.
 *
 * @method helpers\format::phone();
 */
namespace helpers;

class format
{
    // Framework traits
    use __aliases, __singleton;

    // Alternative method names
    public static $__aliases = [
        'phone' => ['phoneNumber'],
    ];

    /**
     * Format phone number.
     *
     * @var string
     *
     * @return mixed
     */
    public static function phone($number)
    {
        // Normalize
        $number = preg_replace('/[^0-9]/','',$number);

        // Detect non-conforming
        if ( is_null($number) OR is_bool($number) OR !isset($number[6]) ) {

            // Nonsense
            return false;
        }

        // Return formatted number; Begin with Country Code (if provided)
        return ((substr($number, 0, strlen($number)-10) == '') ? '+1 ' : '')

            // Area Code (if provided)
            .(( strlen($number) != 7 ) ? '('.substr($number, -10, 3).') ':null)

            // First-3 Digits
            .substr($number, -7, 3)

            // Last-4 Digits
            .'-'.substr($number, -4, 4);
    }
}
