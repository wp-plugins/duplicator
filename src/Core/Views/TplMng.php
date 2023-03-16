<?php

/**
 * Template view manager
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Core\Views;

use Duplicator\Libs\Snap\SnapJson;

final class TplMng
{
    /** @var ?self */
    private static $instance = null;
    /** @var string */
    private $mainFolder = '';
    /** @var bool */
    private static $stripSpaces = false;
    /** @var mixed[] */
    private $globalData = array();

    /**
     *
     * @return self
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Class constructor
     */
    private function __construct()
    {
        $this->mainFolder = DUPLICATOR_PLUGIN_PATH . '/template/';
    }

    /**
     * If strip spaces is true in render method spaced between tag are removed
     *
     * @param bool $strip if true strip spaces
     *
     * @return void
     */
    public static function setStripSpaces($strip)
    {
        self::$stripSpaces = (bool) $strip;
    }

    /**
     * Set template global value in template data
     *
     * @param string $key global value key
     * @param mixed  $val value
     *
     * @return void
     */
    public function setGlobalValue($key, $val)
    {
        $this->globalData[$key] = $val;
    }

    /**
     * Remove global value if exist
     *
     * @param string $key gloval value key
     *
     * @return void
     */
    public function unsetGlobalValue($key)
    {
        if (isset($this->globalData[$key])) {
            unset($this->globalData[$key]);
        }
    }

    /**
     * Return true if global values exists
     *
     * @param string $key gloval value key
     *
     * @return bool
     */
    public function hasGlobalValue($key)
    {
        return isset($this->globalData[$key]);
    }

    /**
     * Multiple global data set
     *
     * @param array<string, mixed> $data data tu set in global data
     *
     * @return void
     */
    public function updateGlobalData(array $data = array())
    {
        $this->globalData = array_merge($this->globalData, (array) $data);
    }

    /**
     * Return global data
     *
     * @return array<string, mixed>
     */
    public function getGlobalData()
    {
        return $this->globalData;
    }

    /**
     * Render template
     *
     * @param string               $slugTpl template file is a relative path from root template folder
     * @param array<string, mixed> $args    array key / val where key is the var name in template
     * @param bool                 $echo    if false return template in string
     *
     * @return string
     */
    public function render($slugTpl, $args = array(), $echo = true)
    {
        ob_start();
        if (($renderFile = $this->getFileTemplate($slugTpl)) !== false) {
            $tplData = apply_filters(self::getDataHook($slugTpl), array_merge($this->globalData, $args));
            $tplMng  = $this;
            require($renderFile);
        } else {
            echo '<p>FILE TPL NOT FOUND: ' . $slugTpl . '</p>';
        }
        $renderResult = apply_filters(self::getRenderHook($slugTpl), ob_get_clean());

        if (self::$stripSpaces) {
            $renderResult = preg_replace('~>[\n\s]+<~', '><', $renderResult);
        }
        if ($echo) {
            echo $renderResult;
            return '';
        } else {
            return $renderResult;
        }
    }

    /**
     * Render template in json string
     *
     * @param string               $slugTpl template file is a relative path from root template folder
     * @param array<string, mixed> $args    array key / val where key is the var name in template
     * @param bool                 $echo    if false return template in string
     *
     * @return string
     */
    public function renderJson($slugTpl, $args = array(), $echo = true)
    {
        $renderResult = SnapJson::jsonEncode($this->render($slugTpl, $args, false));
        if ($echo) {
            echo $renderResult;
            return '';
        } else {
            return $renderResult;
        }
    }

    /**
     * Render template apply esc attr
     *
     * @param string               $slugTpl template file is a relative path from root template folder
     * @param array<string, mixed> $args    array key / val where key is the var name in template
     * @param bool                 $echo    if false return template in string
     *
     * @return string
     */
    public function renderEscAttr($slugTpl, $args = array(), $echo = true)
    {
        $renderResult = esc_attr($this->render($slugTpl, $args, false));
        if ($echo) {
            echo $renderResult;
            return '';
        } else {
            return $renderResult;
        }
    }

    /**
     * Get hook unique from template slug
     *
     * @param string $slugTpl template slug
     *
     * @return string
     */
    public static function tplFileToHookSlug($slugTpl)
    {
        return str_replace(array('\\', '/', '.'), '_', $slugTpl);
    }

    /**
     * Return data hook from template slug
     *
     * @param string $slugTpl template slug
     *
     * @return string
     */
    public static function getDataHook($slugTpl)
    {
        return 'duplicator_template_data_' . self::tplFileToHookSlug($slugTpl);
    }

    /**
     * Return render hook from template slug
     *
     * @param string $slugTpl template slug
     *
     * @return string
     */
    public static function getRenderHook($slugTpl)
    {
        return 'duplicator_template_render_' . self::tplFileToHookSlug($slugTpl);
    }

    /**
     * Acctept html of php extensions. if the file have unknown extension automatic add the php extension
     *
     * @param string $slugTpl template slug
     *
     * @return boolean|string return false if don\'t find the template file
     */
    protected function getFileTemplate($slugTpl)
    {
        $fullPath = $this->mainFolder . $slugTpl . '.php';

        if (file_exists($fullPath)) {
            return $fullPath;
        } else {
            return false;
        }
    }
}
