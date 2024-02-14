<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Installer\Utils\InstallerUpsell;

dupxTplRender('parts/education/did-you-know-blurb', array(
        'features' => InstallerUpsell::getProFeatureList()
));
?>
</div>
<?php
dupxTplRender('parts/education/footer-cta');
PrmMng::getInstance()->getParamsHtmlInfo();
?>
</body>
</html>

