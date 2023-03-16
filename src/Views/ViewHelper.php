<?php

/**
 * @package Duplicator
 */

namespace Duplicator\Views;

use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Core\Views\TplMng;

class ViewHelper
{
    /**
     * Display Duplicator Logo on all pages
     *
     * @return void
     */
    public static function adminLogoHeader()
    {
        if (!ControllersManager::isDuplicatorPage()) {
            return;
        }
        TplMng::getInstance()->render('parts/admin-logo-header');
    }
}
