<?php

/**
 * Duplicator package row in table packages list
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

use Duplicator\Controllers\PackagesPageController;
use Duplicator\Core\Controllers\ControllersManager;

defined("ABSPATH") or die("");

/**
 * Variables
 *
 * @var \Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 * @var DUP_Package[] $packages
 */
$packages = $tplData['packages'];

?>
<div class="dup-section-last-packages">
    <p>
        <b><?php esc_html_e('Recently Packages', 'duplicator-pro'); ?></b>
    </p>
    <?php if (count($packages) > 0) { ?>
    <ul>
        <?php foreach ($packages as $package) {
            $createdTime  = strtotime($package->Created);
            $createdDate  = date(get_option('date_format'), $createdTime);
            $createdHours = date(get_option('time_format'), $createdTime);

            ?>
            <li>
                <a href="<?php echo esc_url(ControllersManager::getPackageDetailUrl($package->ID)); ?>">
                    <?php echo esc_html($package->Name); ?>
                </a> - <i class="gary" ><?php echo esc_html($createdDate . ' ' .  $createdHours); ?></i>
            </li>
        <?php } ?>
    </ul>
    <?php } ?>
    <p class="dup-packages-counts">
        <?php printf(esc_html__('Packages: %1$d, Failures: %2$d', 'duplicator-pro'), $tplData['totalPackages'], $tplData['totalFailures']); ?>
    </p>
</div>