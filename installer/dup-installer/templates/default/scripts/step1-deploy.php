<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Libs\Snap\SnapJson;

$nextStepPrams = array(
    PrmMng::PARAM_CTRL_ACTION => 'ctrl-step2',
    DUPX_Security::CTRL_TOKEN               => DUPX_CSRF::generate('ctrl-step2')
);
?>
<script>
    DUPX.deployStep1 = function () {
        DUPX.multipleStepDeploy($('#s1-input-form'), <?php echo SnapJson::jsonEncode($nextStepPrams); ?>);
    };
</script>