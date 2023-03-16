<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Ajax;

use DUP_Handler;
use DUP_Log;
use DUP_Util;
use Duplicator\Libs\Snap\SnapUtil;
use Exception;

class AjaxWrapper
{
    /**
     * This function wrap a callback and return always a json well formatted output.
     *
     * check nonce and capability if passed and return a json with this format
     * [
     *      success : bool
     *      data : [
     *          funcData : mixed    // callback return data
     *          message : string    // a message for jvascript func (for example an exception message)
     *          output : string     // all normal output wrapped between ob_start and ob_get_clean
     *                              // if $errorUnespectedOutput is true and output isn't empty the json return an error
     *      ]
     * ]
     *
     * @param callable $callback              callback function
     * @param string   $nonceaction           if action is null don't verify nonce
     * @param string   $nonce                 nonce string
     * @param string   $capability            if capability is null don't verify capability
     * @param bool     $errorUnespectedOutput if true thorw exception with unespected optput
     *
     * @return void
     */
    public static function json(
        $callback,
        $nonceaction = null,
        $nonce = null,
        $capability = null,
        $errorUnespectedOutput = true
    ) {
        $error = false;

        $result = array(
            'funcData' => null,
            'output'   => '',
            'message'  => ''
        );

        ob_start();
        try {
            DUP_Handler::init_error_handler();
            $nonce = SnapUtil::sanitizeNSCharsNewline($nonce);
            if (!is_null($nonceaction) && !wp_verify_nonce($nonce, $nonceaction)) {
                DUP_Log::trace('Security issue');
                throw new Exception('Security issue');
            }
            if (!is_null($capability)) {
                DUP_Util::hasCapability($capability, DUP_Util::SECURE_ISSUE_THROW);
            }

            // execute ajax function
            $result['funcData'] = call_user_func($callback);
        } catch (Exception $e) {
            $error             = true;
            $result['message'] = $e->getMessage();
        }

        $result['output'] = ob_get_clean();
        if ($errorUnespectedOutput && !empty($result['output'])) {
            $error = true;
        }

        if ($error) {
            wp_send_json_error($result);
        } else {
            wp_send_json_success($result);
        }
    }
}
