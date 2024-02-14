<?php

/**
 * Class that collects the functions of initial checks on the requirements to run the plugin
 *
 * @package   Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 */

namespace Duplicator\Installer\Core\Addons;

abstract class InstAbstractAddonCore
{
    const ADDON_DATA_CONTEXT = 'duplicator_addon';

    /**
     * Addons instances
     *
     * @var [self]
     */
    private static $instances = array();

    /**
     * Current addon data
     *
     * @var array
     */
    protected $addonData = array();

    /**
     * Get curent addon instance
     *
     * @return self
     */
    public static function getInstance()
    {
        $class = get_called_class();
        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = new static();
        }

        return self::$instances[$class];
    }

    /**
     * Constructor
     */
    protected function __construct()
    {
        $reflect         = new \ReflectionClass(get_called_class());
        $this->addonData = self::getInitAddonData($reflect->getShortName());
    }

    /**
     * Function called on addon init only if is avaiable
     *
     * @return void
     */
    abstract public function init();

    /**
     * Get main addon file path
     *
     * @return string
     */
    public static function getAddonFile()
    {
        // To prevent the warning about static abstract functions that appears in PHP 5.4/5.6 I use this trick.
        throw new \Exception('this function have to overwritte on child class');
    }

    /**
     * Get main addon folder
     *
     * @return string
     */
    public static function getAddonPath()
    {
        // To prevent the warning about static abstract functions that appears in PHP 5.4/5.6 I use this trick.
        throw new \Exception('this function have to overwritte on child class');
    }

    /**
     * Get slug of current addon
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->addonData['slug'];
    }

    /**
     * True if current addon is avaiable
     *
     * @return boolean
     */
    public function canEnable()
    {
        if (version_compare(PHP_VERSION, $this->addonData['requiresPHP'], '<')) {
            return false;
        }

        if (version_compare(DUPX_VERSION, $this->addonData['requiresDuplcator'], '<')) {
            return false;
        }

        return true;
    }

    /**
     * True if addon has dependencies
     *
     * @return boolean
     */
    public function hasDependencies()
    {
        $avaliableAddons = InstAddonsManager::getInstance()->getAvaiableAddons();
        return !array_diff($this->addonData['requiresAddons'], $avaliableAddons);
    }

    /**
     * Get addon data from header addon file
     *
     * @param string $class addon class
     *
     * @return array
     */
    protected static function getInitAddonData($class)
    {
        $data          = self::getFileFata(static::getAddonFile(), self::getDefaltHeaders());
        $getDefaultVal = self::getDefaultHeadersValues();

        foreach ($data as $key => $val) {
            if (strlen($val) === 0) {
                $data[$key] = $getDefaultVal[$key];
            }
        }

        if (!is_array($data['requiresAddons'])) {
            $data['requiresAddons'] = explode(',', $data['requiresAddons']);
        }
        $data['requiresAddons'] = array_map('trim', $data['requiresAddons']);

        $data['slug'] = $class;
        if (strlen($data['name']) === 0) {
            $data['name'] = $data['slug'];
        }
        return $data;
    }

    /**
     * Retur default addon date headers
     *
     * @return array
     */
    protected static function getDefaultHeadersValues()
    {
        static $defaultHeaders = null;
        if (is_null($defaultHeaders)) {
            $defaultHeaders = array(
                'name'              => '',
                'addonURI'          => '',
                'version'           => '0',
                'description'       => '',
                'author'            => '',
                'authorURI'         => '',
                'requiresWP'        => '4.0',
                'requiresPHP'       => '5.3',
                'requiresDuplcator' => '4.0.2',
                'requiresAddons'    => array()
            );
        }
        return $defaultHeaders;
    }

    /**
     * Return headers list keys
     *
     * @return array
     */
    protected static function getDefaltHeaders()
    {
        return array(
            'name'              => 'Name',
            'addonURI'          => 'Addon URI',
            'version'           => 'Version',
            'description'       => 'Description',
            'author'            => 'Author',
            'authorURI'         => 'Author URI',
            'requiresWP'        => 'Requires WordPress min version',
            'requiresPHP'       => 'Requires PHP',
            'requiresDuplcator' => 'Requires Duplicator min version',
            'requiresAddons'    => 'Requires addons'
        );
    }

    /**
     * Retrieve metadata from a file.
     *
     * Searches for metadata in the first 8 KB of a file, such as a plugin or theme.
     * Each piece of metadata must be on its own line. Fields can not span multiple
     * lines, the value will get cut at the end of the first line.
     *
     * If the file data is not within that first 8 KB, then the author should correct
     * their plugin file and move the data headers to the top.
     *
     * from wordpress get_file_data function
     *
     * @param string $file           Absolute path to the file.
     * @param array  $defaultHeaders List of headers, in the format `array( 'HeaderKey' => 'Header Name' )`.
     *
     * @return array Array of file headers in `HeaderKey => Header Value` format.
     */
    protected static function getFileFata($file, $defaultHeaders)
    {
        // We don't need to write to the file, so just open for reading.
        $fp = fopen($file, 'r');

        // Pull only the first 8 KB of the file in.
        $file_data = fread($fp, 8 * KB_IN_BYTES);

        // PHP will close file handle, but we are good citizens.
        fclose($fp);

        // Make sure we catch CR-only line endings.
        $file_data   = str_replace("\r", "\n", $file_data);
        $all_headers = $defaultHeaders;

        foreach ($all_headers as $field => $regex) {
            if (preg_match('/^[ \t\/*#@]*' . preg_quote($regex, '/') . ':(.*)$/mi', $file_data, $match) && $match[1]) {
                $all_headers[$field] = self::cleanupHeaderComment($match[1]);
            } else {
                $all_headers[$field] = '';
            }
        }

        return $all_headers;
    }

    /**
     * Strip close comment and close php tags from file headers used by WP.
     *
     * From wordpress _cleanup_header_comment
     *
     * @param string $str Header comment to clean up.
     *
     * @return string
     */
    protected static function cleanupHeaderComment($str)
    {
        return trim(preg_replace('/\s*(?:\*\/|\?>).*/', '', $str));
    }
}
