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
 * A PHP Crawler for scraping and analyzing web pages
 *
 * @method libraries\crawler::domain();
 * @method libraries\crawler::explore();
 * @method libraries\crawler::page();
 * @method libraries\crawler::wait();
 */
namespace libraries;

class crawler
{
    // Framework traits
    use __aliases, __singleton;

    // Alternative method names
    public static $__aliases = [
        'page'   => ['scrape', 'link'],
        'crawl'  => ['recursive', 'discover', 'find'],
        'domain' => ['domains', 'hosts'],
    ];

    /**
     * Internal class variables.
     *
     * @var array
     */
    private static $data;

    /**
     * Initialize the crawler class.
     *
     * @return void
     */
    public function __construct()
    {
        // Create a new web agent
        self::$data['crawler'] = new libraries\agent;

        // Crawler should be faster
        self::$data['timeout'] = 2500;

        // Set timeout within the user agent
        self::$data['crawler']->timeout(self::$data['timeout']);

        // Crawler disregards SSL verification
        self::$data['crawler']->secure(false);
    }

    /**
     * Begin exploring links on a specific domain only.
     *
     * @param string
     * @param int
     * @param int
     *
     * @return mixed
     */
    public static function domain($domain, $depth = 25, $timeout = 59, $ignore = [])
    {
        // Wrapper call to explore a specific domain only
        return self::explore($domain, $depth, $timeout, true, $ignore);
    }

    /**
     * Begin exploring links to a specified depth and length of time.
     *
     * @param string
     * @param int
     * @param int
     *
     * @return mixed
     */
    public static function explore($allowed_hosts, $depth = 25, $timeout = 59, $bound = false, $ignore = [])
    {
        // Data tracking arrays
        $crawls = $indexed = $content = $hosts = $failure = array();

        // Set a marker for timeouts
        list($tmr, $pps) = [microtime(true), 0];

        // Check for string inputs
        if ( is_string( $allowed_hosts ) ) {

            // Normalize
            $allowed_hosts = array($allowed_hosts);
        }

        // Iterate domains
        foreach ($allowed_hosts as $host) {

            // Add to queue
            $queue[] = $host;
        }

        // Process until queue is exhausted / depth reached
        while ( count($queue) AND count($crawls) <= $depth AND $timeout > (microtime(true) - $tmr)+$pps ) {

            // Crawl the newly discovered link
            self::$data['crawler']->get(current($queue));

            // Check for an unsuccessful crawl (usually a timeout)
            if (!self::$data['crawler']->status()) {

                // Failed Paths
                $failure[] = $queue[key($queue)];

                // Remove from crawl queue
                unset($queue[key($queue)]);

                // Skip
                continue;
            }

            // Digest crawler info and being building link data
            $crawls[current($queue)] = $digest = self::digest();

            // Add to content array
            $content[] = $digest['BodyCompressed'];

            // Discovered zero links?
            if ( ! $digest['Links'] ) {

                // Empty array on zero links
                $digest['Links'] = array();
            }

            // Iterate over discovered links
            foreach (filter_var_array($digest['Links'], FILTER_VALIDATE_URL) as $Link) {

                // Domain must be present; Or this must be an unbound search
                if ( (str_replace($allowed_hosts, '', $Link['Crawl']) != $Link['Crawl']) OR !$bound ) {

                    // Filter query strings, hashtags, telephone links and double slash errors
                    $uri = parse_url(explode('tel:', preg_replace('/([^:])(\/{2,})/', '$1/', $Link['Crawl']))[0]);

                    // Parseable URL?
                    if ( isset($uri['host']) ) {

                        // Must be a newly discovered link
                        if ( !isset($hosts[$uri['host']]) ) {

                            // Add to discovered domains
                            $hosts[$uri['host']] = 0;
                        }

                        // Reconstructs a crawlable URl for indexing
                        $path = $uri['scheme'].'://'.$uri['host'].($uri['path'] ?? '/');

                        // Must be a newly discovered link
                        if ( !isset($indexed[$path]) ) {

                            // Initialize
                            $indexed[$path] = 0;

                            // Skip paths containing..
                            foreach ($ignore as $string) {

                                // Detect ignore strings
                                if ( stripos($path, $string) !== false ) {

                                    // Skip Link
                                    continue 2;
                                }
                            }

                            // Add to crawl queue
                            $queue[] = $path;
                        }

                        // Ranking index data
                        $indexed[$path]++;

                        // Ranking domain data
                        $hosts[$uri['host']]++;
                    }
                }
            }

            // Pages per second
            $pps = (microtime(1) - $tmr)/count($crawls);

            // Remove from crawl queue
            unset($queue[key($queue)]);
        }

        // Process all content
        $words = self::content( implode('', $content) );

        // Sort by Popularity
        arsort($indexed);

        // Finished
        return array(
            'Speed'  => $pps,
            'Hosts'  => $hosts,
            'Index'  => $indexed,
            'Pages'  => $crawls,
            'Errors' => $failure,
            'Queue'  => $queue,
            'Words'  => $words,
        );
    }

