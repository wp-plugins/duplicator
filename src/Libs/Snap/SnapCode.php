<?php

/**
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 */

namespace Duplicator\Libs\Snap;

/**
 * Snap code generator utils
 */
class SnapCode
{
    /**
     * Get class code from file
     *
     * @param string $file             file path
     * @param bool   $wrapNamespace    if true wrap name space with brackets
     * @param bool   $removeBalnkLines if treu remove balnk lines
     * @param bool   $removeComments   if true remove comments
     *
     * @return string
     */
    public static function getSrcClassCode(
        $file,
        $wrapNamespace = true,
        $removeBalnkLines = true,
        $removeComments = true
    ) {
        if (!is_file($file) || !is_readable($file)) {
            return '';
        }

        if (($src = file_get_contents($file)) === false) {
            return '';
        }

        if ($wrapNamespace) {
            $src = preg_replace('/(.*^\s*)(namespace.*?)(;)(.*)/sm', "$2 {\n$4}", $src);
        }

        if ($removeComments) {
            $src = preg_replace('/^\s*\/\*.*?\*\//sm', '', $src);
            $src = preg_replace('/^\s*\/\/.*$/m', '', $src);
        }

        if ($removeBalnkLines) {
            $src = preg_replace('/\n\s*\n/s', "\n", $src);
        }

        return $src;
    }
}
