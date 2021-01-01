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
 * An inflector class for singularizing and pluralizing English words. This helper
 * basically uses the Doctrine inflector code with some aethetics modifications and
 * attemps to improve readability by refactoring the code and moving things around.
 *
 * @method helpers\inflector::singular('string');   // books, songs, movies
 * @method helpers\inflector::plural('string');     // book, song, movie
 *
 * -----------------------------------------------------------------------------------------
 *
 * The methods in these classes are from several different sources collected
 * across several different php projects and several different authors. The
 * original author names and emails are not known. Pluralize & Singularize
 * implementation are borrowed from CakePHP with some modifications.
 *
 * @link        www.doctrine-project.org
 * @link        https://github.com/doctrine/inflector
 * @license     https://opensource.org/licenses/MIT
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 *
 * Code licensed under MIT License, and modified for use in Framework
 */
namespace helpers;

class inflector
{
    // Framework traits
    use __aliases, __singleton;

    // Alternative method names
    public static $__aliases = [
        'singular' => ['single', 'to_singular', 'tosingle', 'singularize'],
        'plural'   => ['multiple', 'to_plural', 'toplural', 'pluralize'],
    ];

    /**
     * Internal class storage.
     *
     * @var array
     */
    private static $data = array();

    /**
     * Plural inflector rules.
     *
     * @var array
     */
    private static $plural = array(
        'rules' => array(
            '/(s)tatus$/i'                                                           => '\1\2tatuses',
            '/(quiz)$/i'                                                             => '\1zes',
            '/^(ox)$/i'                                                              => '\1\2en',
            '/([m|l])ouse$/i'                                                        => '\1ice',
            '/(matr|vert|ind)(ix|ex)$/i'                                             => '\1ices',
            '/(x|ch|ss|sh)$/i'                                                       => '\1es',
            '/([^aeiouy]|qu)y$/i'                                                    => '\1ies',
            '/(hive)$/i'                                                             => '\1s',
            '/(?:([^f])fe|([lr])f)$/i'                                               => '\1\2ves',
            '/sis$/i'                                                                => 'ses',
            '/([ti])um$/i'                                                           => '\1a',
            '/(p)erson$/i'                                                           => '\1eople',
            '/(m)an$/i'                                                              => '\1en',
            '/(c)hild$/i'                                                            => '\1hildren',
            '/(f)oot$/i'                                                             => '\1eet',
            '/(buffal|her|potat|tomat|volcan)o$/i'                                   => '\1\2oes',
            '/(alumn|bacill|cact|foc|fung|nucle|radi|stimul|syllab|termin|vir)us$/i' => '\1i',
            '/us$/i'                                                                 => 'uses',
            '/(alias)$/i'                                                            => '\1es',
            '/(analys|ax|cris|test|thes)is$/i'                                       => '\1es',
            '/s$/'                                                                   => 's',
            '/^$/'                                                                   => '',
            '/$/'                                                                    => 's',
        ),
        'uninflected' => array(
            '.*[nrlm]ese', '.*deer', '.*fish', '.*measles', '.*ois', '.*pox', '.*sheep', 'people', 'cookie',
        ),
        'irregular' => array(
            'atlas'     => 'atlases', 'axe'           => 'axes', 'beef'        => 'beefs', 'brother'          => 'brothers', 'cafe'    => 'cafes',
            'chateau'   => 'chateaux', 'child'        => 'children', 'cookie'  => 'cookies', 'corpus'         => 'corpuses', 'cow'     => 'cows',
            'criterion' => 'criteria', 'curriculum'   => 'curricula', 'demo'   => 'demos', 'domino'           => 'dominoes', 'echo'    => 'echoes',
            'foot'      => 'feet', 'fungus'           => 'fungi', 'ganglion'   => 'ganglions', 'genie'        => 'genies', 'genus'     => 'genera',
            'graffito'  => 'graffiti', 'hippopotamus' => 'hippopotami', 'hoof' => 'hoofs', 'human'            => 'humans', 'iris'      => 'irises',
            'leaf'      => 'leaves', 'loaf'           => 'loaves', 'man'       => 'men', 'medium'             => 'media', 'memorandum' => 'memoranda',
            'money'     => 'monies', 'mongoose'       => 'mongooses', 'motto'  => 'mottoes', 'move'           => 'moves', 'mythos'     => 'mythoi',
            'niche'     => 'niches', 'nucleus'        => 'nuclei', 'numen'     => 'numina', 'occiput'         => 'occiputs', 'octopus' => 'octopuses',
            'opus'      => 'opuses', 'ox'             => 'oxen', 'penis'       => 'penises', 'person'         => 'people', 'plateau'   => 'plateaux',
            'runner-up' => 'runners-up', 'sex'        => 'sexes', 'soliloquy'  => 'soliloquies', 'son-in-law' => 'sons-in-law',
            'syllabus'  => 'syllabi', 'testis'        => 'testes', 'thief'     => 'thieves', 'tooth'          => 'teeth', 'tornado'    => 'tornadoes',
            'trilby'    => 'trilbys', 'turf'          => 'turfs', 'volcano'    => 'volcanoes',
        ),
    );