    /**
     * Wrapper for Agent GET Request.
     *
     * @param string
     *
     * @return mixed
     */
    public static function page($path)
    {
        // Crawl the path
        self::$data['crawler']->get($path);

        // Check for an unsuccessful crawl (usually a timeout)
        if (!self::$data['crawler']->status()) {

            // Failed to crawl
            return false;
        }

        // Digest the crawler information
        return self::digest();
    }

    /**
     * For slower servers perform a long (indefinite by default) crawl.
     *
     * @param string
     * @param int
     *
     * @return mixed
     */
    public static function wait($path, $timeout = 0)
    {
        // Check for indefinite crawls
        if ($timeout == 0) {

            // Wait indefinitely for this page to load
            $timeout = PHP_INT_MAX;
        }

        // Set timeout within the user agent
        self::$data['crawler']->timeout($timeout);

        // Perform crawl
        $crawlDigest = self::crawl($path);

        // Reset timeout to the default value
        self::$data['crawler']->timeout(self::$data['timeout']);

        // Crawl digest
        return $crawlDigest;
    }

    /**
     * Return the crawler digest.
     *
     * @return mixed
     */
    private static function digest()
    {
        // Special DOM processing
        self::domprocess(self::$data['crawler']->body());

        // Digest crawler data
        return array(
            'Status'         => self::$data['crawler']->status(),
            'Path'           => self::$data['crawler']->path(),
            'Protocol'       => self::$data['crawler']->protocol(),
            'Root'           => self::$data['crawler']->root(),
            'Domain'         => self::$data['crawler']->domain(),
            'TLD'            => self::$data['crawler']->tld(),
            'Headers'        => self::$data['crawler']->headers(),
            'Title'          => self::title(),
            'Meta'           => self::meta(),
            'Body'           => self::$data['crawler']->body(),
            'BodyCompressed' => preg_replace('~>\s+<~', '> <', self::$data['crawler']->body()),
            'Content'        => self::content(self::$data['crawler']->body()),
            'Redirects'      => self::$data['crawler']->redirects(),
            'Details'        => self::$data['crawler']->details(),
            'Links'          => self::links(),
            'Frames'         => self::frames(),
        );
    }

    /**
     * Prepares a DOM object.
     *
     * @return void
     */
    private static function domprocess($html)
    {
        // DOM: http://php.net/manual/en/class.domdocument.php
        self::$data['dom'] = new \domDocument();

        // Suppress errors and load HTML from crawled page
        @self::$data['dom']->loadHTML($html);

        // Removing whitespace
        self::$data['dom']->preserveWhiteSpace = false;
    }

    /**
     * Returns the documents title if it exists on the crawled page.
     *
     * @return mixed
     */
    private static function title()
    {
        // Grab the Title tag from the DOM
        $title = self::$data['dom']->getElementsByTagName('title')[0];

        // Return the Title tag if it exists
        return (!isset($title))?false:$title->nodeValue;
    }

