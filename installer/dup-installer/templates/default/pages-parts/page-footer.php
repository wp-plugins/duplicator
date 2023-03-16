<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Libs\Upsell;

dupxTplRender('parts/education/did-you-know-blurb', array(
        'features' => Upsell::getProFeatureList()
));
?>
</div>
<?php
dupxTplRender('parts/education/footer-cta');
PrmMng::getInstance()->getParamsHtmlInfo();
?>
</body>
</html>

