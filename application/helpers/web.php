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
 * A helper class for dealing with websites, urls and curl requests
 *
 * @method helpers\web::code( $http_status_code [, boolean] );
 * @method helpers\web::status( $http_status_code );
 * @method helpers\web::refresh();
 * @method helpers\web::redirect( $redirect_location [, boolean] );
 * @method helpers\web::temporary( $redirect_location );
 * @method helpers\web::permanent( $redirect_location );
 * @method helpers\web::request( boolean );
 * @method helpers\web::action();
 * @method helpers\web::protocol();
 * @method helpers\web::secure();
 * @method helpers\web::insecure();
 * @method helpers\web::upgrade();
 * @method helpers\web::nonwww();
 * @method helpers\web::site();
 * @method helpers\web::domain();
 * @method helpers\web::requesturi();
 * @method helpers\web::querystring();
 * @method helpers\web::index();
 * @method helpers\web::current();
 * @method helpers\web::seo();
 * @method helpers\web::slug();
 * @method helpers\web::stop_words();
 * @method helpers\web::is_stop_word();
 * @method helpers\web::error_404();
 * @method helpers\web::is_self();
 * @method helpers\web::only_self();
 * @method helpers\web::is_www();
 * @method helpers\web::only_secure();
 */
namespace helpers;

class web
{
    // Framework traits
    use __aliases, __singleton;

    // Alternative method names
    public static $__aliases = [
        'secure'   => ['https', 'ssl'],
        'insecure' => ['http'],
        'nonwww'   => ['nowww'],
    ];

    /**
     * Array of HTTP Status Codes.
     *
     * @var array
     */
    private static $codes = array(
        100 => 'Continue',
        101 => 'Switching Protocols',

        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',

        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',

        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        422 => 'Unprocessable Entity',

        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
    );