    /**
     * Returns an array of the meta tags from the crawled page.
     *
     * @return array
     */
    private static function meta()
    {
        // Search for <meta> tags within the document; using regex so we dont have to make another request, e.g., get_meta_tags()
        preg_match_all('/<[\s]*meta[\s]*name="?' . '([^>"]*)"?[\s]*' . 'content="?([^>"]*)"?[\s]*[\/]?[\s]*>/si', self::$data['crawler']->body(), $match);

        // Digest the meta tags
        return array(

            // Full HTML tags
            'Raw'  => $match[0],

            // Meta tags as an key / value pair (with normalized keys)
            'Tags' => array_combine(array_map('strtolower', $match[1]), $match[2])
        );
    }

    /**
     * Parses the content of the page.
     *
     * @return array
     */
    private static function content($content)
    {
        // Remove any JavaScript from the Content
        $content = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', ' ', $content);

        // Remove any in-line CSS from the Content
        $content = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', ' ', $content);

        // Ensure there are spaces between tags
        $content = str_replace('><', '> <', $content);

        // Remove the HTML tags from the Content while cleaning up whitespace
        $content = preg_replace('/\s+/', ' ', strip_tags($content));

        // Remove special HTML characters
        $content = preg_replace('/&#?[a-z0-9]{2,8};/i', '', $content);

        // Get the list of SEO stop words
        $stopArr = helpers\web::stop_words();

        // Iterate through content and generate keyword usage
        foreach (explode(' ', $content) as $word) {

            /*
             * Normalize words (remove commas, colons, semi-colons, etc.) This also
             * casts integers as strings and ignores casing for words for SEO purposes.
             */
            $word = (string) strtolower(preg_replace("/[^A-Za-z0-9]/", '', $word));

            // Remove short words
            if (!isset($word[2])) {

                // Words 2 characters or less are excluded
                continue;
            }

            // Check if a word exists
            if (isset($keywords['Occurrence'][$word])) {

                // Increment the keyword count
                $keywords['Occurrence'][$word]++;
            } else {

                // Add the word
                $keywords['Occurrence'][$word] = 1;
            }

            // Check against SEO stop words
            if (!in_array($word, $stopArr)) {

                // Add to the SEO relevant keywords
                $keywords['SEO'][$word] = $keywords['Occurrence'][$word];
            }
        }

        // Check for content
        if (isset($keywords)) {

            // Sort Keywords by Occurrence
            arsort($keywords['Occurrence']);
            arsort($keywords['SEO']);

            // Top 10 keywords in order
            $keywords['Top'] = array_keys(array_slice($keywords['SEO'], 0, 10, true));
        } else {

            // No content
            $keywords = array(
                'Occurrence' => array(),
                'SEO'        => array(),
                'Top'        => array(),
            );
        }

        // Normalize content
        $content = trim($content);

        // Check for empty content
        if ($content == '') {

            // Make developer friendly variable
            $content = false;
        }

        // Digest content
        return array(
            'Content'  => $content,
            'Words'    => str_word_count($content),
            'Unique'   => count($keywords['Occurrence']),
            'Keywords' => $keywords,
        );
    }

    /**
     * Return an array of links from the crawled page.
     *
     * @return array
     */
    private static function links()
    {
        // Links discovered
        $links = array();

        /*
         * Tracking array to remove duplicate links. By design this crawler also
         * removes references to root domain and anchors linking to tops of pages.
         */
        $tracking = array('/', '//', '#');

        // Search for <a> tags within the DOM
        $tags = self::$data['dom']->getElementsByTagName('a');

        // Iterate over tags
        foreach ($tags as $tag) {

            // Get the HREF and TITLE attributes from the tags into an array
            @$links[] = [trim($tag->getAttribute('href')), trim($tag->getAttribute('title')), trim($tag->childNodes->item(0)->nodeValue)];
        }

        // Iterate through each link
        foreach ($links as $key => list($href, $title, $text)) {
            // Check for empty HREFs, duplicate HREFs
            if (!isset(trim($href)[0]) or in_array($href, $tracking)) {

                // Remove empty links
                unset($links[$key]);
            } else {
                // Add the absolute path
                $links[$key] = array(
                    'Type'  => self::link_type($href),
                    'Path'  => $href,
                    'Crawl' => self::link_crawlable($href),
                    'Title' => (isset($title[0])) ? $title : false,
                    'Text'  => (isset($text[0])) ? $text : false,
                    'QueryString' => (strpos($href, '?')) ? true : false,
                );
            }

            // Add to tracking array
            $tracking[] = $href;
        }

        // Check for no links
        if (count($links) == 0) {

            // Make developer friendly variable
            return false;
        }

        // Renumber the $links array and return
        return array_values($links);
    }

