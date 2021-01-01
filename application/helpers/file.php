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
 * A helper class for working with files
 *
 * @method helpers\file::download();
 * @method helpers\file::unzip();
 * @method helpers\file::remote_package();
 * @method helpers\file::inclusive();
 * @method helpers\file::unignore( $file );
 * @method helpers\file::exclusive();
 * @method helpers\file::ignore( $file );
 * @method helpers\file::contents( $path [, boolean] );
 * @method helpers\file::map( $path [, boolean] );
 * @method helpers\file::folders( $path [, boolean] );
 * @method helpers\file::files( $path [, boolean] );
 * @method helpers\file::hidden( $path );
 * @method helpers\file::summary( $path [, boolean] );
 * @method helpers\file::file_info( $path );
 * @method helpers\file::dir_info( $path );
 * @method helpers\file::info( $path );
 * @method helpers\file::permissions( $mixed );
 * @method helpers\file::octal( $path );
 * @method helpers\file::size( $mixed, $precision );
 * @method helpers\file::lines( $path );
 */
namespace helpers;

class file
{
    // Framework traits
    use __aliases, __singleton;

    // Alternative method names
    public static $__aliases = [
        'folders'     => ['dir', 'dirs', 'folder'],
        'permissions' => ['perms', 'symbolic', 'full_permissions', 'linux_permissions'],
    ];

    /**
     * An array of files to exclude (e.g., Mac / Windows tracking files).
     *
     * @var array
     */
    public static $exclude = ['.DS_Store', 'Thumbs.db', '.git'];

    /**
     * An internal array for class settings.
     *
     * @var array
     */
    public static $settings;

    /**
     * Initialize file helper class.
     *
     * @return object
     */
    public function __construct()
    {
        // By default exclude annoying files
        self::exclusive();
    }

    /**
     * Downloads a remote file to a local file.
     *
     * @return void
     */
    public static function download($local, $remote)
    {
        // Create download agent
        $agent = new libraries\agent;

        // Download file
        $agent->get($remote);

        // Open a local file
        $fh = fopen($local, "w+");

        // Write to a local file
        fputs($fh, $agent->body());

        // Done
        fclose($fh);
    }

    /**
     * Unzips an archive.
     *
     * @return void
     */
    public static function unzip($name, $path = '.', $unlink = false)
    {
        // Folder before unzip
        $original = scandir($path);

        // Create handler
        $zh = new \ZipArchive;

        // Open file
        $zh->open($name);

        // Unzips file
        $zh->extractTo($path);

        // Keep?
        if ($unlink) {

            // Delete file
            unlink($name);
        }

        // Close handler
        $zh->close();

        // Extract folders / files from unzip operation
        foreach(array_values(array_diff(scandir($path), $original)) as $file) {

            // Extracted file information
            $files[$file] = pathinfo($file);
        }

        // Extraction status
        return $files ?? false;
    }

    /**
     * Downloads, unzips and installs remote package.
     *
     * @return boolean
     */
    public static function remote_package($path, $remote)
    {
        // Download remote package
        self::download($path.'/tmp.zip', $remote);

        // Unzip remote package; unlink
        return self::unzip($path.'tmp.zip', $path, true);
    }

    /**
     * Change the setting to include self::$exclude files.
     *
     * @return array
     */
    public static function inclusive()
    {
        // Change setting
        self::$settings['exclude'] = false;

        // Return exclusion list
        return self::$exclude;
    }

    /**
     * Remove a file from the exclusion list.
     *
     * @return array
     */
    public static function unignore($file)
    {
        // Remove file from the exclusion array
        return self::$exclude = array_diff(self::$exclude, array($file));
    }

    /**
     * Change the setting to exclude self::$exclude files.
     *
     * @return array
     */
    public static function exclusive()
    {
        // Change setting
        self::$settings['exclude'] = true;

        // Return exclusion list
        return self::$exclude;
    }

    /**
     * Remove a file from the exclusion list.
     *
     * @return array
     */
    public static function ignore($file)
    {
        // Remove file from exclude list
        self::$exclude[] = $file;

        // Return exclusion list
        return self::$exclude;
    }

    /**
     * Get the contents of a directory in an array.
     *
     * @param string
     *
     * @return array
     */
    public static function contents($path, $hidden = false)
    {
        // Exclude specific files
        $exclude = array('.', '..');

        // Check class settings
        if (self::$settings['exclude']) {

            // Merge exclude file list
            $exclude = array_merge($exclude, self::$exclude);
        }

        // Iterate through contents
        foreach ($contents = scandir($path) as $k => $item) {

            // Check for hidden files
            if (substr($item, 0, 1) == '.') {

                // Remove hidden files from array
                $hiddenArr[] = $item;
            }
        }

        // With hidden
        if ($hidden === true) {

            // Return visible and hidden files
            return array_diff($contents, array_merge($exclude));
        }

        // Only hidden
        elseif ($hidden === 'only') {

            // Return only hidden files
            return array_diff($hiddenArr, array_merge($exclude));
        }

        // Return the visible contents of a directory
        return array_values(array_diff($contents, array_merge($exclude, $hiddenArr)));
    }

