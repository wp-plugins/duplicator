<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Libs\Snap;

use Exception;

/**
 * Snap code generator utils
 */
class SnapCode
{
    /**
     * Get class code from file
     *
     * @param string $file              file path
     * @param bool   $wrapNamespace     if true wrap name space with brackets
     * @param bool   $removeFirstPHPTag if true removes opening php tah
     * @param bool   $removeBalnkLines  if true remove blank lines
     * @param bool   $removeComments    if true remove comments
     * @param bool   $required          if true and file can't be read then throw and exception else return empty string
     *
     * @return string
     */
    public static function getSrcClassCode(
        $file,
        $wrapNamespace = true,
        $removeFirstPHPTag = false,
        $removeBalnkLines = true,
        $removeComments = true,
        $required = true
    ) {
        if (!is_file($file) || !is_readable($file)) {
            if ($required) {
                throw new Exception('Code file "' . $file . '" don\'t exists');
            }
            return '';
        }

        if (($src = file_get_contents($file)) === false) {
            if ($required) {
                throw new Exception('Can\'t read code file "' . $file . '"');
            }
            return '';
        }

        if ($removeFirstPHPTag) {
            $src = preg_replace('/^(<\?php)/', "", $src);
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