    /**
     * Returns an absolute link path if crawlable or false if not crawlable.
     *
     * @return mixed
     */
    private static function link_type($href)
    {
        // Check for anchor links
        if (substr($href, 0, 1) == '#') {

            // Anchor, in-page link
            return 'anchor';
        }

        // Check for protocol-relative links
        elseif (substr($href, 0, 2) == '//') {

            // Link does not specify HTTP or HTTPS
            return 'protocol-relative';
        }

        // Check for absolute paths
        elseif (substr($href, 0, 4) == 'http') {

            // Absolute link
            return 'absolute';
        }

        // Relative path link
        return 'relative';
    }

    /**
     * Returns a link classification for based on the href.
     *
     * @return mixed
     */
    private static function link_crawlable($href)
    {
        // Check for anchor links
        if (substr($href, 0, 1) == '#') {

            // Cannot crawl
            return false;
        }

        if (stripos($href, 'mailto:')!==false) {

            // Possible email
            $email = trim(str_replace('mailto:', '', $href));

            // Validate and filter email
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {

                // Email
                return $email;
            }
        }

        // Check for protocol-relative links
        elseif (substr($href, 0, 2) == '//') {

            // Make crawlable using same protocol
            return filter_var(self::$data['crawler']->protocol().$href, FILTER_VALIDATE_URL);
        }

        // Check for absolute paths
        elseif (substr($href, 0, 4) == 'http') {

            // Already crawlable
            return filter_var($href, FILTER_VALIDATE_URL);
        }

        // Check for relative links (from root)
        elseif (substr($href, 0, 1) == '/') {

            // Transform the relative path into an absolute (crawlable) link path
            return filter_var(self::$data['crawler']->protocol().'://'.self::$data['crawler']->domain().$href, FILTER_VALIDATE_URL);
        }

        // Handle relative links based on path crawled
        elseif (substr(self::$data['crawler']->path(), -1) == '/') {

            // Transform the relative path (missing slash) based on path
            return filter_var(self::$data['crawler']->path().'/'.$href, FILTER_VALIDATE_URL);
        }

        // Handle relative links based on path crawled
        elseif (substr(self::$data['crawler']->path(), -1) != '/') {

            // Transform the relative path (missing slash) based on path
            return filter_var(self::$data['crawler']->protocol().'://'.self::$data['crawler']->path().'/'.$href, FILTER_VALIDATE_URL);
        }

        // Relative path from a file (.html, .php; eg., not ending in a trailing slash)
        $pathArr = explode('/', self::$data['crawler']->path());

        // Remove the last path item
        array_pop($pathArr);

        // Transform the relative path
        return filter_var(implode('/', $pathArr).'/'.$href, FILTER_VALIDATE_URL);
    }

    /**
     * Detects legacy (not support in HTML5) frames and iframes.
     *
     * @return array
     */
    private static function frames()
    {
        // Initialize variables
        $frames = array(
            'Sources' => array(),
            'Content' => '',
        );

        // Create an Xpath based on the DOM
        $xpath = new \DOMXpath(self::$data['dom']);

        // Iterate over tags
        foreach ($xpath->query('//frame | //iframe') as $tag) {

            // Check for blank src
            if (trim($tag->getAttribute('src')) != '') {

                // Get the NAME and SRC attributes from the tags into an array
                @$frames['Sources'][] = self::link_crawlable(trim($tag->getAttribute('src')));
            }
        }

        // Combine content from each frame
        foreach ($frames['Sources'] as $src) {

            // Append frame content
            @$frames['Content'] .= file_get_contents($src);
        }

        // Change DOM to reprocess Links
        self::domprocess($frames['Content']);

        // Process links for sub frames and iframes
        $frames['Links'] = self::links();

        // Parse content of frames
        $frames['Content'] = self::content($frames['Content']);

        // Check for empty sources
        if (count($frames['Sources']) == 0) {

            // No sources
            return false;
        }

        // Combined frames content and sources
        return $frames;
    }
}
