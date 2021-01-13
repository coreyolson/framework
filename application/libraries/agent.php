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
 * A class for creating user agents. Great for web crawlers or interfacing with
 * other APIs. An agent can be customized in many ways, including speed, security
 * and agent names. This is a base class for the Framework web crawler.
 *
 * @method libraries\agent::timeout();
 * @method libraries\agent::secure();
 * @method libraries\agent::agent_name();
 * @method libraries\agent::agent_site();
 * @method libraries\agent::agent_custom();
 * @method libraries\agent::user_agent();
 * @method libraries\agent::execute();
 * @method libraries\agent::status();
 * @method libraries\agent::path();
 * @method libraries\agent::protocol();
 * @method libraries\agent::domain();
 * @method libraries\agent::headers();
 * @method libraries\agent::body();
 * @method libraries\agent::redirects();
 * @method libraries\agent::details();
 * @method libraries\agent::get();
 * @method libraries\agent::post();
 * @method libraries\agent::put();
 * @method libraries\agent::delete();
 */
namespace libraries;

class agent
{
    // Framework traits
    use __aliases;

    // Alternative method names
    public static $__aliases = [
        'curl'   => ['execute'],
        'status' => ['status_code', 'code', 'http_code'],
    ];

    /**
     * Internal class variables.
     *
     * @var array
     */
    private $data;

    /**
     * Ignore duplicate CURL information.
     *
     * @var array
     */
    private static $ignore = array('url', 'content_type', 'http_code',
        'ssl_verify_result', 'redirect_count', 'redirect_url', 'certinfo', );

    /**
     * Internal class parameters for resetting.
     *
     * @var array
     */
    private $param = array(
        'pre'    => ['code', 'headers', 'protocol', 'domain', 'tld', 'root', 'body', 'details'],
        'post'   => ['post', 'put', 'delete', 'custom'],
        'status' => ['status', 'redirect'],
    );

    /**
     * Non-exhaustive list of common second-level domain TLDs. Should you absolutely need better
     * domain parsing consider using project: https://github.com/jeremykendall/php-domain-parser.
     *
     * @var array
     */
    private static $secondary = array('com', 'net', 'org', 'edu', 'gov', 'mil', 'int', 'rec', 'web',
        'nic', 'ltd', 'sch', 'soc', 'grp', 'asn', 'med', 'biz', 'gob', 'info', 'pro', 'nom', );

    /**
     * Initialize the agent class.
     *
     * @return
     */
    public function __construct()
    {
        // Initialize
        self::timeout();
        self::secure();

        // Configure user agent (name)
        $this->data['agent']['name'] = helpers\web::proper();

        // Configure user agent (site or domain)
        $this->data['agent']['site'] = helpers\web::site();

        // Prepares parameters for new CURL request
        $this->resetter('pre', 'post', 'status');

        // Maximum number of redirects
        $this->data['max_redirects'] = 10;
    }

    /**
     * Configure crawler timeout.
     *
     * @param int
     */
    public function timeout($timeout = 5000)
    {
        // Configure crawler speed in milliseconds
        $this->data['timeout'] = $timeout;
    }

    /**
     * Configure crawler SSL verification.
     *
     * @param bool
     */
    public function secure($secure = true)
    {
        // Configure crawler speed in milliseconds
        $this->data['secure'] = $secure;
    }

    /**
     * Configure the crawler user agent name.
     *
     * @param string
     */
    public function agent_name($name)
    {
        // Configure user agent (name)
        $this->data['agent']['name'] = $name;
    }

    /**
     * Configure the crawler user agent site.
     *
     * @param string
     */
    public function agent_site($site)
    {
        // Configure user agent (site or domain)
        $this->data['agent']['site'] = $site;
    }

    /**
     * Configure the crawler user agent site.
     *
     * @param string
     */
    public function agent_custom($custom)
    {
        // Configure a custom user agent
        $this->data['agent']['custom'] = $custom;
    }