    /**
     * Recursively maps a directory into a multi-dimensionsal array.
     *
     * @param string
     *
     * @return mixed
     */
    public static function map($path, $hidden = false)
    {
        // Check for directory
        if (is_dir($path)) {

            // Iterate through directory contents
            foreach (self::contents($path, $hidden) as $k => $item) {

                // Check for nested directories
                if (is_dir($i = $path.'/'.$item)) {

                    // Recursive merge nested directories into content array
                    $contents[$i] = array_merge(self::dir_info($i), ['contents' => self::map($i, $hidden)]);
                } else {
                    // Get the file information
                    $contents[$i] = self::file_info($i);
                }
            }
        } else {
            // Check if this is a file
            if (file_exists($path)) {

                // Return the file information
                return self::file_info($path);
            } else {
                // Unexpected input
                return false;
            }
        }

        // Return directory map
        return array_values($contents);
    }

    /**
     * Get only the folders within a directory in an array.
     *
     * @param string
     *
     * @return array
     */
    public static function folders($path, $hidden = false)
    {
        // Pass to self::contents();
        $contents = self::contents($path, $hidden);

        // Iterate through contents
        foreach ($contents as $k => $item) {

            // Check if this is a directory
            if (!is_dir($path.'/'.$item)) {

                // Remove files from array
                unset($contents[$k]);
            }
        }

        // Return the contents of a directory
        return array_values($contents);
    }

    /**
     * Get only the files within a directory in an array.
     *
     * @param string
     *
     * @return array
     */
    public static function files($path, $hidden = false)
    {
        // Pass to self::contents();
        $contents = self::contents($path, $hidden);

        // Iterate through contents
        foreach ($contents as $k => $item) {

            // Check if this is a directory
            if (is_dir($path.'/'.$item)) {

                // Remove directories from array
                unset($contents[$k]);
            }
        }

        // Return the contents of a directory
        return array_values($contents);
    }

    /**
     * Get only the hidden items within a directory in an array.
     *
     * @param string
     *
     * @return array
     */
    public static function hidden($path)
    {
        // Pass to self::contents();
        return array_values(self::contents($path, 'only'));
    }

    /**
     * Return summary information of a path, auto detecting file or directory.
     *
     * @param string
     *
     * @return mixed
     */
    public static function summary($path, $hidden = false)
    {
        // Check for file
        if (!is_dir($path)) {

            // Get the file information
            return self::file_info($path);
        }

        // Check for dir
        if (is_dir($path)) {

            // Iterate through contents
            foreach ($contents = self::contents($path, $hidden) as $k => $item) {

                // Check item type
                if (is_dir($i = $path.'/'.$item)) {

                    // Get directory information
                    $contents[$k] = self::dir_info($i);
                } else {
                    // Get file information
                    $contents[$k] = self::file_info($i);
                }
            }
        }

        // Return the summary
        return array_values($contents);
    }

    /**
     * Return the file information in an array if it exists.
     *
     * @param string
     *
     * @return mixed
     */
    public static function file_info($path)
    {
        // Check if file exists
        if (!file_exists($path)) {

            // File does not exist
            return false;
        }

        // Return extensive file information
        return array(
            'type' => 'file',
            'name' => basename($path),
            'path' => $path,
            'bytes' => filesize($path),
            'size' => self::size($path),
            'date' => filemtime($path),
            'readable' => is_readable($path),
            'writable' => is_writable($path),
            'executable' => is_executable($path),
            'permissions' => self::permissions($path),
            'octal' => self::octal($path),
        );
    }

    /**
     * Returns the directory information in an array if it exists.
     *
     * @param string
     *
     * @return mixed
     */
    public static function dir_info($path)
    {
        // Check if file exists
        if (!file_exists($path)) {

            // File does not exist
            return false;
        }

        // Return extensive file information
        return array(
            'type' => 'dir',
            'name' => basename($path),
            'path' => $path,
            'bytes' => false,
            'size' => false,
            'date' => filemtime($path),
            'readable' => is_readable($path),
            'writable' => is_writable($path),
            'executable' => is_executable($path),
            'permissions' => self::permissions($path),
            'octal' => self::octal($path),
        );
    }

    /**
     * Returns information on the path provided (auto-detect file / directory).
     *
     * @param string
     *
     * @return mixed
     */
    public static function info($path)
    {
        // Check if directory
        if (is_dir($path)) {

            // Return the directory information
            return self::dir_info($path);
        }

        // Check if file
        if (file_exists($path)) {

            // Return the file information
            return self::file_info($path);
        }

        // Unexpected input
        return false;
    }

