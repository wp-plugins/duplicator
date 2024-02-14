<?php

namespace Duplicator\Installer\Utils\Tests;

interface TestInterface
{
    /**
     * @return bool true on success
     */
    public static function preTestPrepare();

    /**
     * @return bool true on success
     */
    public static function afterTestClean();
}