    /**
     * Return default crawler user agent.
     *
     * @param string
     */
    public function user_agent()
    {
        // Check for a custom user agent
        if (isset($this->data['agent']['custom'])) {

            // Use the custom agent
            return $this->data['agent']['custom'];
        }

        // Auto-generated user agent
        return 'Mozilla/5.0 (compatible; '

            // Crawler Name
            .preg_replace("/((\.|^)([a-z0-9]){1,2}\.)|\./i", '', ucwords($this->data['agent']['name'],'.'))

            // Version, Website Information
            .'/1.0; +'.$this->data['agent']['site'].')';
    }

    /**
     * Configure HTTP headers to be sent.
     *
     * @param string
     */
    public function http_headers($headers)
    {
        // Set headers to be included
        $this->data['http_headers'] = $headers;
    }

    /**
     * Main Request Execution.
     *
     * @param mixed
     *
     * @return mixed
     */
    public function curl($path = null, $history = false)
    {
        // Check if an action has been specified
        if (is_null($path)) {

            // No destination
            return false;
        }

        // Remember the path
        $this->data['path'] = $path;

        // Redirect history
        if (!$history) {

            // Remove the redirect history
            $this->data['redirects'] = false;
        }

        // Configure the request
        curl_setopt_array(
            $this->data['curl'] = curl_init(), [
                CURLOPT_URL            => $this->data['path'],
                CURLOPT_HEADER         => true,
                CURLOPT_VERBOSE        => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT_MS     => $this->data['timeout'],
                CURLOPT_SSL_VERIFYHOST => ($this->data['secure']) ? 2 : false,
                CURLOPT_SSL_VERIFYPEER => $this->data['secure'],
                CURLOPT_FAILONERROR    => true,
                CURLOPT_USERAGENT      => self::user_agent(),
        ]);

        // Check for POST Data
        if (!is_null($this->data['http_headers'] ?? null)) {

            // Configure the HTTP Headers to include
            curl_setopt($this->data['curl'], CURLOPT_HTTPHEADER, $this->data['http_headers']);
        }

        // Check for POST Data
        if (!is_null($this->data['post'])) {

            // Configure for a POST Request
            curl_setopt($this->data['curl'], CURLOPT_POST, true);

            // Set the POST data
            curl_setopt($this->data['curl'], CURLOPT_POSTFIELDS, $this->data['post']);
        }

        // Check if this is a DELETE request
        if (!is_null($this->data['delete'])) {

            // Specialized custom DELETE headers added
            curl_setopt($this->data['curl'], CURLOPT_CUSTOMREQUEST, 'DELETE');
        }

        // Check if this is a PUT request
        if (!is_null($this->data['put'])) {

            // Specialized custom PUT headers added
            curl_setopt($this->data['curl'], CURLOPT_CUSTOMREQUEST, 'PUT');

            // Set the POST data
            curl_setopt($this->data['curl'], CURLOPT_POSTFIELDS, http_build_query($this->data['put']));
        }

        // Check if this is a CUSTOM request
        if (!is_null($this->data['custom'])) {

            // Special custom request
            curl_setopt($this->data['curl'], CURLOPT_CUSTOMREQUEST, $this->data['custom']);
        }

        // Cleanup previous data and prepare for a new request
        $this->resetter('pre', 'status');

        // Send and check for request errors
        if (!$this->data['request'] = curl_exec($this->data['curl'])) {

            // Zero out the status code
            $this->data['status'] = false;

            // Nullify the headers
            $this->data['headers'] = null;

            // Return the CURL error
            return $this->data['body'] = curl_error($this->data['curl']);
        }

        // Digest the CURL Request
        $this->process_request();

        // Detect redirects and record redirection history
        if (isset($this->data['code']) and in_array($this->data['code'], [301, 302, 303, 307, 308])) {

            // Inifite loop detection
            $this->data['redirects']['Loop'] = false;

            // Add to the redirect history
            $this->data['redirects'][] = array(
                'Status'   => $this->data['code'],
                'Path'     => $this->data['path'],
                'Protocol' => $this->data['protocol'],
                'Root'     => $this->data['root'],
                'Domain'   => $this->data['domain'],
                'TLD'      => $this->data['tld'],
                'Headers'  => $this->data['headers'],
                'Body'     => $this->data['body'],
                'Details'  => $this->data['details'],
            );

            // Detect maximum amount of redirects
            if (count($this->data['redirects']) < $this->data['max_redirects']) {

                // Iterate through previous redirects
                foreach ($this->data['redirects'] as $redirect) {

                    // Detect redirect loops
                    if ($redirect['Path'] == $this->data['headers']['Location']) {

                        // Inifite loop detected
                        $this->data['redirects']['Loop'] = true;

                        // Return and set the status code
                        return $this->data['status'] = $this->data['code'];
                    }
                }

                // Reissue the request (keeping redirect history)
                self::curl($this->data['headers']['Location'], true);
            }
        }

        // Cleanup data and prepare for a new request
        $this->resetter('post');

        // Return and set the status code
        return $this->data['status'] = $this->data['code'];
    }

