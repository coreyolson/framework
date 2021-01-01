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
 * A class for XHR responses.
 *
 * @method helpers\xhr::status();
 */
namespace helpers;

class xhr
{
    // Framework traits
    use __aliases, __singleton;

    // Alternative method names
    public static $__aliases = [
        'status' => ['response'],
    ];

    /**
     * Respond with XHR status.
     */
    public static function status($status)
    {
        // Detect boolean
        if (is_bool($status)) {

            // Convert boolean into XHR message
            $status = ($status) ? 'success' : 'fail';
        }

        // Output XHR status
        echo json_encode(['status' => $status]);

        // Stop processing
        libraries\page::end();
    }
}
