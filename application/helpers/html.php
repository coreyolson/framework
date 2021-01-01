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
 * A helper class for working with HTML markup
 *
 * @method helpers\html::tag('h3#id.classone.classtwo', 'contents');
 * @method helpers\html::tag('p#intro', 'hello world');
 * @method helpers\html::tag('a.submit', 'submit', ['href' => '/link/path/']);
 */
namespace helpers;

class html
{
    // Framework traits
    use __aliases, __singleton;

    // Alternative method names
    public static $__aliases = [
        'tag' => ['element'],
    ];

    /**
     * An array of all valid HTML elements.
     *
     * @var array
     */
    private static $tags = array('a', 'abbr', 'address', 'area', 'article', 'aside', 'audio',
        'b', 'base', 'bdi', 'bdo', 'blockquote', 'body', 'br', 'button', 'canvas', 'caption',
        'cite', 'code', 'col', 'colgroup', 'command', 'datalist', 'dd', 'del', 'details',
        'dfn', 'div', 'dl', 'doctype', 'dt', 'em', 'embed', 'fieldset', 'figcaption',
        'figure', 'footer', 'form', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'head', 'header',
        'hr', 'html', 'i', 'iframe', 'img', 'input', 'ins', 'kbd', 'keygen', 'label',
        'legend', 'li', 'link', 'main', 'map', 'mark', 'menu', 'meta', 'meter', 'nav',
        'noscript', 'object', 'ol', 'optgroup', 'option', 'output', 'p', 'param', 'pre',
        'progress', 'q', 'rp', 'rt', 'ruby', 's', 'samp', 'script', 'section', 'select',
        'small', 'source', 'span', 'strong', 'style', 'sub', 'summary', 'sup', 'table',
        'tbody', 'td', 'textarea', 'tfoot', 'th', 'thead', 'time', 'title', 'tr', 'track',
        'u', 'ul', 'var', 'video', 'wbr', );

    /**
     * An array of all valid HTML elements without closing tags.
     *
     * @var array
     */
    private static $singleTags = array('area', 'base', 'br', 'col', 'command', 'doctype',
        'embed', 'hr', 'img', 'input', 'keygen', 'link', 'meta', 'source', 'track', 'wbr', );

    /**
     * Prepares an HTML element tag for self::create(). Attempts to detect CSS
     * selectors to make tags with ID and Classes. Only creates valid tags.
     *
     * @param string
     * @param string
     * @param array
     *
     * @return string
     */
    public static function tag($str, $contents = false, $attributes = array())
    {
        // Split by # and . to get element
        $iArr = preg_split('/(#|\.)/', $str);

        // Get the element
        $tag = strtolower($iArr[0]);

        // Check if this is a valid element
        if (!in_array($tag, self::$tags)) {

            // Not a valid tag
            return false;
        }

        // Search for an element ID
        preg_match('/#\w+/', $str, $idArr);

        // Get the ID if specified
        $id = (isset($idArr[0])) ? substr($idArr[0], 1) : false;

        // Search for class names
        preg_match_all('/\.[\w-]+/', $str, $classArr);

        // Check for classes
        if (count($classArr[0])) {

            // Get the classes
            foreach ($classArr[0] as $class) {

                // Add classes and remove CSS selection
                $classes[] = substr($class, 1);
            }
        } else {
            // No classes provided
            $classes = array();
        }

        // Create the tag
        return self::create($tag, $id, $classes, $contents, $attributes);
    }

    /**
     * Forms the HTML tag string based on parameters.
     *
     * @param string
     * @param string
     * @param array
     * @param string
     *
     * @return string
     */
    private static function create($tag, $id, $classes, $contents, $attributes)
    {
        // Create a string
        $str = '';

        // Add the opening tag
        $str .= '<'.$tag;

        // Check if an ID has been specified
        if ($id) {

            // Add ID to the element if specified
            $str .= ' id="'.$id.'"';
        }

        // Check if there are any classes
        if (count($classes)) {

            // Add class(es) to the HTML element
            $str .= ' class="'.implode(' ', $classes).'"';
        }

        // Check if there are any attributes to add
        if (count($attributes)) {

            // Iterate through the attributes
            foreach ($attributes as $attr => $value) {

                // Add the attributes
                $str .= ' '.$attr.'="'.$value.'"';
            }
        }

        // Check for the type of HTML element
        if (in_array($tag, self::$singleTags)) {

            // Add closing tag for single tag elements
            return $str .= ' />';
        } else {
            // This is a normal HTML element
            $str .= '>';
        }

        // Add contents to the element string
        $str .= $contents;

        // Add closing tag for normal tag elements
        $str .= '</'.$tag.'>';

        // Return HTML element string
        return $str;
    }

    /**
     * Create a mailto link with optional ordinal encoding.
     *
     * @param string
     * @param bool
     *
     * @return object
     */
    public static function mailto($email, $encode = false)
    {
        // Create a variable for output
        $ordinal = '';

        // Iterate through each letter in the email string
        for ($i = 0; $i < strlen($email); ++$i) {

            // Begin concatenation of the email string
            $ordinal .= ($encode) ? '&#'.ord($email[$i]).';' : $email[$i];
        }

        // Return mailto tag
        return $ordinal;
    }
}