    /**
     * Returns the linux style file permissions; e.g., -rw-r--r--.
     *
     * @param mixed
     *
     * @return string
     */
    public static function permissions($mixed)
    {
        // Detect input type
        if (is_string($mixed)) {

            // Get the file permissions
            $octal = self::octal($mixed);
        } else {
            // Already in octal format (hopefully)
            $octal = $mixed;
        }

        // Begin symbolic processing
        if (($octal & 0xC000) === 0xC000) {

            // Socket
            $permissions = 's';
        } elseif (($octal & 0xA000) === 0xA000) {

            // Symbolic Link
            $permissions = 'l';
        } elseif (($octal & 0x8000) === 0x8000) {

            // Regular
            $permissions = '-';
        } elseif (($octal & 0x6000) === 0x6000) {

            // Block special
            $permissions = 'b';
        } elseif (($octal & 0x4000) === 0x4000) {

            // Directory
            $permissions = 'd';
        } elseif (($octal & 0x2000) === 0x2000) {

            // Character special
            $permissions = 'c';
        } elseif (($octal & 0x1000) === 0x1000) {

            // FIFO pipe
            $permissions = 'p';
        } else {
            // Unknown
            $permissions = 'u';
        }

        // Get the Owner Permissions
        $permissions .=

            // Readable
            (($octal & 0x0100) ? 'r' : '-')

                        // Writeable
            .(($octal & 0x0080) ? 'w' : '-')

                        // Other
            .(($octal & 0x0040) ?

                // Executable
                (($octal & 0x0800) ? 's' : 'x') :

                // Special
                (($octal & 0x0800) ? 'S' : '-'));

        // Get the Group Permissions
        $permissions .=

            // Readable
            (($octal & 0x0020) ? 'r' : '-')

                        // Writeable
            .(($octal & 0x0010) ? 'w' : '-')

                        // Other
            .(($octal & 0x0008) ?

                // Executable
                (($octal & 0x0400) ? 's' : 'x') :

                // Special
                (($octal & 0x0400) ? 'S' : '-'));

        // Get the World Permissions
        $permissions .=

            // Readable
            (($octal & 0x0004) ? 'r' : '-')

                        // Writeable
            .(($octal & 0x0002) ? 'w' : '-')

                        // Other
            .(($octal & 0x0001) ?

                // Executable
                (($octal & 0x0200) ? 't' : 'x') :

                // Sticky
                (($octal & 0x0200) ? 'T' : '-'));

        // Return full (linux style) permissions
        return $permissions;
    }

    /**
     * Return the octal file permissions; e.g., 644.
     *
     * @param string
     *
     * @return int
     */
    public static function octal($path)
    {
        // Return the file permissions in octal format
        return (int) substr(sprintf('%o', fileperms($path)), -3);
    }

    /**
     * Converts bytes to the nearest size of a higher magnitude.
     *
     * @param mixed
     * @param int
     *
     * @return string
     */
    public static function size($mixed, $precision = 2, $kilo = 1024)
    {
        // Check input
        if (is_int($mixed)) {

            // Already in bytes format (hopefully)
            $bytes = $mixed;
        }

        // Check if file exists
        elseif (is_file($mixed)) {

            // Get the file size
            $bytes = filesize($mixed);

        }  elseif (is_dir($mixed)) {

            // Ignore '.' and '..' in directories
            $fileIterator = new \RecursiveDirectoryIterator($mixed, \FilesystemIterator::SKIP_DOTS);

            // Iterator through subdirectories recursively
            foreach(new \RecursiveIteratorIterator( $fileIterator ) as $file) {

                // Calculating byte size
                $bytes = ($bytes ?? 0) + $file->getSize();
            }

        } else {

            // Unexpected input
            throw new \Exception('Unable to calculate size. Unexpected input.');
        }

        // Division by zero
        if (!$bytes) {
            return '0 B';
        }

        // Array of sizes
        $sizes = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');

        // Gets the exponential size
        $exponent = floor(log($bytes, $kilo));

        // Perform calculation of bytes to larger sizes and return
        return round($bytes / pow($kilo, $exponent), $precision).' '.$sizes[$exponent];
    }

    /**
     * Counts lines in a file or directory.
     *
     * @param string
     *
     * @return int
     */
    public static function lines($path)
    {

        // Check if file exists
        if (is_file($path)) {

            // Get the file size
            $lines = self::_lines($path);

        // Detect directories
        }  elseif (is_dir($path)) {

            // Ignore '.' and '..' in directories
            $fileIterator = new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS);

            // Iterator through subdirectories recursively
            foreach(new \RecursiveIteratorIterator( $fileIterator ) as $file) {

                // Calculating byte size
                $lines = ($lines ?? 0) + self::_lines($file->getPathname());
            }

        // Error
        } else {

            // Unexpected input
            throw new \Exception('Unable to calculate lines. Unexpected input.');
        }

        // Number of lines
        return $lines;
    }

    /**
     * Internal line counter for files.
     *
     * @param mixed
     * @param int
     *
     * @return string
     */
    private static function _lines($path, $count = 0)
    {
        // File handler
        $fh = fopen($path, "r");

        // Loop through file
        while(!feof($fh)){

            // Get the line
            $line = fgets($fh);

            // Increment
            $count++;
        }

        // Close handler
        fclose($fh);

        // Number of lines
        return $count;
    }
}
