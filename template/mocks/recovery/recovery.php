<?php

use Duplicator\Core\Views\TplMng;
use Duplicator\Libs\Upsell;

defined("ABSPATH") || exit;

/**
 * Variables
 *
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 */
?>
<div class="mock-blur" aria-hidden="true">
    <h2 class="margin-bottom-0"><i class="fas fa-undo-alt"></i> Recovery Point</h2>
    <hr>

    <p class="margin-bottom-1">
        Quickly restore this site to a specific point in time. <span class="link-style dup-pro-open-help-link">Need more help?</span>
    </p>
    <div class="dup-pro-recovery-details-max-width-wrapper">
        <form id="dpro-recovery-form" method="post">
            <div class="dup-pro-recovery-widget-wrapper">
                <div class="dup-pro-recovery-point-details margin-bottom-1">
                    <div class="dup-pro-recovery-active-link-wrapper">
                        <div class="dup-pro-recovery-active-link-header">
                            <i class="fas fa-undo-alt main-icon"></i>
                            <div class="main-title">
                                Recovery point is active <i class="fas fa-question-circle fa-sm"></i>
                            </div>
                            <div class="main-subtitle margin-bottom-1">
                                <b>Status:</b>&nbsp;
                                <span class="dup-pro-recovery-status green">ready</span>
                            </div>
                        </div>
                        <div class="dup-pro-recovery-package-info margin-bottom-1">
                            <table>
                                <tbody>
                                <tr>
                                    <td>Name:</td>
                                    <td><b>20230101_duplicatorrecovery</b></td>
                                </tr>
                                <tr>
                                    <td>Date:</td>
                                    <td><b>2023-01-01 00:00:00</b></td>
                                </tr>
                                <tr>
                                    <td>Age:</td>
                                    <td>
                                        <b>Created 0 hours ago.</b>&nbsp;
                                        <i>All changes made after package creation will be lost.</i>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="dup-pro-recovery-point-selector">
                    <div class="dup-pro-recovery-point-selector-area-wrapper">
                <span class="dup-pro-opening-packages-windows">
                    <a href="#">[Create New]</a>
                </span>
                        <label>
                            <i class="fas fa-question-circle fa-sm">
                            </i>
                            <b>Step 1 :</b> <i>Choose Recovery Point Archive</i>
                        </label>
                        <div class="dup-pro-recovery-point-selector-area">
                            <select class="recovery-select" name="recovery_package">
                                <option value=""> -- Not selected --</option>
                                <optgroup label="2023/01/01">
                                    <option selected="selected">
                                        [2023-01-01 00:00:00] 20230101_duplicatorrecovery
                                    </option>
                                </optgroup>
                            </select>
                            <button type="button" class="button recovery-reset">Reset</button>
                            <button type="button" class="button button-primary recovery-set">Set</button>
                        </div>
                    </div>
                </div>
                <div class="dup-pro-recovery-point-actions">
                    <label>
                        <i class="fas fa-question-circle fa-sm">
                        </i>
                        <b>Step 2 :</b> <i>Copy Recovery URL &amp; Store in Safe Place</i>
                    </label>
                    <div class="copy-link">
                        <div class="content">
                            http://duplicator.com//recover/20230101_duplicatorrecovery_1c38a5948c5a3d2b5881_20230205120338_installer-backup.php
                        </div>
                        <i class="far fa-copy copy-icon"></i>
                    </div>
                    <div class="dup-pro-recovery-buttons">
                        <a href="#"
                           class="button button-primary dup-pro-launch " target="_blank">
                            <i class="fas fa-external-link-alt"></i>&nbsp;&nbsp;Launch Recovery </a>
                        <button type="button" class="button button-primary dup-pro-recovery-download-launcher ">
                            <i class="fa fa-rocket"></i>&nbsp;&nbsp;Download
                        </button>
                        <button type="button" class="button button-primary dup-pro-recovery-copy-url">
                            <i class="far fa-copy copy-icon"></i>&nbsp;&nbsp;Copy URL
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php TplMng::getInstance()->render(
    'parts/Education/static-popup',
    array(
        'title'        => __('Rollback your sites with Recovery Points!', 'duplicator'),
        'warning-text' => __('Recovery Points are not supported in Duplicator Lite!', 'duplicator'),
        'content-tpl'  => 'mocks/recovery/content-popup',
        'upsell-url'   => Upsell::getCampaignUrl('blurred-mocks', 'Recovery')
    ),
    true
); ?>