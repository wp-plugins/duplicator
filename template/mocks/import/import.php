<?php

use Duplicator\Core\Views\TplMng;
use Duplicator\Utils\Upsell;

defined("ABSPATH") || exit;

/**
 * Variables
 *
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 */
?>
<div class="wrap">
    <h1 class="margin-bottom-2">
        Import
    </h1>
    <div class="dup-pro-tab-content-wrapper">
        <div id="dup-pro-import-phase-one">
            <div class="dup-pro-import-header">
                <h2 class="title">
                    <i class="fas fa-arrow-alt-circle-down">
                    </i> Step<span class="red">1</span> of 2: Upload Archive
                </h2>
                <hr>
            </div>
            <!-- ==============================
      DRAG/DROP AREA -->
            <div id="dup-pro-import-upload-tabs-wrapper" class="dup-pro-tabs-wrapper margin-bottom-2 mock-blur" aria-hidden="true" style="position: relative;">
                <div id="dup-pro-import-mode-tab-header" class="clearfix margin-bottom-2">
                    <div id="dup-pro-import-mode-upload-tab" class="active">
                        <i class="far fa-file-archive"></i> Import File <sup>&nbsp;</sup>
                    </div>
                    <div id="dup-pro-import-mode-remote-tab">
                        <i class="fas fa-link"></i> Import Link
                    </div>
                </div>
                <div id="dup-pro-import-upload-file-tab" class="tab-content ">
                    <div id="dup-pro-import-upload-file" class="dup-pro-import-upload-box fs-upload-element fs-upload fs-light">
                        <div class="fs-upload-target">
                            <div id="dup-pro-import-upload-file-content" class="center-xy">
                                <i class="fa fa-download fa-2x">
                                </i>
                                <span class="dup-drag-drop-message">
                                  Drag &amp; Drop Archive File Here
                                </span>
                                <input 
                                    id="dup-import-dd-btn" 
                                    type="button" 
                                    class="button button-large button-default dup-import-button" 
                                    name="dpro-files" value="Select File..."
                                >
                            </div>
                        </div>
                        <input class="fs-upload-input" type="file">
                    </div>
                    <div id="dup-pro-import-upload-file-footer">
                        <i class="fas fa-question-circle fa-sm"></i>&nbsp;
                        <b>Chunk Size:</b> 1 MB &nbsp;|&nbsp;
                        <b>Max Size:
                        </b> No Limit&nbsp;|&nbsp;
                        <span class="pointer link-style">
                          <i>Slow Upload</i>&nbsp;
                          <i class="fas fa-question-circle fa-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div id="dpro-pro-import-available-packages" class="view-list-item mock-blur" aria-hidden="true">
            <table class="dup-import-avail-packs packages-list">
                <thead>
                    <tr>
                        <th class="name">Archives</th>
                        <th class="size">Size</th>
                        <th class="created">Created</th>
                        <th class="funcs">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="dup-pro-import-package is-importable" data-path="">
                        <td class="name">
                            <span class="text">20230101_duplicator_package_49809197bde745059228_20221004184822_archive.daf</span>
                        </td>
                        <td class="size">62.67 MB</td>
                        <td class="created">2022-10-04 00:00:00</td>
                        <td class="funcs">
                            <div class="actions">
                                <button type="button" class="button dup-pro-import-action-package-detail-toggle">
                                    <i class="fa fa-caret-down"></i> Details
                                </button>
                                <span class="separator"></span>
                                <button type="button" class="dup-pro-import-action-remove button button-secondary">
                                    <i class="fa fa-ban"></i> Remove
                                </button>
                                <span class="separator"></span>
                                <button type="button" class="dup-pro-import-action-install button button-primary" data-install-url="">
                                    <i class="fa fa-bolt fa-sm"></i> Continue
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr class="dup-pro-import-package is-importable" data-path="">
                        <td class="name">
                            <span class="text">20230101_duplicator_package_49809197bde745059228_20221004184822_archive.daf</span>
                        </td>
                        <td class="size">62.67 MB</td>
                        <td class="created">2022-10-04 00:00:00</td>
                        <td class="funcs">
                            <div class="actions">
                                <button type="button" class="button dup-pro-import-action-package-detail-toggle">
                                    <i class="fa fa-caret-down"></i> Details
                                </button>
                                <span class="separator"></span>
                                <button type="button" class="dup-pro-import-action-remove button button-secondary">
                                    <i class="fa fa-ban"></i> Remove
                                </button>
                                <span class="separator"></span>
                                <button type="button" class="dup-pro-import-action-install button button-primary" data-install-url="">
                                    <i class="fa fa-bolt fa-sm"></i> Continue
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr class="dup-pro-import-package is-importable" data-path="">
                        <td class="name">
                            <span class="text">20230101_duplicator_package_49809197bde745059228_20221004184822_archive.daf</span>
                        </td>
                        <td class="size">62.67 MB</td>
                        <td class="created">2022-10-04 00:00:00</td>
                        <td class="funcs">
                            <div class="actions">
                                <button type="button" class="button dup-pro-import-action-package-detail-toggle">
                                    <i class="fa fa-caret-down"></i> Details
                                </button>
                                <span class="separator"></span>
                                <button type="button" class="dup-pro-import-action-remove button button-secondary">
                                    <i class="fa fa-ban"></i> Remove
                                </button>
                                <span class="separator"></span>
                                <button type="button" class="dup-pro-import-action-install button button-primary" data-install-url="">
                                    <i class="fa fa-bolt fa-sm"></i> Continue
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr class="dup-pro-import-package is-importable" data-path="">
                        <td class="name">
                            <span class="text">20230101_duplicator_package_49809197bde745059228_20221004184822_archive.daf</span>
                        </td>
                        <td class="size">62.67 MB</td>
                        <td class="created">2022-10-04 00:00:00</td>
                        <td class="funcs">
                            <div class="actions">
                                <button type="button" class="button dup-pro-import-action-package-detail-toggle">
                                    <i class="fa fa-caret-down"></i> Details
                                </button>
                                <span class="separator"></span>
                                <button type="button" class="dup-pro-import-action-remove button button-secondary">
                                    <i class="fa fa-ban"></i> Remove
                                </button>
                                <span class="separator"></span>
                                <button type="button" class="dup-pro-import-action-install button button-primary" data-install-url="">
                                    <i class="fa fa-bolt fa-sm"></i> Continue
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr class="dup-pro-import-package is-importable" data-path="">
                        <td class="name">
                            <span class="text">20230101_duplicator_package_49809197bde745059228_20221004184822_archive.daf</span>
                        </td>
                        <td class="size">62.67 MB</td>
                        <td class="created">2022-10-04 00:00:00</td>
                        <td class="funcs">
                            <div class="actions">
                                <button type="button" class="button dup-pro-import-action-package-detail-toggle">
                                    <i class="fa fa-caret-down"></i> Details
                                </button>
                                <span class="separator"></span>
                                <button type="button" class="dup-pro-import-action-remove button button-secondary">
                                    <i class="fa fa-ban"></i> Remove
                                </button>
                                <span class="separator"></span>
                                <button type="button" class="dup-pro-import-action-install button button-primary" data-install-url="">
                                    <i class="fa fa-bolt fa-sm"></i> Continue
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php TplMng::getInstance()->render(
    'parts/Education/static-popup',
    array(
        'title'        => __('Overwrite a WordPress site with Drag and Drop Import!', 'duplicator'),
        'warning-text' => __('Drag and Drop Import is not available in Duplicator Lite!', 'duplicator'),
        'content-tpl'  => 'mocks/import/content-popup',
        'upsell-url'   => Upsell::getCampaignUrl('blurred-mocks', 'Import')
    ),
    true
); ?>
