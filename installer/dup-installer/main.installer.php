<?php

/*
 * Duplicator Website Installer
 * Copyright (C) 2018, Snap Creek LLC
 * website: snapcreek.com
 *
 * Duplicator (Pro) Plugin is distributed under the GNU General Public License, Version 3,
 * June 2007. Copyright (C) 2007 Free Software Foundation, Inc., 51 Franklin
 * St, Fifth Floor, Boston, MA 02110, USA
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

if (!defined('DUPXABSPATH')) {
    define('DUPXABSPATH', dirname(__FILE__));
}

$disabled_dirs = array(
	'backups-dup-lite',
	'wp-snapshots'
);

if (in_array(basename(dirname(__FILE__)), $disabled_dirs)) {
	die;
}

define('DUPX_VERSION', '1.5.0');
define('DUPX_INIT', str_replace('\\', '/', dirname(__FILE__)));
define('DUPX_ROOT', preg_match('/^[\\\\\/]?$/', dirname(DUPX_INIT)) ? '/' : dirname(DUPX_INIT));

use Duplicator\Installer\Utils\Log\Log;
use Duplicator\Libs\Snap\SnapUtil;

require_once(DUPX_INIT . '/src/Utils/Autoloader.php');
Duplicator\Installer\Utils\Autoloader::register();

try {
    /**
     * init constants and include
     */
    Duplicator\Installer\Core\Bootstrap::init();
    Log::setThrowExceptionOnError(true);
    Log::logTime('INIT END', Log::LV_DETAILED);

    // if is ajax always die in controller
    DUPX_Ctrl_ajax::controller();
} catch (Exception $ex) {
    Log::logException($ex, Log::LV_DEFAULT, 'EXCEPTION ON INIT: ');
    dupxTplRender('page-boot-error', array(
        'message' => $ex->getMessage(),
        'trace'   => $ex->getTraceAsString()
    ));
    die();
}

ob_start();
try {
    $controller     = DUPX_CTRL::getInstance();
    $exceptionError = false;
    // Log::error thotw an exception
    Log::setThrowExceptionOnError(true);
    Log::logTime('CONTROLLER START', Log::LV_DETAILED);

    $controller->mainController();
} catch (Exception $e) {
    SnapUtil::obCleanAll(false);
    $controller->setExceptionPage($e);
}

/**
 * clean output
 */
$unespectOutput = trim(ob_get_clean());
ob_end_clean();
if (!empty($unespectOutput)) {
    Log::info('ERROR: Unespect output ' . Log::v2str($unespectOutput));
    $exceptionError = new Exception('Unespected output ' . Log::v2str($unespectOutput));
    $controller->setExceptionPage($exceptionError);
}

ob_start();
try {
    echo $controller->renderPage();
} catch (Exception $e) {
    SnapUtil::obCleanAll(false);
    ob_end_clean();
    $controller->setExceptionPage($e);
    echo $controller->renderPage();
}
