<?php

/**
 * Plugin API: WP_Hook class
 *
 * From wordpress WP_Hook class
 */

namespace Duplicator\Installer\Core\Hooks;

/**
 * Core class used to implement action and filter hook functionality.
 *
 * @see Iterator
 * @see ArrayAccess
 */
final class Hook implements \Iterator, \ArrayAccess
{
    /**
     * Hook callbacks.
     *
     * @var array
     */
    public $callbacks = array();

    /**
     * The priority keys of actively running iterations of a hook.
     *
     * @var array
     */
    private $iterations = array();

    /**
     * The current priority of actively running iterations of a hook.
     *
     * @var array
     */
    private $currentPriority = array();

    /**
     * Number of levels this hook can be recursively called.
     *
     * @var int
     */
    private $nestingLevel = 0;

    /**
     * Flag for if we're current doing an action, rather than a filter.
     *
     * @var bool
     */
    private $doingAction = false;

    /**
     * Hooks a function or method to a specific filter action.
     *
     * @param string   $tag             The name of the filter to hook the $function_to_add callback to.
     * @param callable $function_to_add The callback to be run when the filter is applied.
     * @param int      $priority        The order in which the functions associated with a particular action
     *                                  are executed. Lower numbers correspond with earlier execution,
     *                                  and functions with the same priority are executed in the order
     *                                  in which they were added to the action.
     * @param int      $accepted_args   The number of arguments the function accepts.
     *
     * @return void
     */
    public function addFilter($tag, $function_to_add, $priority, $accepted_args)
    {
        $idx = self::wpFilterBuildUniqueId($tag, $function_to_add, $priority);

        $priority_existed = isset($this->callbacks[$priority]);

        $this->callbacks[$priority][$idx] = array(
            'function'      => $function_to_add,
            'accepted_args' => $accepted_args,
        );

        // If we're adding a new priority to the list, put them back in sorted order.
        if (!$priority_existed && count($this->callbacks) > 1) {
            ksort($this->callbacks, SORT_NUMERIC);
        }

        if ($this->nestingLevel > 0) {
            $this->resortActiveIterations($priority, $priority_existed);
        }
    }

    /**
     * Handles resetting callback priority keys mid-iteration.
     *
     * @param false|int $new_priority     Optional. The priority of the new filter being added. Default false,
     *                                    for no priority being added.
     * @param bool      $priority_existed Optional. Flag for whether the priority already existed before the new
     *                                    filter was added. Default false.
     *
     * @return void
     */
    private function resortActiveIterations($new_priority = false, $priority_existed = false)
    {
        $new_priorities = array_keys($this->callbacks);

        // If there are no remaining hooks, clear out all running iterations.
        if (!$new_priorities) {
            foreach ($this->iterations as $index => $iteration) {
                $this->iterations[$index] = $new_priorities;
            }
            return;
        }

        $min = min($new_priorities);
        foreach ($this->iterations as $index => &$iteration) {
            $current = current($iteration);
            // If we're already at the end of this iteration, just leave the array pointer where it is.
            if (false === $current) {
                continue;
            }

            $iteration = $new_priorities;

            if ($current < $min) {
                array_unshift($iteration, $current);
                continue;
            }

            while (current($iteration) < $current) {
                if (false === next($iteration)) {
                    break;
                }
            }

            // If we have a new priority that didn't exist, but ::applyFilters() or ::doAction() thinks it's the current priority...
            if ($new_priority === $this->currentPriority[$index] && !$priority_existed) {
                /*
                 * ...and the new priority is the same as what $this->iterations thinks is the previous
                 * priority, we need to move back to it.
                 */

                if (false === current($iteration)) {
                    // If we've already moved off the end of the array, go back to the last element.
                    $prev = end($iteration);
                } else {
                    // Otherwise, just go back to the previous element.
                    $prev = prev($iteration);
                }
                if (false === $prev) {
                    // Start of the array. Reset, and go about our day.
                    reset($iteration);
                } elseif ($new_priority !== $prev) {
                    // Previous wasn't the same. Move forward again.
                    next($iteration);
                }
            }
        }
        unset($iteration);
    }

    /**
     * Unhooks a function or method from a specific filter action.
     *
     * @param string   $tag                The filter hook to which the function to be removed is hooked.
     * @param callable $function_to_remove The callback to be removed from running when the filter is applied.
     * @param int      $priority           The exact priority used when adding the original filter callback.
     *
     * @return bool Whether the callback existed before it was removed.
     */
    public function removeFilter($tag, $function_to_remove, $priority)
    {
        $function_key = self::wpFilterBuildUniqueId($tag, $function_to_remove, $priority);

        $exists = isset($this->callbacks[$priority][$function_key]);
        if ($exists) {
            unset($this->callbacks[$priority][$function_key]);
            if (!$this->callbacks[$priority]) {
                unset($this->callbacks[$priority]);
                if ($this->nestingLevel > 0) {
                    $this->resortActiveIterations();
                }
            }
        }
        return $exists;
    }

