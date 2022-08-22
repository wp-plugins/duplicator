<?php

/**
 * Validation object
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\U
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Bootstrap;
use Duplicator\Installer\Core\Params\PrmMng;

require_once(DUPX_INIT . '/classes/validation/class.validation.database.service.php');
require_once(DUPX_INIT . '/classes/validation/class.validation.abstract.item.php');
require_once(DUPX_INIT . '/classes/validation/tests/class.validation.test.owrinstall.php');
require_once(DUPX_INIT . '/classes/validation/tests/class.validation.test.addon.sites.php');
require_once(DUPX_INIT . '/classes/validation/tests/class.validation.test.manual.extraction.php');
require_once(DUPX_INIT . '/classes/validation/tests/class.validation.test.dbonly.iswordpress.php');
require_once(DUPX_INIT . '/classes/validation/tests/class.validation.test.package.age.php');
require_once(DUPX_INIT . '/classes/validation/tests/class.validation.test.php.version.php');
require_once(DUPX_INIT . '/classes/validation/tests/class.validation.test.open.basedir.php');
require_once(DUPX_INIT . '/classes/validation/tests/class.validation.test.memory.limit.php');
require_once(DUPX_INIT . '/classes/validation/tests/class.validation.test.php.extensions.php');
require_once(DUPX_INIT . '/classes/validation/tests/class.validation.test.timeout.php');
require_once(DUPX_INIT . '/classes/validation/tests/class.validation.test.wordfence.php');
require_once(DUPX_INIT . '/classes/validation/tests/class.validation.test.disk.space.php');
require_once(DUPX_INIT . '/classes/validation/tests/class.validation.test.importer.version.php');
require_once(DUPX_INIT . '/classes/validation/tests/class.validation.test.importable.php');
require_once(DUPX_INIT . '/classes/validation/tests/class.validation.test.rest.api.php');
require_once(DUPX_INIT . '/classes/validation/tests/class.validation.test.managed.tprefix.php');
require_once(DUPX_INIT . '/classes/validation/tests/class.validation.test.iswritable.php');
require_once(DUPX_INIT . '/classes/validation/tests/class.validation.test.iswritable.configs.php');
require_once(DUPX_INIT . '/classes/validation/tests/class.validation.test.mysql.connect.php');
require_once(DUPX_INIT . '/classes/validation/tests/class.validation.test.tokenizer.php');
require_once(DUPX_INIT . '/classes/validation/tests/class.validation.test.replace.paths.php');
require_once(DUPX_INIT . '/classes/validation/tests/class.validation.test.managed.supported.php');
require_once(DUPX_INIT . '/classes/validation/tests/class.validation.test.siteground.php');
require_once(DUPX_INIT . '/classes/validation/tests/class.validation.test.archive.check.php');
require_once(DUPX_INIT . '/classes/validation/tests/class.validation.test.recovery.link.php');
require_once(DUPX_INIT . '/classes/validation/database-tests/class.validation.test.db.host.name.php');
require_once(DUPX_INIT . '/classes/validation/database-tests/class.validation.test.db.connection.php');
require_once(DUPX_INIT . '/classes/validation/database-tests/class.validation.test.db.version.php');
require_once(DUPX_INIT . '/classes/validation/database-tests/class.validation.test.db.create.php');
require_once(DUPX_INIT . '/classes/validation/database-tests/class.validation.test.db.cleanup.php');
require_once(DUPX_INIT . '/classes/validation/database-tests/class.validation.test.db.affected.tables.php');
require_once(DUPX_INIT . '/classes/validation/database-tests/class.validation.test.db.prefix.too.long.php');
require_once(DUPX_INIT . '/classes/validation/database-tests/class.validation.test.db.visibility.php');
require_once(DUPX_INIT . '/classes/validation/database-tests/class.validation.test.db.manual.tables.count.php');
require_once(DUPX_INIT . '/classes/validation/database-tests/class.validation.test.db.multiple.wp.installs.php');
require_once(DUPX_INIT . '/classes/validation/database-tests/class.validation.test.db.user.perms.php');
require_once(DUPX_INIT . '/classes/validation/database-tests/class.validation.test.db.user.resources.php');
require_once(DUPX_INIT . '/classes/validation/database-tests/class.validation.test.db.triggers.php');
require_once(DUPX_INIT . '/classes/validation/database-tests/class.validation.test.db.show.variables.php');
require_once(DUPX_INIT . '/classes/validation/database-tests/class.validation.test.db.supported.charset.php');
require_once(DUPX_INIT . '/classes/validation/database-tests/class.validation.test.db.supported.engine.php');
require_once(DUPX_INIT . '/classes/validation/database-tests/class.validation.test.db.gtid.mode.php');
require_once(DUPX_INIT . '/classes/validation/database-tests/class.validation.test.db.case.sentivie.tables.php');
require_once(DUPX_INIT . '/classes/validation/database-tests/class.validation.test.db.supported.default.charset.php');

class DUPX_Validation_manager
{
    const CAT_GENERAL            = 'general';
    const CAT_FILESYSTEM         = 'filesystem';
    const CAT_PHP                = 'php';
    const CAT_DATABASE           = 'database';
    const ACTION_ON_START_NORMAL = 'normal';
    const ACTION_ON_START_AUTO   = 'auto';
    const MIN_LEVEL_VALID        = 1; // DUPX_Validation_abstract_item::LV_HARD_WARNING, can't assign directly in php 5.3

    /**
     *
     * @var self
     */
    private static $instance = null;

    /**
     *
     * @var DUPX_Validation_abstract_item[]
     */
    private $tests = array();

    /**
     *
     * @var array
     */
    private $extraData = array();

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

    private function __construct()
    {
        /** GENERAL * */
        $this->tests[] = new DUPX_Validation_test_archive_check(self::CAT_GENERAL);
        $this->tests[] = new DUPX_Validation_test_importer_version(self::CAT_GENERAL);
        $this->tests[] = new DUPX_Validation_test_owrinstall(self::CAT_GENERAL);
        $this->tests[] = new DUPX_Validation_test_recovery(self::CAT_GENERAL);
        $this->tests[] = new DUPX_Validation_test_importable(self::CAT_GENERAL);
        $this->tests[] = new DUPX_Validation_test_rest_api(self::CAT_GENERAL);
        $this->tests[] = new DUPX_Validation_test_manual_extraction(self::CAT_GENERAL);
        $this->tests[] = new DUPX_Validation_test_dbonly_iswordpress(self::CAT_GENERAL);
        $this->tests[] = new DUPX_Validation_test_package_age(self::CAT_GENERAL);
        $this->tests[] = new DUPX_Validation_test_replace_paths(self::CAT_GENERAL);
        $this->tests[] = new DUPX_Validation_test_managed_supported(self::CAT_GENERAL);
        $this->tests[] = new DUPX_Validation_test_siteground(self::CAT_GENERAL);
        $this->tests[] = new DUPX_Validation_test_addon_sites(self::CAT_GENERAL);
        $this->tests[] = new DUPX_Validation_test_wordfence(self::CAT_GENERAL);
        $this->tests[] = new DUPX_Validation_test_managed_tprefix(self::CAT_GENERAL);

        /** PHP * */
        $this->tests[] = new DUPX_Validation_test_php_version(self::CAT_PHP);
        $this->tests[] = new DUPX_Validation_test_open_basedir(self::CAT_PHP);
        $this->tests[] = new DUPX_Validation_test_memory_limit(self::CAT_PHP);
        $this->tests[] = new DUPX_Validation_test_extensions(self::CAT_PHP);
        $this->tests[] = new DUPX_Validation_test_mysql_connect(self::CAT_PHP);
        $this->tests[] = new DUPX_Validation_test_tokenizer(self::CAT_PHP);
        $this->tests[] = new DUPX_Validation_test_timeout(self::CAT_PHP);

        /** FILESYSTEM * */
        $this->tests[] = new DUPX_Validation_test_disk_space(self::CAT_FILESYSTEM);
        $this->tests[] = new DUPX_Validation_test_iswritable(self::CAT_FILESYSTEM);
        $this->tests[] = new DUPX_Validation_test_iswritable_configs(self::CAT_FILESYSTEM);

        /** DATABASE * */
        $this->tests[] = new DUPX_Validation_test_db_host_name(self::CAT_DATABASE);
        $this->tests[] = new DUPX_Validation_test_db_connection(self::CAT_DATABASE);
        $this->tests[] = new DUPX_Validation_test_db_version(self::CAT_DATABASE);
        $this->tests[] = new DUPX_Validation_test_db_create(self::CAT_DATABASE);
        $this->tests[] = new DUPX_Validation_test_db_supported_engine(self::CAT_DATABASE);
        $this->tests[] = new DUPX_Validation_test_db_gtid_mode(self::CAT_DATABASE);
        $this->tests[] = new DUPX_Validation_test_db_visibility(self::CAT_DATABASE);
        $this->tests[] = new DUPX_Validation_test_db_manual_tabels_count(self::CAT_DATABASE);
        $this->tests[] = new DUPX_Validation_test_db_multiple_wp_installs(self::CAT_DATABASE);
        $this->tests[] = new DUPX_Validation_test_db_user_resources(self::CAT_DATABASE);
        $this->tests[] = new DUPX_Validation_test_db_user_perms(self::CAT_DATABASE);
        $this->tests[] = new DUPX_Validation_test_db_custom_queries(self::CAT_DATABASE);
        $this->tests[] = new DUPX_Validation_test_db_triggers(self::CAT_DATABASE);
        $this->tests[] = new DUPX_Validation_test_db_supported_default_charset(self::CAT_DATABASE);
        $this->tests[] = new DUPX_Validation_test_db_supported_charset(self::CAT_DATABASE);
        $this->tests[] = new DUPX_Validation_test_db_case_sensitive_tables(self::CAT_DATABASE);
        $this->tests[] = new DUPX_Validation_test_db_affected_tables(self::CAT_DATABASE);
        $this->tests[] = new DUPX_Validation_test_db_prefix_too_long(self::CAT_DATABASE);
        // after all database tests
        $this->tests[] = new DUPX_Validation_test_db_cleanup(self::CAT_DATABASE);
    }

    /**
     *
     * @return boolean
     */
    public static function isValidated()
    {
        $paramsManager = PrmMng::getInstance();
        return $paramsManager->getValue(PrmMng::PARAM_VALIDATION_LEVEL) >= self::MIN_LEVEL_VALID;
    }

    /**
     *
     * @return boolean
     */
    public static function isFirstValidationOnLoad()
    {
        return (
            Bootstrap::isInit() &&
            PrmMng::getInstance()->getValue(PrmMng::PARAM_VALIDATION_ACTION_ON_START) === DUPX_Validation_manager::ACTION_ON_START_AUTO
        );
    }

    /**
     *
     * @return boolean
     */
    public static function validateOnLoad()
    {
        $paramsManager = PrmMng::getInstance();
        if ($paramsManager->getValue(PrmMng::PARAM_VALIDATION_ACTION_ON_START) === DUPX_Validation_manager::ACTION_ON_START_AUTO) {
            return true;
        }
        if ($paramsManager->getValue(PrmMng::PARAM_STEP_ACTION) === DUPX_CTRL::ACTION_STEP_ON_VALIDATE) {
            return true;
        }
        return false;
    }

    public function getValidateData()
    {
        $this->runTests();
        $mainResult = $this->getMainResult();

        $paramsManager = PrmMng::getInstance();
        $paramsManager->setValue(PrmMng::PARAM_VALIDATION_LEVEL, $mainResult);
        $paramsManager->save();

        return array(
            'mainLevel'        => $mainResult,
            'mainBagedClass'   => DUPX_Validation_abstract_item::resultLevelToBadgeClass($mainResult),
            'mainText'         => DUPX_Validation_abstract_item::resultLevelToString($mainResult),
            'categoriesLevels' => array(
                'database'   => $this->getCagegoryResult(self::CAT_DATABASE),
                'php'        => $this->getCagegoryResult(self::CAT_PHP),
                'general'    => $this->getCagegoryResult(self::CAT_GENERAL),
                'filesystem' => $this->getCagegoryResult(self::CAT_FILESYSTEM)
            ),
            'htmlResult'       => DUPX_CTRL::renderPostProcessings($this->getValidationHtmlResult()),
            'extraData'        => $this->extraData
        );
    }

    protected function runTests()
    {
        $this->extraData = array();

        foreach ($this->tests as $test) {
            $test->test(true);
        }
    }

    protected function getValidationHtmlResult()
    {
        return dupxTplRender('parts/validation/validation-result', array(
            'validationManager' => $this
            ), false);
    }

    /**
     *
     * @param string $key
     * @param mixed $value
     */
    public function addExtraData($key, $value)
    {
        $this->extraData[$key] = $value;
    }

    public function getTestsCategory($category)
    {
        $result = array();
        foreach ($this->tests as $test) {
            if ($test->getCategory() === $category) {
                $result[] = $test;
            }
        }
        return $result;
    }

    public function getCagegoryResult($category)
    {
        $result = PHP_INT_MAX;
        foreach ($this->tests as $test) {
            if ($test->getCategory() === $category && $test->test() < $result) {
                $result = $test->test();
            }
        }
        if ($result === DUPX_Validation_abstract_item::LV_GOOD) {
            $result = DUPX_Validation_abstract_item::LV_PASS;
        }
        return $result;
    }

    public function getCagegoryBadge($category)
    {
        return DUPX_Validation_abstract_item::resultLevelToBadgeClass($this->getCagegoryResult($category));
    }

    public function getMainResult()
    {
        $result = PHP_INT_MAX;
        foreach ($this->tests as $test) {
            if ($test->test() < $result) {
                $result = $test->test();
            }
        }
        if ($result === DUPX_Validation_abstract_item::LV_GOOD) {
            $result = DUPX_Validation_abstract_item::LV_PASS;
        }
        return $result;
    }
}
