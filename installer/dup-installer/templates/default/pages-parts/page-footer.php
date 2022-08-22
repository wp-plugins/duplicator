<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;

?>
</div>
<?php
PrmMng::getInstance()->getParamsHtmlInfo();
?>
</body>
</html>