    /**
     * Checks if a specific action has been registered for this hook.
     *
     * When using the `$function_to_check` argument, this function may return a non-boolean value
     * that evaluates to false (e.g. 0), so use the `===` operator for testing the return value.
     *
     * @param string         $tag               Optional. The name of the filter hook. Default empty.
     * @param callable|false $function_to_check Optional. The callback to check for. Default false.
     *
     * @return bool|int If `$function_to_check` is omitted, returns boolean for whether the hook has
     *                  anything registered. When checking a specific function, the priority of that
     *                  hook is returned, or false if the function is not attached.
     */
    public function hasFilter($tag = '', $function_to_check = false)
    {
        if (false === $function_to_check) {
            return $this->hasFilters();
        }

        $function_key = self::wpFilterBuildUniqueId($tag, $function_to_check, false);
        if (!$function_key) {
            return false;
        }

        foreach ($this->callbacks as $priority => $callbacks) {
            if (isset($callbacks[$function_key])) {
                return $priority;
            }
        }

        return false;
    }

    /**
     * Checks if any callbacks have been registered for this hook.
     *
     * @return bool True if callbacks have been registered for the current hook, otherwise false.
     */
    public function hasFilters()
    {
        foreach ($this->callbacks as $callbacks) {
            if ($callbacks) {
                return true;
            }
        }
        return false;
    }

    /**
     * Removes all callbacks from the current filter.
     *
     * @param int|false $priority Optional. The priority number to remove. Default false.
     *
     * @return void
     */
    public function removeAllFilters($priority = false)
    {
        if (!$this->callbacks) {
            return;
        }

        if (false === $priority) {
            $this->callbacks = array();
        } elseif (isset($this->callbacks[$priority])) {
            unset($this->callbacks[$priority]);
        }

        if ($this->nestingLevel > 0) {
            $this->resortActiveIterations();
        }
    }

    /**
     * Calls the callback functions that have been added to a filter hook.
     *
     * @param mixed $value The value to filter.
     * @param array $args  Additional parameters to pass to the callback functions.
     *                     This array is expected to include $value at index 0.
     * @return mixed The filtered value after all hooked functions are applied to it.
     */
    public function applyFilters($value, $args)
    {
        if (!$this->callbacks) {
            return $value;
        }

        $nestingLevel = $this->nestingLevel++;

        $this->iterations[$nestingLevel] = array_keys($this->callbacks);
        $num_args                        = count($args);

        do {
            $this->currentPriority[$nestingLevel] = current($this->iterations[$nestingLevel]);
            $priority                             = $this->currentPriority[$nestingLevel];

            foreach ($this->callbacks[$priority] as $the_) {
                if (!$this->doingAction) {
                    $args[0] = $value;
                }

                // Avoid the array_slice() if possible.
                if (0 == $the_['accepted_args']) {
                    $value = call_user_func($the_['function']);
                } elseif ($the_['accepted_args'] >= $num_args) {
                    $value = call_user_func_array($the_['function'], $args);
                } else {
                    $value = call_user_func_array($the_['function'], array_slice($args, 0, (int) $the_['accepted_args']));
                }
            }
        } while (false !== next($this->iterations[$nestingLevel]));

        unset($this->iterations[$nestingLevel]);
        unset($this->currentPriority[$nestingLevel]);

        $this->nestingLevel--;

        return $value;
    }

    /**
     * Calls the callback functions that have been added to an action hook.
     *
     * @param array $args Parameters to pass to the callback functions.
     *
     * @return void
     */
    public function doAction($args)
    {
        $this->doingAction = true;
        $this->applyFilters('', $args);

        // If there are recursive calls to the current action, we haven't finished it until we get to the last one.
        if (!$this->nestingLevel) {
            $this->doingAction = false;
        }
    }

    /**
     * Processes the functions hooked into the 'all' hook.
     *
     * @param array $args Arguments to pass to the hook callbacks. Passed by reference.
     *
     * @return void
     */
    public function doAllHook(&$args)
    {
        $nestingLevel                    = $this->nestingLevel++;
        $this->iterations[$nestingLevel] = array_keys($this->callbacks);

        do {
            $priority = current($this->iterations[$nestingLevel]);
            foreach ($this->callbacks[$priority] as $the_) {
                call_user_func_array($the_['function'], $args);
            }
        } while (false !== next($this->iterations[$nestingLevel]));

        unset($this->iterations[$nestingLevel]);
        $this->nestingLevel--;
    }

    /**
     * Return the current priority level of the currently running iteration of the hook.
     *
     * @return int|false If the hook is running, return the current priority level. If it isn't running, return false.
     */
    public function currentPriority()
    {
        if (false === current($this->iterations)) {
            return false;
        }

        return current(current($this->iterations));
    }

