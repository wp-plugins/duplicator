<?php

/**
 * Search and reaplace manager
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\U
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
require_once(DUPX_INIT . '/classes/utilities/template/class.u.template.item.php');
final class DUPX_Template
{
    const TEMPLATE_ADVANCED        = 'default';
    const TEMPLATE_BASE            = 'base';
    const TEMPLATE_IMPORT_BASE     = 'import-base';
    const TEMPLATE_IMPORT_ADVANCED = 'import-advanced';

    /**
     *
     * @var DUPX_Template
     */
    private static $instance = null;
    /**
     *
     * @var DUPX_TemplateItem[]
     */
    private $templates = array();
    /**
     *
     * @var string
     */
    private $currentTemplate = null;
    /**
     *
     * @return DUPX_Template
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        // ADD DEFAULT TEMPLATE
        $this->addTemplate(DUPX_Template::TEMPLATE_ADVANCED, DUPX_INIT . '/templates/default');
        $this->setTemplate(DUPX_Template::TEMPLATE_ADVANCED);
    }

    /**
     *
     * @param string $name
     * @return boolean
     * @throws Exception
     */
    public function setTemplate($name)
    {
        if (!isset($this->templates[$name])) {
            throw new Exception('The template ' . $name . ' doesn\'t exist');
        }

        $this->currentTemplate = $name;
        return true;
    }

    /**
     *
     * @param string $name
     * @param srring $mainFolder
     * @param string $parentName
     * @return boolean
     * @throws Exception
     */
    public function addTemplate($name, $mainFolder, $parentName = null)
    {
        if (isset($this->templates[$name])) {
            throw new Exception('The template "' . $name . '" already exists');
        }

        if (is_null($parentName)) {
            $parent = null;
        } elseif (isset($this->templates[$parentName])) {
            $parent = $this->templates[$parentName];
        } else {
            throw new Exception('The parent template "' . $parentName . '" doesn\'t exist');
        }

        $this->templates[$name] = new DUPX_TemplateItem($name, $mainFolder, $parent);
        return true;
    }

    /**
     *
     * @param string $fileTpl   // template file is a relative path from root template folder
     * @param array $args    // array key / val where key is the var name in template
     * @param bool $echo    // if false return template in string
     *
     * @return string
     */
    public function render($fileTpl, $args = array(), $echo = true)
    {
        return $this->templates[$this->currentTemplate]->render($fileTpl, $args, $echo);
    }

    private function __clone()
    {
    }
}

/**
 *
 * @param string $fileTpl   // template file is a relative path from root template folder
 * @param array $args    // array key / val where key is the var name in template
 * @param bool $echo    // if false return template in string
 *
 * @return string
 */
function dupxTplRender($fileTpl, $args = array(), $echo = true)
{
    static $tplMng = null;
    if (is_null($tplMng)) {
        $tplMng = DUPX_Template::getInstance();
    }

    return $tplMng->render($fileTpl, $args, $echo);
}
