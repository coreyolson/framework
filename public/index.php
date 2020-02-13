<?php
/**
 * Framework
 * ------------------------------------------------
 * A minimalist PHP framework.
 *
 * @copyright   Copyright (c) 2010 - 2020 Corey Olson
 * @license     https://opensource.org/licenses/MIT
 * @link        https://github.com/coreyolson/framework
 */

// Framework
require '../application/framework.php';

// Performance
libraries\benchmark::mark('Startup');

// Sitewide Theme
libraries\page::theme('_layout')->optimize();

// Standard Routing
framework::index('home/index', 'before/after', '_cron');

/**
 * BASIC USAGE
 * -----------------------------------------------
 * Framework is accessed by f::class()->method();
 *
 * [0] Remove f::framework() to disable internal routing
 *
 * [1] Using a view:           f::view('filename');
 * [2] Using a controller:     f::controller('home')->get_index();
 * [3] Using a model:          f::model('template')->demo();
 * [4] Using a helper:         f::helpers('arr')->keys( [1,2,3] );
 * [5] Using a library:        f::library('benchmark')->since();
 *
 * [6] Dynamic views:          view::return('filename', ['var' => 'value']);
 * [7] Custom routes:          route::get('/.*', function () { //code });
 * [8] Use a layout:           f::library('page')->theme('filename')->optimize();
 * [9] Quick echo:             view::echo('filename', ['var' => 'value']);
 *
 * [*] Cached views:           view::echo('filename', ['var' => 'value'], true);
 * [*] Default Routing:        Change via framework::index('controller/action', 'pre/post')
 * [*] Cron Jobs:              Subfolder in Controllers via framework::index('c/a', 'p/p', '_cron')
 */
