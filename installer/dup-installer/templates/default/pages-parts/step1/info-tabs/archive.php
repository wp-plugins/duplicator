<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;

$archiveConfig = DUPX_ArchiveConfig::getInstance();
?>
<div id="tabs-2">
    <table class="s1-archive-local">
        <tr>
            <td colspan="2"><div class="hdr-sub3">Archive File</div></td>
        </tr>
        <tr>
            <td>Created:</td>
            <td><?php echo $archiveConfig->created; ?> </td>
        </tr>
        <tr>
            <td>Size:</td>
            <td><?php echo DUPX_U::readableByteSize(DUPX_Conf_Utils::archiveSize()); ?> </td>
        </tr>
        <tr>
            <td>Archive:</td>
            <td><?php echo DUPX_ArchiveConfig::getInstance()->package_name; ?> </td>
        </tr>
    </table>


    <table class="s1-archive-local">
        <tr>
            <td colspan="2"><div class="hdr-sub3">Site Details</div></td>
        </tr>
        <tr>
            <td>Site:</td>
            <td><?php echo DUPX_U::esc_html($archiveConfig->blogname); ?> </td>
        </tr>
        <tr>
            <td>URL:</td>
            <td><?php echo DUPX_U::esc_html(rtrim($archiveConfig->getRealValue('siteUrl'), '/')); ?> </td>
        </tr>
        <tr>
            <td>Notes:</td>
            <td><?php echo strlen($archiveConfig->package_notes) ? "{$archiveConfig->package_notes}" : " - no notes - "; ?></td>
        </tr>
        <?php if ($archiveConfig->exportOnlyDB) : ?>
            <tr>
                <td>Mode:</td>
                <td>Archive only database was enabled during package package creation.</td>
            </tr>
        <?php endif; ?>
    </table>

</div>