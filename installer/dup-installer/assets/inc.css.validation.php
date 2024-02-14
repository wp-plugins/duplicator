<?php

/**
 * validation css
 *
 * Standard: PSR-2
 *
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<style>
    #validate-area .status-badge {
        margin: 1px 2px 0 0;
        line-height: 18px;
        height: 18px;
    }

    #validate-area.show-all .header .status-badge,
    #validate-area.show-warnings .header .status-badge.warn,
    #validate-area.show-warnings .header .status-badge.hwarn,
    #validate-area.show-warnings .header .status-badge.fail {
        display: none;
    }

    #validate-area.show-warnings .test-wrapper.good,
    #validate-area.show-warnings .test-wrapper.pass {
        display: none;
    }

    #validation-result .category-wrapper {
        background-color: #efefef;
        border: 1px solid silver;
        border-radius:2px;
        margin-bottom: 10px;
        overflow: hidden;
    }

    #validation-result .category-wrapper > .header {
        background-color: #E0E0E0;
        color: #000;
        padding: 3px 3px 3px 5px;
        font-weight: bold;
        border-bottom: 1px solid silver;
    }

    #validation-result .category-wrapper > .header .status-badge {
        margin-top: 2px;
    }

    #validation-result .category-title {
        font-size: 14px;
    }

    #validation-result .category-content {
        background-color: #FFF;
    }

    #validation-result .test-title {
        background-color: #efefef; 
        padding: 3px 3px 3px 5px;
        font-size: 13px;
        line-height: 20px;
    }

    #validation-result .test-title:hover {
        background-color: #dfdfdf;
    }

    #validation-result .test-content {
        padding: 10px;
        line-height: 18px;
        font-size: 12px;
    }

    #validation-result .dupx-validation-test-package-size.warn .test-content {
        background-color: #fcf9e8;
    }

    #validation-result .test-content pre {
        overflow: auto;
    }

    #validation-result  .test-content *:first-child {
        margin-top: 0;
    }

    #validation-result  .test-content *:last-child {
        margin-bottom: 0;
    }

    #validation-result .test-content .sub-title {
        border-bottom: 1px solid #d3d3d3;
        font-weight: bold;
        margin: 7px 0 3px 0;
    }

    #validation-result .test-content a {
        color:#485AA3;
    }

    #validation-result .test-content ul {
        padding-left:25px
    }

    #validation-result .test-content ul li {
        padding:2px
    }

    #validation-result .test-content ul.vids {
        list-style-type: none;
    }

    #validation-result .validation-iswritable-failes-objects {
        padding: 10px 0 10px 10px;
        background: #EDEDED;
    }

    #validation-result .validation-iswritable-failes-objects pre {
        min-height: 60px;
        max-height: 400px;
        max-width: 100%;
        overflow: auto;
    }

    #validation-result .test-content .desc-sol {
        padding: 10px;
        display: block;
        background: #efefef;
        margin-top: 10px;
        border-radius: 5px;
    }
</style>