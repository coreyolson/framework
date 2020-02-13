# Framework

## A Minimalist PHP Framework

Designed to be lightweight, fast and easy to use. Provides the bare essentials
for a modern website; e.g., simple request routing to controllers which can then
go on to invoke models or views as required. A minimalist PHP 7.0+ framework.

# Getting Started

All requests routed through _index.php_ to a controller. Application and framework
logic is in the **application** folder. Sub-folders are labeled: controllers, helpers,
libraries, models and views within the same main **application** folder.

### Quick Installation

No setup required. **mod_rewrite** optional, but ideally **enabled**. All requests go
through _public/index.php_. Modify _public/index.php_ as needed. Without mod_rewrite
URLS are: */index.php/controller/method/param1/param2/*

##### Installation using PHP Composer:

    composer create-project coreyolson/framework folder

##### Suggested Rewrite Rules:

    RewriteEngine On
    RewriteBase /
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond \$1 !^(.*\..*) [NC]
    RewriteRule ^(.*)$ ./index.php/\$1 [L]

### Structuring a Website or Web Application

Routing logic goes in **controllers**, application logic in **models**, and **views**
contain templates. Alternative routing (i.e., including _Framework.php_ via another
file is possible, and) still allows access to libraries, helpers and views.

# Routing

The framework routes URLs via {controller}/{action}/{param1}/{param2}. Printing internal the
method `f::info()` offers more insights. The default controller is _home.php_ with **index**
as the default action/method, and can be changed. The framework looks for `before_{method}()`
and `after_{method}()` respectively. These can be changed by passing your desired preferences
to `framework::index('home/index', 'before/after');` within _public/index.php_.

### GET example.com/project/list
Routes to the **project.php** controller, or *404 error if controller does not exist.*
* Runs the `get()` method if it exists
* Runs the `before()` method if it exists
* Runs the `get_before()` method if it exists
* Runs the `before_list()` method if it exists
* Runs the `before_get_list()` method if it exists
* Runs the `get_list()` method, or *404 error if method does not exist.*
* Runs the `after_get_list()` method if it exists
* Runs the `after_list()` method if it exists
* Runs the `get_after()` method if it exists
* Runs the `after()` method if it exists

Get internal Framework and routing information by printing `f::info()`.

# Basic Usage

Using `f::method()` has a slight performance penalty, and is provided for convenience during
development. For the best performance, access classes and methods using fully qualified names;
e.g., `\helpers\arr::keys();` for the Array Helper.

##### Auto-load any helper or library

    f::class()->method();                  // f::arr(), f::benchmark(), f::page(), f::file()

##### Using a Controller, Model, Helper or Library

    f::controller('home')->get_index();    // controllers\home::get_index();
    f::model('template')->demo();          // models\template::demo();
    f::helpers('arr')->keys();             // helpers\arr::keys();
    f::library('benchmark')->mark();       // libraries\benchmark::mark();

##### Using a view
    echo f::view('filename');              // view::echo('filename');

##### Using a dynamic view
    f::view('filename', ['var' => 'val']);	// view::return('filename', ['var' => 'val']);

# Advanced Usage
Add custom routes in _public/index.php_ as needed; e.g., `route::verb('/{pattern}', function () { //code });` Any
HTTP verb may be used, even non-standard verbs. Two framework-specific verbs `route::any` and `route::port` have
unique functionality for matching *any* HTTP verb, and immediately running framework routing upon matching
*port* requests.

**GET request to example.com/users/**

    route::get('/users/', function () {
        //code
    });                                             // Continues processing

**POST request to example.com/users/edit/**

    route::post('/users/edit/', function () {
        //code
    }, true);                                       // Stops processing on TRUE

**PORT request to example.com/internals/**

    route::port('/internals/', function () {});     // Switches to framework:index() routing

### Cron Configuration
The framework can function as a cron dispatcher; however, a cronjob still needs to be configured on
the web server. The following runs the framework cron dispatcher every minute; which looks in the
_application/controllers/_cron_ folder for scheduled controllers to run. Use the framework's
scheduling options to configure the desired time and frequency.

    (crontab -l 2>/dev/null; echo "# * * * * * wget https://localhost/?_cron -O /dev/null") | crontab -


*Note: Framework must be able to write to the _storage/_ folder for Crons to work; or, you can manually
create a writeable file at _storage/._cron.json_ ; adjust if using a different _cron configuration/folder.*

##### An example Cron task / controller within Framework

    <?php

    namespace controllers\_cron;

    class example
    {
        public static $options = [
            'dayOfWeek' => 'friday',
            'frequency' => 'everyFiveMinutes',
            'between'   => ['1:00 am', '2:30 am'],
        ];

        public static function index()
        {
            // Code here
        }
    }

# License

Released under the MIT License. All contributions released under the MIT License.
