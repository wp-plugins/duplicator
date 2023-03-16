<?php

/**
 * Cunking manager with stored data in json file.
 *
 * Standard: PSR-2
 *
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\Chunk
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Libs\Snap\SnapJson;

require_once(DUPX_INIT . '/classes/chunk/class.chunkingmanager.php');

/**
 * Store position on json file
 */
abstract class DUPX_ChunkingManager_file extends DUPX_ChunkingManager
{
    /**
     * load data from previous step if exists
     *
     * @param string $key file name
     *
     * @return mixed
     */
    protected function getStoredData($key)
    {
        if (file_exists($key)) {
            $data = file_get_contents($key);
            return json_decode($data, true);
        } else {
            return null;
        }
    }

    /**
     * delete stored data if exists
     */
    protected function deleteStoredData($key)
    {
        if (file_exists($key)) {
            unlink($key);
        }
    }

    /**
     *
     * @param string $key file path
     * @param mixed $data to save in file path
     *
     * @return boolean|int This function returns the number of bytes that were written to the file, or FALSE on failure.
     */
    protected function saveStoredData($key, $data)
    {
        if (($json = SnapJson::jsonEncode($data)) === false) {
            throw new Exception('Json encode chunk data error');
        }

        return file_put_contents($key, $json);
    }
}