    /**
     * Singular inflector rules.
     *
     * @var array
     */
    private static $singular = array(
        'rules' => array(
            '/(s)tatuses$/i'                                                          => '\1\2tatus',
            '/^(.*)(menu)s$/i'                                                        => '\1\2',
            '/(quiz)zes$/i'                                                           => '\\1',
            '/(matr)ices$/i'                                                          => '\1ix',
            '/(vert|ind)ices$/i'                                                      => '\1ex',
            '/^(ox)en/i'                                                              => '\1',
            '/(alias)(es)*$/i'                                                        => '\1',
            '/(buffal|her|potat|tomat|volcan)oes$/i'                                  => '\1o',
            '/(alumn|bacill|cact|foc|fung|nucle|radi|stimul|syllab|termin|viri?)i$/i' => '\1us',
            '/([ftw]ax)es/i'                                                          => '\1',
            '/(analys|ax|cris|test|thes)es$/i'                                        => '\1is',
            '/(shoe|slave)s$/i'                                                       => '\1',
            '/(o)es$/i'                                                               => '\1',
            '/ouses$/'                                                                => 'ouse',
            '/([^a])uses$/'                                                           => '\1us',
            '/([m|l])ice$/i'                                                          => '\1ouse',
            '/(x|ch|ss|sh)es$/i'                                                      => '\1',
            '/(m)ovies$/i'                                                            => '\1\2ovie',
            '/(s)eries$/i'                                                            => '\1\2eries',
            '/([^aeiouy]|qu)ies$/i'                                                   => '\1y',
            '/([lr])ves$/i'                                                           => '\1f',
            '/(tive)s$/i'                                                             => '\1',
            '/(hive)s$/i'                                                             => '\1',
            '/(drive)s$/i'                                                            => '\1',
            '/([^fo])ves$/i'                                                          => '\1fe',
            '/(^analy)ses$/i'                                                         => '\1sis',
            '/(analy|diagno|^ba|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i'             => '\1\2sis',
            '/([ti])a$/i'                                                             => '\1um',
            '/(p)eople$/i'                                                            => '\1\2erson',
            '/(m)en$/i'                                                               => '\1an',
            '/(c)hildren$/i'                                                          => '\1\2hild',
            '/(f)eet$/i'                                                              => '\1oot',
            '/(n)ews$/i'                                                              => '\1\2ews',
            '/eaus$/'                                                                 => 'eau',
            '/^(.*us)$/'                                                              => '\\1',
            '/s$/i'                                                                   => '',
        ),
        'uninflected' => array(
            '.*[nrlm]ese', '.*deer', '.*fish', '.*measles', '.*ois', '.*pox', '.*sheep', '.*ss',
        ),
        'irregular' => array(
            'criteria' => 'criterion', 'curves' => 'curve', 'emphases' => 'emphasis', 'foes' => 'foe',
            'hoaxes' => 'hoax', 'media' => 'medium', 'neuroses' => 'neurosis', 'waves' => 'wave', 'oases' => 'oasis',
        ),
    );

    /**
     * Words that should not be inflected.
     *
     * @var array
     */
    private static $uninflected = array(
        'Amoyese', 'bison', 'Borghese', 'bream', 'breeches', 'britches', 'buffalo', 'cantus',
        'carp', 'chassis', 'clippers', 'cod', 'coitus', 'Congoese', 'contretemps', 'corps',
        'debris', 'diabetes', 'djinn', 'eland', 'elk', 'equipment', 'Faroese', 'flounder',
        'Foochowese', 'gallows', 'Genevese', 'Genoese', 'Gilbertese', 'graffiti',
        'headquarters', 'herpes', 'hijinks', 'Hottentotese', 'information', 'innings',
        'jackanapes', 'Kiplingese', 'Kongoese', 'Lucchese', 'mackerel', 'Maltese', '.*?media',
        'mews', 'moose', 'mumps', 'Nankingese', 'news', 'nexus', 'Niasese',
        'Pekingese', 'Piedmontese', 'pincers', 'Pistoiese', 'pliers', 'Portuguese',
        'proceedings', 'rabies', 'rice', 'rhinoceros', 'salmon', 'Sarawakese', 'scissors',
        'sea[- ]bass', 'series', 'Shavese', 'shears', 'siemens', 'species', 'staff', 'swine',
        'testes', 'trousers', 'trout', 'tuna', 'Vermontese', 'Wenchowese', 'whiting',
        'wildebeest', 'Yengeese', 'data', 'audio', 'compensation', 'coreopsis', 'education',
        'gold', 'knowledge', 'love', 'rain', 'money', 'offspring', 'plankton', 'police',
        'species', 'traffic', 'meta', 'php', 'html', 'md', 'xml', 'txt',
    );