    /**
     * CURL Request Response Status.
     *
     * @return string
     */
    public function status()
    {
        // Get the full HTTP Status
        return $this->data['code'];
    }

    /**
     * Returns the path used in the CURL Request.
     *
     * @return string
     */
    public function path()
    {
        // Get the path called
        return $this->data['path'];
    }

    /**
     * Returns the protocol used in the CURL Request.
     *
     * @return string
     */
    public function protocol()
    {
        // Get protocol used
        return $this->data['protocol'];
    }

    /**
     * Returns the domain root from the CURL Request.
     *
     * @return string
     */
    public function root()
    {
        // Get the root domain name
        return $this->data['root'];
    }

    /**
     * Returns the domain or sub-domain from the CURL Request.
     *
     * @return string
     */
    public function domain()
    {
        // Get the domain name (potentially a sub-domain)
        return $this->data['domain'];
    }

    /**
     * Returns the domain TLD from the CURL Request.
     *
     * @return string
     */
    public function tld()
    {
        // Get the TLD of the domain name
        return $this->data['tld'];
    }

    /**
     * CURL Request Response Headers.
     *
     * @return mixed
     */
    public function headers()
    {
        // Get the HTTP Headers
        return $this->data['headers'];
    }

    /**
     * CURL Request Response Body.
     *
     * @return mixed
     */
    public function body()
    {
        // Get the CURL Response Body
        return $this->data['body'];
    }

    /**
     * Request Redirect History.
     *
     * @return array
     */
    public function redirects()
    {
        // Get the redirects
        return $this->data['redirects'];
    }

    /**
     * CURL Request Detailed Information.
     *
     * @return mixed
     */
    public function details()
    {
        // Get the full HTTP Status
        return $this->data['details'];
    }

    /**
     * Wrapper for CURL for standard GET Requests.
     *
     * @param 	string
     *
     * @return mixed
     */
    public function get($path)
    {
        // Execute CURL request
        return self::curl($path);
    }

    /**
     * Wrapper for CURL for POST Requests.
     *
     * @param 	string
     * @param 	array
     *
     * @return mixed
     */
    public function post($path, $post = null)
    {
        // Set the POST data
        $this->data['post'] = $post;

        // Execute CURL request
        return self::curl($path);
    }

    /**
     * Wrapper for CURL for PUT Requests.
     *
     * @param string
     * @param array
     *
     * @return mixed
     */
    public function put($path, $put = null)
    {
        // Set the put data
        $this->data['put'] = $put;

        // Execute CURL request
        return self::curl($path);
    }

    /**
     * Wrapper for CURL for DELETE Requests.
     *
     * @param string
     *
     * @return mixed
     */
    public function delete($path)
    {
        // Set the delete parameter
        $this->data['delete'] = true;

        // Execute CURL request
        return self::curl($path);
    }

    /**
     * Wrapper for CURL for CUSTOM Requests.
     *
     * @param string
     *
     * @return mixed
     */
    public function custom($type, $path)
    {
        // Set the custom parameter
        $this->data['custom'] = $type;

        // Execute CURL request
        return self::curl($path);
    }

