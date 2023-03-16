<?php

namespace Duplicator\Utils\CachesPurge;

use DUP_Log;
use Error;
use Exception;

class CacheItem
{
    /**
     * name of purge element (usualli plugin name)
     *
     * @var string
     */
    protected $name = '';

    /**
     * check function, returns true if the element is to be purged
     *
     * @var callable|bool
     */
    protected $checkCallback = null;

    /**
     * Purge cache callback
     *
     * @var callable
     */
    protected $purgeCallback = null;

    /**
     * Message when cache is purged
     *
     * @var string
     */
    protected $purgedMessage = '';

    /**
     * Construnctor
     *
     * @param string        $name          item name
     * @param bool|callable $checkCallback check callback, return true if cache of current item have to removed
     * @param callable      $purgeCallback purge cache callback
     */
    public function __construct($name, $checkCallback, $purgeCallback)
    {
        if (strlen($name) == 0) {
            throw new Exception('name can\'t be empty');
        }
        $this->name = $name;
        if (!is_bool($checkCallback) && !is_callable($checkCallback)) {
            throw new Exception('checkCallback must be boolean or callable');
        }
        $this->checkCallback = $checkCallback;

        /* purge callback may not exist if the referenced plugin is not initialized.
         * That's why the check is performed only if you actually purge the plugin
         */
        $this->purgeCallback = $purgeCallback;
        $this->purgedMessage = sprintf(__('All caches on <b>%s</b> have been purged.', 'duplicator'), $this->name);
    }

    /**
     * overwrite default purged message
     *
     * @param string $message message if item have benn purged
     *
     * @return void
     */
    public function setPurgedMessage($message)
    {
        $this->purgedMessage = $message;
    }

    /**
     * purge caches item
     *
     * @param string $message message if item have benn purged
     *
     * @return bool
     */
    public function purge(&$message)
    {
        try {
            if (
                (is_bool($this->checkCallback) && $this->checkCallback) ||
                call_user_func($this->checkCallback) == true
            ) {
                DUP_Log::trace('Purge ' . $this->name);
                if (!is_callable($this->purgeCallback)) {
                    throw new Exception('purgeCallback must be callable');
                }
                call_user_func($this->purgeCallback);
                $message = $this->purgedMessage;
            }
            return true;
        } catch (Exception $e) {
            DUP_Log::trace('Error purge ' . $this->name . ' message:' . $e->getMessage());
            $message = sprintf(__('Error on caches purge of <b>%s</b>.', 'duplicator'), $this->name);
            return false;
        } catch (Error $e) {
            DUP_Log::trace('Error purge ' . $this->name . ' message:' . $e->getMessage());
            $message = sprintf(__('Error on caches purge of <b>%s</b>.', 'duplicator'), $this->name);
            return false;
        }
    }
}