    /**
     * Normalizes filters set up before WordPress has initialized to Hook objects.
     *
     * The `$filters` parameter should be an array keyed by hook name, with values
     * containing either:
     *
     *  - A `Hook` instance
     *  - An array of callbacks keyed by their priorities
     *
     * Examples:
     *
     *     $filters = array(
     *         'wp_fatal_error_handler_enabled' => array(
     *             10 => array(
     *                 array(
     *                     'accepted_args' => 0,
     *                     'function'      => function() {
     *                         return false;
     *                     },
     *                 ),
     *             ),
     *         ),
     *     );
     *
     * @param array $filters Filters to normalize. See documentation above for details.
     * @return WP_Hook[] Array of normalized filters.
     */
    public static function buildPreinitializedHooks($filters)
    {
        /** @var WP_Hook[] $normalized */
        $normalized = array();

        foreach ($filters as $tag => $callback_groups) {
            if (is_object($callback_groups) && $callback_groups instanceof self) {
                $normalized[$tag] = $callback_groups;
                continue;
            }
            $hook = new self();

            // Loop through callback groups.
            foreach ($callback_groups as $priority => $callbacks) {
                // Loop through callbacks.
                foreach ($callbacks as $cb) {
                    $hook->addFilter($tag, $cb['function'], $priority, $cb['accepted_args']);
                }
            }
            $normalized[$tag] = $hook;
        }
        return $normalized;
    }

    /**
     * Determines whether an offset value exists.
     *
     * @link https://www.php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset An offset to check for.
     * @return bool True if the offset exists, false otherwise.
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return isset($this->callbacks[$offset]);
    }

    /**
     * Retrieves a value at a specified offset.
     *
     * @link https://www.php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset The offset to retrieve.
     * @return mixed If set, the value at the specified offset, null otherwise.
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return isset($this->callbacks[$offset]) ? $this->callbacks[$offset] : null;
    }

    /**
     * Sets a value at a specified offset.
     *
     * @link https://www.php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value  The value to set.
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->callbacks[] = $value;
        } else {
            $this->callbacks[$offset] = $value;
        }
    }

    /**
     * Unsets a specified offset.
     *
     * @link https://www.php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset The offset to unset.
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        unset($this->callbacks[$offset]);
    }

    /**
     * Returns the current element.
     *
     * @link https://www.php.net/manual/en/iterator.current.php
     *
     * @return array Of callbacks at current priority.
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return current($this->callbacks);
    }

    /**
     * Moves forward to the next element.
     *
     * @link https://www.php.net/manual/en/iterator.next.php
     *
     * @return array Of callbacks at next priority.
     */
    #[\ReturnTypeWillChange]
    public function next()
    {
        return next($this->callbacks);
    }

    /**
     * Returns the key of the current element.
     *
     * @link https://www.php.net/manual/en/iterator.key.php
     *
     * @return mixed Returns current priority on success, or NULL on failure
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return key($this->callbacks);
    }

    /**
     * Checks if current position is valid.
     *
     * @link https://www.php.net/manual/en/iterator.valid.php
     *
     * @return bool Whether the current position is valid.
     */
    #[\ReturnTypeWillChange]
    public function valid()
    {
        return key($this->callbacks) !== null;
    }

    /**
     * Rewinds the Iterator to the first element.
     *
     * @link https://www.php.net/manual/en/iterator.rewind.php
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function rewind()
    {
        reset($this->callbacks);
    }

    /**
     * Build Unique ID for storage and retrieval.
     *
     * The old way to serialize the callback caused issues and this function is the
     * solution. It works by checking for objects and creating a new property in
     * the class to keep track of the object and new objects of the same class that
     * need to be added.
     *
     * It also allows for the removal of actions and filters for objects after they
     * change class properties. It is possible to include the property $wp_filter_id
     * in your class and set it to "null" or a number to bypass the workaround.
     * However this will prevent you from adding new classes and any new classes
     * will overwrite the previous hook by the same class.
     *
     * Functions and static method callbacks are just returned as strings and
     * shouldn't have any speed penalty.
     *
     * @link https://core.trac.wordpress.org/ticket/3875
     *
     * @since 2.2.3
     * @since 5.3.0 Removed workarounds for spl_object_hash().
     *              `$tag` and `$priority` are no longer used,
     *              and the function always returns a string.
     * @access private
     *
     * @param string   $tag      Unused. The name of the filter to build ID for.
     * @param callable $function The function to generate ID for.
     * @param int      $priority Unused. The order in which the functions
     *                           associated with a particular action are executed.
     * @return string Unique function ID for usage as array key.
     */
    protected static function wpFilterBuildUniqueId($tag, $function, $priority)
    {
        if (is_string($function)) {
            return $function;
        }

        if (is_object($function)) {
            // Closures are currently implemented as objects.
            $function = array($function, '');
        } else {
            $function = (array) $function;
        }

        if (is_object($function[0])) {
            // Object class calling.
            return spl_object_hash($function[0]) . $function[1];
        } elseif (is_string($function[0])) {
            // Static calling.
            return $function[0] . '::' . $function[1];
        }
    }
}
