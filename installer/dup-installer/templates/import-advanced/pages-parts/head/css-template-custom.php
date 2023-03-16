<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;

$importSiteInfo = PrmMng::getInstance()->getValue(PrmMng::PARAM_FROM_SITE_IMPORT_INFO);

if (isset($importSiteInfo['color-scheme'])) {
    $colorScheme = $importSiteInfo['color-scheme'];
} else {
    $colorScheme           = array();
    $colorScheme['colors'] = array('#222', '#333', '#0073aa', '#00a0d2');
}
$colorPrimaryButton = isset($importSiteInfo['color-primary-button']) ? $importSiteInfo['color-primary-button'] : $colorScheme->colors[2];
?>
<style>
    body.backend-import {
        background: transparent;
    }

    .backend-import #page-top-messages,
    #ajaxerr-area,
    #progress-area,
    .backend-import #main-content-wrapper {
        max-width: 900px;
    }

    .backend-import #header-main-wrapper .hdr-main {
        max-width: calc(900px - 150px);
    }

    .backend-import #page-top-messages,
    #ajaxerr-area,
    #progress-area {
        padding: 0;
        box-sizing:border-box;
    }

    .backend-import #content {
        border: 0 none;
        margin: 0;
        border-radius: 0;
        box-shadow: none;
        max-width: none;
        width: 100%;
    }

    .backend-import #content-inner {
        margin: 0 20px 40px 0;
    }

    .backend-import .main-form-content {
        min-height: 0;
    }

    .backend-import .sub-header,
    .backend-import #header-main-wrapper .dupx-logfile-link {
        font-size: 12px;
    }

    .backend-import .generic-box,
    .backend-import .hdr-sub1,
    .backend-import .hdr-sub1-area {
        border-radius: 3px;
        border-color: #e5e5e5;
        background: #FFF;
    }

    .backend-import .generic-box .box-title,
    .backend-import .hdr-sub1 {
        font-size: 16px;
        font-weight: bold;
        padding: 8px 12px;
        background: #E0E0E0;
    }

    .backend-import  #validation-result .category-wrapper {
        border-radius:2px;
    }

    .backend-import #validation-result .category-wrapper > .header {
        background: #E0E0E0;
    }

    .backend-import #validation-result .test-title {
        background: #F3F3F3;
    }
    
    .backend-import #validation-result .test-title:hover {
        background: #E0E0E0;
    }

    .backend-import .default-btn {
        background: <?php echo $colorPrimaryButton; ?>;
        border-color: <?php echo $colorPrimaryButton; ?>;
        color: #fff;
        text-decoration: none;
        text-shadow: none;
    }

    .backend-import .default-btn, 
    .backend-import .secondary-btn {
        display: inline-block;
        text-decoration: none;
        font-size: 13px;
        line-height: 32px;
        min-height: 32px;
        margin: 0;
        margin-left: 0px;
        padding: 0 12px;
        cursor: pointer;
        border-width: 1px;
        border-style: solid;
        -webkit-appearance: none;
        border-radius: 3px;
        white-space: nowrap;
        box-sizing: border-box;
    }

    .backend-import .default-btn:hover {
        background: <?php echo $colorScheme['colors'][3]; ?>;
        border-color: <?php echo $colorScheme['colors'][3]; ?>;
        color: #fff;
    }

    .backend-import .default-btn.disabled,
    .backend-import .default-btn:disabled,
    .backend-import .secondary-btn.disabled,
    .backend-import .secondary-btn:disabled  {
        color:silver;         
        background-color: #f3f5f6;
        border: 1px solid silver;
    }

    .backend-import .secondary-btn {
        color: black;         
        background-color: #f3f5f6;
        border: 1px solid #7e8993;
    }

    .backend-import .secondary-btn:hover {
        color: #FEFEFE;         
        background-color: #CFCFCF;
    }

    .backend-import .ui-widget-overlay {
        background: #f1f1f1;
        opacity: .7;
        filter: Alpha(Opacity=70);
    }

</style>