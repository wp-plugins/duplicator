<?php

namespace Duplicator\Installer\Core\Deploy\Database;

use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Installer\Utils\Log\Log;
use Duplicator\Libs\Snap\JsonSerialize\AbstractJsonSerializable;
use DUPX_ArchiveConfig;
use DUPX_DB_Functions;
use DUPX_DB_Tables;
use DUPX_DBInstall;
use DUPX_InstallerState;
use Exception;

class QueryFixes extends AbstractJsonSerializable
{
    const TEMP_POSTFIX                 = 't_';
    const USER_DEFINER_REPLACE_PATTERN = "/^(\s*(?:\/\*!\d+\s)?\s*(?:CREATE.+)?DEFINER\s*=)([^\*\s]+)(.*)$/m";
    const USER_DEFINER_REMOVE_PATTERN  = "/^(\s*(?:\/\*!\d+\s)?\s*(?:CREATE.+)?)(DEFINER\s*=\s*\S+)(.*)$/m";
    const USER_DEFINER_REMOVE_REPLACE  = '$1 $3';
    const SQL_SECURITY_INVOKER_PATTERN = "/^(\s*CREATE.+(?:PROCEDURE|FUNCTION)[\s\S]*)(BEGIN)([\s\S]*)$/";
    const SQL_SECURITY_INVOKER_REPLACE = "$1SQL SECURITY INVOKER\n$2$3";