    /**
     * List of common SEO stop words.
     *
     * @var array
     */
    private static $seo = array('a','able','about','above','abroad','according','accordingly','across','actually','adj','after','afterwards','again','against','ago','ahead','ain\'t','all','allow','allows','almost','alone','along','alongside','already','also','although','always','am','amid','amidst','among','amongst','an','and','another','any','anybody','anyhow','anyone','anything','anyway','anyways','anywhere','apart','appear','appreciate','appropriate','are','aren\'t','around','as','a\'s','aside','ask','asking','associated','at','available','away','awfully','b','back','backward','backwards','be','became','because','become','becomes','becoming','been','before','beforehand','begin','behind','being','believe','below','beside','besides','best','better','between','beyond','both','brief','but','by','c','came','can','cannot','cant','can\'t','caption','cause','causes','certain','certainly','changes','clearly','c\'mon','co','co.','com','come','comes','concerning','consequently','consider','considering','contain','containing','contains','corresponding','could','couldn\'t','course','c\'s','currently','d','dare','daren\'t','definitely','described','despite','did','didn\'t','different','directly','do','does','doesn\'t','doing','done','don\'t','down','downwards','during','e','each','edu','eg','eight','eighty','either','else','elsewhere','end','ending','enough','entirely','especially','et','etc','even','ever','evermore','every','everybody','everyone','everything','everywhere','ex','exactly','example','except','f','fairly','far','farther','few','fewer','fifth','first','five','followed','following','follows','for','forever','former','formerly','forth','forward','found','four','from','further','furthermore','g','get','gets','getting','given','gives','go','goes','going','gone','got','gotten','greetings','h','had','hadn\'t','half','happens','hardly','has','hasn\'t','have','haven\'t','having','he','he\'d','he\'ll','hello','help','hence','her','here','hereafter','hereby','herein','here\'s','hereupon','hers','herself','he\'s','hi','him','himself','his','hither','hopefully','how','howbeit','however','hundred','i','i\'d','ie','if','ignored','i\'ll','i\'m','immediate','in','inasmuch','inc','inc.','indeed','indicate','indicated','indicates','inner','inside','insofar','instead','into','inward','is','isn\'t','it','it\'d','it\'ll','its','it\'s','itself','i\'ve','j','just','k','keep','keeps','kept','know','known','knows','l','last','lately','later','latter','latterly','least','less','lest','let','let\'s','like','liked','likely','likewise','little','look','looking','looks','low','lower','ltd','m','made','mainly','make','makes','many','may','maybe','mayn\'t','me','mean','meantime','meanwhile','merely','might','mightn\'t','mine','minus','miss','more','moreover','most','mostly','mr','mrs','much','must','mustn\'t','my','myself','n','name','namely','nd','near','nearly','necessary','need','needn\'t','needs','neither','never','neverf','neverless','nevertheless','new','next','nine','ninety','no','nobody','non','none','nonetheless','noone','no-one','nor','normally','not','nothing','notwithstanding','novel','now','nowhere','o','obviously','of','off','often','oh','ok','okay','old','on','once','one','ones','one\'s','only','onto','opposite','or','other','others','otherwise','ought','oughtn\'t','our','ours','ourselves','out','outside','over','overall','own','p','particular','particularly','past','per','perhaps','placed','please','plus','possible','presumably','probably','provided','provides','q','que','quite','qv','r','rather','rd','re','really','reasonably','recent','recently','regarding','regardless','regards','relatively','respectively','right','round','s','said','same','saw','say','saying','says','second','secondly','see','seeing','seem','seemed','seeming','seems','seen','self','selves','sensible','sent','serious','seriously','seven','several','shall','shan\'t','she','she\'d','she\'ll','she\'s','should','shouldn\'t','since','six','so','some','somebody','someday','somehow','someone','something','sometime','sometimes','somewhat','somewhere','soon','sorry','specified','specify','specifying','still','sub','such','sup','sure','t','take','taken','taking','tell','tends','th','than','thank','thanks','thanx','that','that\'ll','thats','that\'s','that\'ve','the','their','theirs','them','themselves','then','thence','there','thereafter','thereby','there\'d','therefore','therein','there\'ll','there\'re','theres','there\'s','thereupon','there\'ve','these','they','they\'d','they\'ll','they\'re','they\'ve','thing','things','think','third','thirty','this','thorough','thoroughly','those','though','three','through','throughout','thru','thus','till','to','together','too','took','toward','towards','tried','tries','truly','try','trying','t\'s','twice','two','u','un','under','underneath','undoing','unfortunately','unless','unlike','unlikely','until','unto','up','upon','upwards','us','use','used','useful','uses','using','usually','v','value','various','versus','very','via','viz','vs','w','want','wants','was','wasn\'t','way','we','we\'d','welcome','well','we\'ll','went','were','we\'re','weren\'t','we\'ve','what','whatever','what\'ll','what\'s','what\'ve','when','whence','whenever','where','whereafter','whereas','whereby','wherein','where\'s','whereupon','wherever','whether','which','whichever','while','whilst','whither','who','who\'d','whoever','whole','who\'ll','whom','whomever','who\'s','whose','why','will','willing','wish','with','within','without','wonder','won\'t','would','wouldn\'t','x','y','yes','yet','you','you\'d','you\'ll','your','you\'re','yours','yourself','yourselves','you\'ve','z','zero');

    /**
     * Lookup an HTTP Statuc Code.
     *
     * @param int
     * @param bool
     *
     * @return mixed
     */
    public static function code($code, $mode = false)
    {
        // Check if this is a valid code
        if (array_key_exists($code, self::$codes)) {

            // Decide how to return the code
            if ($mode) {

                // Return the full
                return array(
                    'Status' => $code,
                    'Status Code' => $code.' '.self::$codes[$code],
                    'Description' => self::$codes[$code],
                );
            } else {
                // Return the status code description
                return self::$codes[$code];
            }
        }

        // Not found
        return false;
    }

    /**
     * Get the current status code or set a status code.
     *
     * @param string
     */
    public static function status($status = false)
    {
        // Check for Getter
        if (!$status) {

            // Wrapper PHP call
            return http_response_code();
        }

        // Check for a valid Setter
        if (!self::code($status)) {

            // Not a valid code
            return false;
        }

        // Wrapper PHP call
        return http_response_code($status);
    }