    /**
     * Wrapper for CURL for Framework PORT requests.
     *
     * @param string
     *
     * @return mixed
     */
    public function port($path)
    {
        // Wrapper for CUSTOM Framework PORT request.
        return self::custom('PORT', $path);
    }

    /**
     * Separates logic from curl() and handles most of the variable processing.
     *
     * @param mixed
     * @param mixed
     */
    private function process_request()
    {
        // Start parsing the headers
        foreach (explode("\r\n", substr($this->data['request'], 0, strpos($this->data['request'], "\r\n\r\n"))) as $headerline) {

            // Explode each headerline into KEY and VALUE
            list($key, $value) = array_pad(explode(': ', $headerline), 2, null);

            // Save the response headers
            $this->data['headers'][$key] = $value;
        }

        // Standardize Header Location / Link for developers
        $this->data['headers']['Path'] = $this->data['path'];

        // Set the header status code
        $this->data['headers']['Status'] = key($this->data['headers']);

        // Set the CURL status code
        $this->data['code'] = curl_getinfo($this->data['curl'], CURLINFO_HTTP_CODE);

        // Set the CURL body
        $this->data['body'] = substr($this->data['request'], curl_getinfo($this->data['curl'], CURLINFO_HEADER_SIZE));

        // Iterate through information from CURL Request details
        foreach ($curlDetails = curl_getinfo($this->data['curl']) as $key => $cInfo) {

            // Detect duplicate information
            if (!in_array($key, self::$ignore)) {

                // Standardize array key formatting and add to CURL Request details
                $this->data['details'][ implode('-', array_map('ucwords', explode('_', str_replace('ip', 'IP', $key)))) ] = $cInfo;
            }
        }

        // Parse the $path domain information
        $domainArr = array_filter(explode('/', $curlDetails['url']));

        // Set the protocol used
        $this->data['protocol'] = strtolower(substr(array_shift($domainArr), 0, -1));

        // Set the domain name (might be a sub-domain)
        $this->data['domain'] = array_shift($domainArr);

        // Parse the domain and get the root
        $rootArr = explode('.', $this->data['domain']);

        // Set the domain name TLD
        $tldBackup = $this->data['tld'] = array_pop($rootArr);

        // Next piece is a wildcard (e.g., second-level TLD)
        $mixed = array_pop($rootArr);

        // Check for third-level domains and second-level TLDs
        if (strlen($mixed) <= 2 or in_array($mixed, self::$secondary)) {

            // Reset the domain name TLD using the secondary $mixed piece
            $this->data['tld'] = $mixed.'.'.$this->data['tld'];

            // Set the root using the second-level domain
            $this->data['root'] = array_pop($rootArr).'.'.$this->data['tld'];

            // Exceptions for sites like Web.com
            if ($this->data['domain'] == $this->data['root']) {

                // Revert back to first TLD
                $this->data['tld'] = $tldBackup;

                // Reset the root
                $this->data['root'] = $mixed.'.'.$this->data['tld'];
            }

            // Stop processing
            return false;
        }

        // Seems to be a normal second level domain and TLD
        $this->data['root'] = $mixed.'.'.$this->data['tld'];
    }

    /**
     * Resets parameters to defaults for next CURL Request.
     *
     * @param mixed
     */
    private function resetter(...$args)
    {
        // Params as keys
        $args = array_flip($args);

        // Parameter types for NULL resetting
        foreach (['pre', 'post'] as $arg) {

            // Check which type
            if (isset($args[$arg])) {

                // Iterate through these parameters
                foreach ($this->param[$arg] as $key) {

                    // Reset params
                    $this->data[$key] = null;
                }
            }
        }

        // Parameters for FALSE resetting
        if (isset($args['status'])) {

            // Iterate through these parameters
            foreach ($this->param['status'] as $key) {

                // Reset params
                $this->data[$key] = false;
            }
        }
    }
}