    /** @var array */
    protected $globalRules = array(
        'search'  => array(),
        'replace' => array()
    );
    /** @var array */
    protected $tablesPrefixRules = array();
    /** @var string */
    protected $generatorLog = '';

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->rulesProcAndViews();
        $this->rulesMySQLEngine();
        $this->legacyCharsetAndCollation();
        $this->rulesTableNames();
    }

    /**
     * Filter props on json encode
     *
     * @return strng[]
     */
    public function __sleep()
    {
        $props = array_keys(get_object_vars($this));
        return array_diff($props, array('generatorLog'));
    }

    /**
     * Write rules in log
     *
     * @return void
     */
    public function logRules()
    {
        if (strlen($this->generatorLog) == 0) {
            Log::info('NO GENERAL QUERY FIXES');
        } else {
            Log::info('QUERY FIXES');
            Log::info($this->generatorLog);
        }

        if (count($this->globalRules['search']) > 0) {
            Log::info('QUERY FIXES GLOBAL RULES');
            Log::incIndent();
            foreach ($this->globalRules['search'] as $index => $search) {
                Log::info('SEARCH  => ' . $search);
                Log::info('REPLACE => ' . $this->globalRules['replace'][$index] . "\n");
            }
            Log::resetIndent();
        }

        if (count($this->tablesPrefixRules) > 0) {
            Log::info('QUERY FIXES TABLES RULES');
            Log::incIndent();
            foreach ($this->tablesPrefixRules as $indexRulesSet => $ruleSet) {
                Log::info('RULESET ' . ($indexRulesSet + 1));
                Log::incIndent();
                foreach ($ruleSet['search'] as $index => $search) {
                    Log::info('SEARCH  => ' . $search);
                    Log::info('REPLACE => ' . $ruleSet['replace'][$index] . "\n");
                }
                Log::decIndent();
            }
            Log::resetIndent();
        }
    }

    /**
     * @param string $query query to fix
     *
     * @return string The query with appropriate substitutions done
     */
    public function applyFixes($query)
    {
        $query = preg_replace($this->globalRules['search'], $this->globalRules['replace'], $query);

        foreach ($this->tablesPrefixRules as $ruleSet) {
            $query = preg_replace($ruleSet['search'], $ruleSet['replace'], $query);
        }
        return $query;
    }

    /**
     * return search and replace rules
     *
     * @return array
     * @throws Exception
     */
    protected function rulesProcAndViews()
    {
        if (DUPX_InstallerState::isRestoreBackup()) {
            return;
        }

        if (PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_REMOVE_DEFINER)) {
            $this->globalRules['search'][]  = self::USER_DEFINER_REMOVE_PATTERN;
            $this->globalRules['replace'][] = self::USER_DEFINER_REMOVE_REPLACE;
        } else {
            $this->globalRules['search'][] = self::USER_DEFINER_REPLACE_PATTERN;

            $dbHost = PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_HOST);
            $dbUser = PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_USER);

            $definerHost                    = (($dbHost == "localhost" || $dbHost == "127.0.0.1") ? $dbHost : '%');
            $this->globalRules['replace'][] = '$1' . addcslashes("`" . $dbUser . "`@`" . $definerHost . "`", '\\$') . '$3';
        }

        $this->globalRules['search'][]  = self::SQL_SECURITY_INVOKER_PATTERN;
        $this->globalRules['replace'][] = self::SQL_SECURITY_INVOKER_REPLACE;

        $this->generatorLog .= "GLOBAL RULES ADDED: PROC AND VIEWS\n";
    }

    /**
     * Check invalid SQL engines
     *
     * @return void
     */
    protected function rulesMySQLEngine()
    {
        $invalidEngines = array_map(function ($engine) {
            return preg_quote($engine, '/');
        }, DUPX_ArchiveConfig::getInstance()->invalidEngines());

        if (empty($invalidEngines)) {
            return;
        }
        $this->globalRules['search'][]  = '/^(\s*(?:\/\*!\d+\s)?\s*CREATE.+ENGINE=)(' . implode('|', $invalidEngines) . ')(.*)$/ms';
        $this->globalRules['replace'][] = '$1' . DUPX_DB_Functions::getInstance()->getDefaultEngine() . '$3';

        $this->generatorLog .= "GLOBAL RULES ADDED: MYSQL ENGINES\n";
    }

    /**
     * regex managed examples
     *  - `meta_value` longtext CHARACTER SET utf16 COLLATE utf16_slovak_ci DEFAULT NULL,
     *  - `comment_author` tinytext COLLATE utf8mb4_unicode_ci NOT NULL,
     *  - ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci_test;
     *  - ) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4;
     *
     * accept ['"`]charset['"`]
     *
     * @return boolean|array
     */
    public function legacyCharsetAndCollation()
    {
        $invalidCharsets   = DUPX_ArchiveConfig::getInstance()->invalidCharsets();
        $invalidCollations = DUPX_ArchiveConfig::getInstance()->invalidCollations();
        $defCharsetRegex   = addcslashes(DUPX_DB_Functions::getInstance()->getRealCharsetByParam(), '\\$');
        $defCollateRegex   = addcslashes(DUPX_DB_Functions::getInstance()->getRealCollateByParam(), '\\$');

        if (count($invalidCharsets) > 0) {
            $invalidChrRegex = '(?:' . implode('|', array_map(function ($val) {
                        return preg_quote($val, '/');
            }, $invalidCharsets)) . ')';

            $this->globalRules['search'][]  = '/(^.*(?:CHARSET|CHARACTER SET)\s*[\s=]\s*[`\'"]?)(' .
                $invalidChrRegex . ')([`\'"]?\s.*COLLATE\s*[\s=]\s*[`\'"]?)([^`\'"\s;]+)([`\'"]?.*$)/m';
            $this->globalRules['replace'][] = '$1' . $defCharsetRegex . '$3' . $defCollateRegex . '$5';
            $this->globalRules['search'][]  = '/(^.*COLLATE\s*[\s=]\s*[`\'"]?)([^`\'"\s;]+)([`\'"]?\s.*(?:CHARSET|CHARACTER SET)\s*[\s=]\s*[`\'"]?)(' .
                $invalidChrRegex . ')([`\'"]?[\s;].*$)/m';
            $this->globalRules['replace'][] = '$1' . $defCollateRegex . '$3' . $defCharsetRegex . '$5';
            $this->globalRules['search'][]  = '/(^.*(?:CHARSET|CHARACTER SET)\s*[\s=]\s*[`\'"]?)(' . $invalidChrRegex . ')([`\'"]?[\s;].*$)/m';
            $this->globalRules['replace'][] = '$1' . $defCharsetRegex . '$3';

            $this->generatorLog .= "GLOBAL RULES ADDED: INVALID CHARSETS\n";
        }

        if (count($invalidCollations) > 0) {
            $invalidColRegex = '(?:' . implode('|', array_map(function ($val) {
                        return preg_quote($val, '/');
            }, $invalidCollations)) . ')';

            $this->globalRules['search'][]  = '/(^.*(?:CHARSET|CHARACTER SET)\s*[\s=]\s*[`\'"]?)([^`\'"\s;]+)([`\'"]?\s.*COLLATE\s*[\s=]\s*[`\'"]?)(' .
                $invalidColRegex . ')([`\'"]?[\s;].*$)/m';
            $this->globalRules['replace'][] = '$1' . $defCharsetRegex . '$3' . $defCollateRegex . '$5';
            $this->globalRules['search'][]  = '/(^.*COLLATE\s*[\s=]\s*[`\'"]?)(' .
                $invalidColRegex . ')([`\'"]?\s.*(?:CHARSET|CHARACTER SET)\s*[\s=]\s*[`\'"]?)([^`\'"\s;]+)([`\'"]?.*$)/m';
            $this->globalRules['replace'][] = '$1' . $defCollateRegex . '$3' . $defCharsetRegex . '$5';
            $this->globalRules['search'][]  = '/(^.*COLLATE\s*[\s=]\s*[`\'"]?)(' . $invalidColRegex . ')([`\'"]?[\s;].*$)/m';
            $this->globalRules['replace'][] = '$1' . $defCollateRegex . '$3';

            $this->generatorLog .= "GLOBAL RULES ADDED: INVALID COLLATIONS\n";
        }
    }

    /**
     * return search and replace table prefix rules
     *
     * @return array
     */
    protected function rulesTableNames()
    {
        $mapping = DUPX_DB_Tables::getInstance()->getRenameTablesMapping();

        $oldPrefixes = array_keys($mapping);
        $newPrefixes = array();
        foreach ($mapping as $oldPrefix => $newMapping) {
            $newPrefixes = array_merge($newPrefixes, array_keys($newMapping));
        }
        $newPrefixes = array_unique($newPrefixes);

        // Prevent double transformation with temp prefix
        $doublePrefixes = array_intersect($oldPrefixes, $newPrefixes);
        if (count($doublePrefixes) > 0) {
            $this->generatorLog .= 'DOUBLE PREFIXES ' . Log::v2str($doublePrefixes);
        }

        foreach ($mapping as $oldPrefix => $newMapping) {
            $rulesSet = array(
                'search'  => array(),
                'replace' => array()
            );

            $quoteOldPrefix = preg_quote($oldPrefix, '/');

            foreach ($newMapping as $newPrefix => $commons) {
                $this->generatorLog .= "TABLES RULES ADDED: CHANGE TABLES PREFIX " . $oldPrefix . " TO " . $newPrefix ;
                if (in_array($newPrefix, $doublePrefixes)) {
                    $this->generatorLog .= " [USE TMP PREFIX]\n";
                    $newPrefix           = $newPrefix . self::TEMP_POSTFIX;
                } else {
                    $this->generatorLog .= "\n";
                }
                $this->generatorLog .= "\tFOR TABLES " . implode(',', $commons) . "\n";

                $quoteNewPrefix = addcslashes($newPrefix, '\\$');
                $quoteCommons   = array_map(
                    function ($val) {
                        return preg_quote($val, '/');
                    },
                    $commons
                );

                for ($i = 0; $i < ceil(count($quoteCommons) / DUPX_DBInstall::TABLES_REGEX_CHUNK_SIZE); $i++) {
                    $subArray = array_slice($quoteCommons, $i * DUPX_DBInstall::TABLES_REGEX_CHUNK_SIZE, DUPX_DBInstall::TABLES_REGEX_CHUNK_SIZE);

                    if (count($subArray) == 0) {
                        break;
                    }

                    $rulesSet['search'][]  = '/' . $quoteOldPrefix . '(' . implode('|', $subArray) . ')/m';
                    $rulesSet['replace'][] = $quoteNewPrefix . '$1';
                }

                $rulesSet['search'][]  = '/(CONSTRAINT[\s\t]+[`\'"]?.+)(?-i)' . $quoteOldPrefix . '(?i)(.+[`\'"]?[\s\t]+FOREIGN[\s\t]+KEY)/mi';
                $rulesSet['replace'][] = '$1' . $quoteNewPrefix . '$2';
            }

            if (count($rulesSet['search']) > 0) {
                $this->tablesPrefixRules[] = $rulesSet;
            }
        }

        if (count($doublePrefixes)) {
            // REMOVE TEMP PREFIXES
            $rulesSet = array(
                'search'  => array(),
                'replace' => array()
            );

            foreach ($doublePrefixes as $prefix) {
                $quoteTempPrefix = preg_quote($prefix . self::TEMP_POSTFIX, '/');
                $quotePrefix     = addcslashes($prefix, '\\$');

                $rulesSet['search'][]  = '/' . $quoteTempPrefix . '/m';
                $rulesSet['replace'][] = $quotePrefix . '$1';
            }

            $this->tablesPrefixRules[] = $rulesSet;
        }
    }
}