    /**
     * Initialize inflector helper class.
     *
     * @return object
     */
    public function __construct()
    {
        // Setup internal data arrays
        self::internals();
    }

    /**
     * The static self::$plural and self::$singular arrays are largely rules and
     * definitions that need to be initialized. Doctrine "merges" and copies the
     * initial rules into a "merged" array, which includes self::$uninflected.
     */
    private static function internals()
    {
        // Plural Shortcut references (readability)
        $pp = &self::$plural;
        $pm = &self::$plural['merged'];
        $pd = &self::$data['plural'];

        // Prepare the plural internals
        $pm['irregular'] = $pp['irregular'];
        $pm['uninflected'] = array_merge($pp['uninflected'], self::$uninflected);
        $pp['cacheUninflected'] = '(?:'.implode('|', $pm['uninflected']).')';
        $pp['cacheIrregular'] = '(?:'.implode('|', array_keys($pm['irregular'])).')';

        // Singular Shortcut references (readability)
        $sp = &self::$singular;
        $sm = &$sp['merged'];
        $sd = &self::$data['singular'];

        // Prepare the singular internals
        $sm['uninflected'] = array_merge($sp['uninflected'], self::$uninflected);
        $sm['irregular'] = array_merge($sp['irregular'], array_flip($pp['irregular']));
        $sp['cacheUninflected'] = '(?:'.implode('|', $sm['uninflected']).')';
        $sp['cacheIrregular'] = '(?:'.implode('|', array_keys($sm['irregular'])).')';
    }

    /**
     * Returns a word in plural form.
     *
     * @param string
     *
     * @return string
     */
    public static function plural($str)
    {
        // Shortcut references (readability)
        $pp = &self::$plural;
        $pm = &self::$plural['merged'];
        $pd = &self::$data['plural'];

        // Check the internal cache (performance)
        if (isset($pd[$str])) {

            // Return matches from the cache
            return $pd[$str];
        }

        // Check against irregular plurals
        if (preg_match('/(.*)\\b('.$pp['cacheIrregular'].')$/i', $str, $regs)) {

            // Return match while also setting the internal cache (faster next time this word is referenced)
            return $pd[$str] = $regs[1].substr($str, 0, 1).substr($pm['irregular'][strtolower($regs[2])], 1);
        }

        // Check against the uninflected plurals
        if (preg_match('/^('.$pp['cacheUninflected'].')$/i', $str, $regs)) {

            // Return and cache
            return $pd[$str] = $str;
        }

        // Normal word (not irregular or uninflected)
        foreach ($pp['rules'] as $rule => $replacement) {

            // Find a matching rule
            if (preg_match($rule, $str)) {

                // Cache the word for next time and return the inflected
                return $pd[$str] = preg_replace($rule, $replacement, $str);
            }
        }
    }

    /**
     * Returns a word in singular form.
     *
     * @param string
     *
     * @return string
     */
    public static function singular($str)
    {
        // Shortcut references (readability)
        $sp = &self::$singular;
        $pp = &self::$plural;
        $sm = &$sp['merged'];
        $sd = &self::$data['singular'];

        // Check the internal cache (performance)
        if (isset($sd[$str])) {

            // Return matches from the cache
            return $sd[$str];
        }

        // Check against irregular singulars
        if (preg_match('/(.*)\\b('.$sp['cacheIrregular'].')$/i', $str, $regs)) {

            // Return match while also setting the internal cache (faster next time this word is referenced)
            return $sd[$str] = $regs[1].substr($str, 0, 1).substr($sm['irregular'][strtolower($regs[2])], 1);
        }

        // Check against the uninflected singulars
        if (preg_match('/^('.$sp['cacheUninflected'].')$/i', $str, $regs)) {

            // Return and cache
            return $sd[$str] = $str;
        }

        // Normal word (not irregular or uninflected)
        foreach ($sp['rules'] as $rule => $replacement) {

            // Find a matching rule
            if (preg_match($rule, $str)) {

                // Cache the word for next time and return the inflected
                return preg_replace($rule, $replacement, $str);
            }
        }

        return $sd[$str] = $str;
    }
}