    /**
     * Force a refresh of a page.
     */
    public static function refresh()
    {
        // Refresh header
        header('Refresh:0');
    }

    /**
     * Force a redirect (301 Permanent) or (302 Temporary).
     *
     * @param string
     * @param bool
     */
    public static function redirect($location, $permanent = false)
    {
        // Redirect type
        if ($permanent) {

            // 301 Permanent redirect
            header('HTTP/1.1 301 Moved Permanently');
        } else {
            // 302 Permanent redirect
            header('HTTP/1.1 302 Found');
        }

        // Set the redirect location
        header('Location: '.$location);
    }

    /**
     * Force a 302 Temporary redirect.
     *
     * @param string
     */
    public static function temporary($location)
    {
        // Wrapper call for redirect
        self::redirect($location, false);
    }

    /**
     * Force a 301 Permanent redirect.
     *
     * @param string
     */
    public static function permanent($location)
    {
        // Wrapper call for redirect
        self::redirect($location, true);
    }

    public static function request($formal = false)
    {
        // Return type
        if ($formal) {

            // Formal Request Information (similar to browser dev tools)
            return array(
                'Request URL' => self::current(),
                'Request Method' => $_SERVER['REQUEST_METHOD'],
                'Status Code' => http_response_code(),
            );
        }

        // Developer friendly
        return array_merge(framework::route(), array(
            'url' => self::current(),
            'method' => $_SERVER['REQUEST_METHOD'],
            'status' => http_response_code(),
        ));
    }

    /**
     * Get the action request URL minus query string parameters.
     *
     * @return string
     */
    public static function action()
    {
        // Return the action request url
        return self::site().framework::route()['request'];
    }

    /**
     * Get the current protocol (either HTTP or HTTPS).
     *
     * @return string
     */
    public static function protocol()
    {
        // Determine the protocol (HTTP vs HTTPS)
        return ($_SERVER['SERVER_PORT'] == 80) ? 'http' : 'https';
    }

    /**
     * Detect if HTTPS is being used.
     *
     * @return bool
     */
    public static function secure()
    {
        // Convert protocol status to boolean
        return self::protocol() == 'https';
    }

    /**
     * Detect if HTTP is being used.
     *
     * @return bool
     */
    public static function insecure()
    {
        // Convert protocol status to boolean
        return self::protocol() == 'http';
    }

    /**
     * Automatically upgrade from HTTP to HTTPS.
     */
    public static function upgrade()
    {
        // Detect current security
        if (self::insecure()) {

            // Perform a redirect (upgrade to HTTPS) keeping parameters
            self::redirect(str_replace('http', 'https', self::current()));
        }
    }

    /**
     * Force non-www and redirect request.
     */
    public static function nonwww($permanent = true)
    {
        // Detect domain hostname
        $host = explode('.', $_SERVER['HTTP_HOST']);

        // Count number of TLD pieces; Check www on first piece
        if (sizeof($host === 3) AND array_shift($host) == 'www') {

            // Recreate URL from existing protocol, host and server request
            $redirect = self::protocol() . '://' . implode('.', $host) . $_SERVER['REQUEST_URI'];

            // Show 301 (Permanent) or 302 (Temporary) Code
            helpers\web::redirect($redirect, $permanent);
        }
    }

    /**
     * Get the site URL with the protocol being used.
     *
     * @return string
     */
    public static function site()
    {
        // Get the current site url
        return self::protocol().'://'.$_SERVER['SERVER_NAME'];
    }

    /**
     * Get the domain name of the current site.
     *
     * @return string
     */
    public static function domain()
    {
        // Get the current site domain
        return $_SERVER['SERVER_NAME'];
    }

    /**
     * Get the Proper domain name.
     *
     * @return string
     */
    public static function proper()
    {
        // Get the current sub, domain and TLD
        $proper = explode('.', self::domain());

        // Remove the TLD
        array_pop($proper);

        // Return a proper name for the site
        return ucwords(implode('.', $proper));
    }

