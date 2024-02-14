<?php

/**
 *
 * @package templates/default
 */

use Duplicator\Installer\Utils\InstallerUpsell;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * Variables
 *
 * @var int $packageSize
 * @var int $maxPackageSize
*/
?>
<p>
    This package is <?php echo $packageSize; ?>.
    You might run into issues installing packages bigger than <?php echo $maxPackageSize; ?>MB with Duplicator Lite.
    Duplicator Pro has support for larger sites. Confirmed migration of sites up to 100GB!
</p>
<p>
    <a style="color: green;" href="<?php echo InstallerUpsell::getCampaignUrl('installer', 'Large Package') ?>" target="_blank">
        Upgrade Now
    </a>
</p>