    /**
     * Get the REQUEST_URI.
     *
     * @return string
     */
    public static function requesturi()
    {
        // Return the REQUEST_URI
        return framework::route()['request'];
    }

    /**
     * Get the query string.
     *
     * @return string
     */
    public static function querystring()
    {
        // Return the Query String
        return framework::route()['query'];
    }

    /**
     * Get the Framework index.php URL.
     *
     * @return string
     */
    public static function index()
    {
        // Return the Framework index.php url
        return self::site().$_SERVER['SCRIPT_NAME'];
    }

    /**
     * Get the current request URL (full domain and path).
     *
     * @return string
     */
    public static function current()
    {
        // Return the current request url
        return self::site().$_SERVER['REQUEST_URI'];
    }

    /**
     * Returns a string or array with SEO stop words removed.
     *
     * @param mixed
     *
     * @return mixed
     */
    public static function seo($mixed)
    {
        // Detect strings
        if (is_string($mixed)) {

            // Separate the words and iterate through
            foreach ($tArr = explode(' ', $mixed) as $key => $word) {

                // Check against stop words
                if (in_array($word, self::$seo)) {

                    // Remove stop word
                    unset($tArr[$key]);
                }
            }

            // Cleaned string
            return implode(' ', $tArr);
        }

        // Detect arrays
        if (is_array($mixed)) {

            // Iterate through items
            foreach ($mixed as $key => $word) {

                // Check against stop words
                if (in_array($word, self::$seo)) {

                    // Remove stop word
                    unset($mixed[$key]);
                }
            }

            // Cleaned array
            return $mixed;
        }

        // Unexpected input
        return false;
    }

    /**
     * Returns a URL slug with or without SEO stop words removed.
     *
     * @param string
     * @param boolean
     *
     * @return string
     */
    public static function slug($str, $seo = false)
    {
        // Normalize the string
        $str = framework::helpers('str')->human(strtolower($str));

        // Optional SEO stop word removal
        if ($seo) {

            // Get a purified string
            $str = self::seo($str);
        }

        // Provide a title slug
        return str_replace(' ', '-', $str);
    }

    /**
     * Provides an array of SEO stop words.
     *
     * @return array
     */
    public static function stop_words()
    {
        // Returns the internal variable
        return self::$seo;
    }

    /**
     * Detects if string is a SEO stop word.
     *
     * @return bool
     */
    public static function is_stop_word($str)
    {
        // Boolean response
        return in_array($str, self::$seo);
    }

    /**
     * Public function the developer can call for sending a 404 error. This is
     * the default error which uses 404.html in the root directory. If the file
     * is not provided PHP will still send a 404 HEADER to the browser.
     */
    public static function error_404($path = false)
    {
        // Send a 404 HTTP HEADER error code to the browser
        header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');

        // Include the 404 file and exit
        (!$path)?: include $path;

        // Done
        exit();
    }

    /**
     * Detects requests from self
     *
     * @return bool
     */
    public static function is_self()
    {
        // Boolean response
        return ( $_SERVER['REMOTE_ADDR'] == $_SERVER['SERVER_ADDR'] );
    }

    /**
     * Stop execution if not requested by self
     *
     * @param string
     *
     * @return bool
     */
    public static function only_self($show404 = true)
    {
        // Prevent external requests
        if ( ! self::is_self() ) {

            // Show 404 or return
            return ($show404) ? self::error_404() : self::is_self();
        }
    }

    /**
     * Returns true if using "www" subdomain
     *
     * @return bool
     */
    public static function is_www()
    {
        // Detect domain hostname
        $host = explode('.', $_SERVER['HTTP_HOST']);

        // Count number of TLD pieces; Check www on first piece
        return (sizeof($host === 3) AND array_shift($host) == 'www');
    }

    /**
     * Stop execution if not HTTPS
     *
     * @param string
     *
     * @return bool
     */
    public static function only_secure($show404 = false)
    {
        // Prevent external requests
        if ( self::insecure() ) {

            // Show 404 or return
            return ($show404) ? self::error_404() : exit();
        }
    }
}
