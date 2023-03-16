<?php

/**
 * Installer Hooks Manager
 *
 * @package   Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 */

namespace Duplicator\Installer\Core\Hooks;

final class HooksMng
{
    /**
     *
     * @var self
     */
    private static $instance = null;

    /**
     *
     * @var Hook[]
     */
    private $filters = array();

    /**
     *
     * @var Hook[]
     */
    private $actions = array();

    /**
     *
     * @var string[]
     */
    private $currentFilter = array();

    /**
     *
     * @return self
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * init params and load
     */
    private function __construct()
    {
    }

    /**
     * Hook a function or method to a specific filter action.
     *
     * WordPress offers filter hooks to allow plugins to modify
     * various types of internal data at runtime.
     *
     * A plugin can modify data by binding a callback to a filter hook. When the filter
     * is later applied, each bound callback is run in order of priority, and given
     * the opportunity to modify a value by returning a new value.
     *
     * The following example shows how a callback function is bound to a filter hook.
     *
     * Note that `$example` is passed to the callback, (maybe) modified, then returned:
     *
     *     function example_callback( $example ) {
     *         // Maybe modify $example in some way.
     *         return $example;
     *     }
     *     add_filter( 'example_filter', 'example_callback' );
     *
     * Bound callbacks can accept from none to the total number of arguments passed as parameters
     * in the corresponding applyFilters() call.
     *
     * In other words, if an applyFilters() call passes four total arguments, callbacks bound to
     * it can accept none (the same as 1) of the arguments or up to four. The important part is that
     * the `$accepted_args` value must reflect the number of arguments the bound callback *actually*
     * opted to accept. If no arguments were accepted by the callback that is considered to be the
     * same as accepting 1 argument. For example:
     *
     *     // Filter call.
     *     $value = applyFilters( 'hook', $value, $arg2, $arg3 );
     *
     *     // Accepting zero/one arguments.
     *     function example_callback() {
     *         ...
     *         return 'some value';
     *     }
     *     add_filter( 'hook', 'example_callback' ); // Where $priority is default 10, $accepted_args is default 1.
     *
     *     // Accepting two arguments (three possible).
     *     function example_callback( $value, $arg2 ) {
     *         ...
     *         return $maybe_modified_value;
     *     }
     *     add_filter( 'hook', 'example_callback', 10, 2 ); // Where $priority is 10, $accepted_args is 2.
     *
     * *Note:* The function will return true whether or not the callback is valid.
     * It is up to you to take care. This is done for optimization purposes, so
     * everything is as quick as possible.
     *
     * @param string   $tag             The name of the filter to hook the $function_to_add callback to.
     * @param callable $function_to_add The callback to be run when the filter is applied.
     * @param int      $priority        Optional. Used to specify the order in which the functions
     *                                  associated with a particular action are executed.
     *                                  Lower numbers correspond with earlier execution,
     *                                  and functions with the same priority are executed
     *                                  in the order in which they were added to the action. Default 10.
     * @param int      $accepted_args   Optional. The number of arguments the function accepts. Default 1.
     *
     * @return true
     */
    public function addFilter($tag, $function_to_add, $priority = 10, $accepted_args = 1)
    {
        if (!isset($this->filters[$tag])) {
            $this->filters[$tag] = new Hook();
        }
        $this->filters[$tag]->addFilter($tag, $function_to_add, $priority, $accepted_args);
        return true;
    }

    /**
     * Checks if any filter has been registered for a hook.
     *
     * When using the `$function_to_check` argument, this function may return a non-boolean value
     * that evaluates to false (e.g. 0), so use the `===` operator for testing the return value.
     *
     * @param string         $tag               The name of the filter hook.
     * @param callable|false $function_to_check Optional. The callback to check for. Default false.
     *
     * @return bool|int If `$function_to_check` is omitted, returns boolean for whether the hook has
     *                  anything registered. When checking a specific function, the priority of that
     *                  hook is returned, or false if the function is not attached.
     */
    public function hasFilter($tag, $function_to_check = false)
    {
        if (!isset($this->filters[$tag])) {
            return false;
        }

        return $this->filters[$tag]->hasFilter($tag, $function_to_check);
    }

    /**
     * Calls the callback functions that have been added to a filter hook.
     *
     * The callback functions attached to the filter hook are invoked by calling
     * this function. This function can be used to create a new filter hook by
     * simply calling this function with the name of the new hook specified using
     * the `$tag` parameter.
     *
     * The function also allows for multiple additional arguments to be passed to hooks.
     *
     * Example usage:
     *
     *     // The filter callback function.
     *     function example_callback( $string, $arg1, $arg2 ) {
     *         // (maybe) modify $string.
     *         return $string;
     *     }
     *     add_filter( 'example_filter', 'example_callback', 10, 3 );
     *
     *     /*
     *      * Apply the filters by calling the 'example_callback()' function
     *      * that's hooked onto `example_filter` above.
     *      *
     *      * - 'example_filter' is the filter hook.
     *      * - 'filter me' is the value being filtered.
     *      * - $arg1 and $arg2 are the additional arguments passed to the callback.
     *     $value = applyFilters( 'example_filter', 'filter me', $arg1, $arg2 );
     *
     * @param string $tag   The name of the filter hook.
     * @param mixed  $value The value to filter.
     *                      ... $args  Additional parameters to pass to the callback functions.
     *
     * @return mixed The filtered value after all hooked functions are applied to it.
     */
    public function applyFilters($tag, $value)
    {
        $args = func_get_args();

        // Do 'all' actions first.
        if (isset($this->filters['all'])) {
            $this->currentFilter[] = $tag;
            $this->callAllHook($args);
        }

        if (!isset($this->filters[$tag])) {
            if (isset($this->filters['all'])) {
                array_pop($this->currentFilter);
            }
            return $value;
        }

        if (!isset($this->filters['all'])) {
            $this->currentFilter[] = $tag;
        }

        // Don't pass the tag name to Hook.
        array_shift($args);

        $filtered = $this->filters[$tag]->applyFilters($value, $args);

        array_pop($this->currentFilter);

        return $filtered;
    }

    /**
     * Removes a function from a specified filter hook.
     *
     * This function removes a function attached to a specified filter hook. This
     * method can be used to remove default functions attached to a specific filter
     * hook and possibly replace them with a substitute.
     *
     * To remove a hook, the $function_to_remove and $priority arguments must match
     * when the hook was added. This goes for both filters and actions. No warning
     * will be given on removal failure.
     *
     * @param string   $tag                The filter hook to which the function to be removed is hooked.
     * @param callable $function_to_remove The name of the function which should be removed.
     * @param int      $priority           Optional. The priority of the function. Default 10.
     *
     * @return bool    Whether the function existed before it was removed.
     */
    public function removeFilter($tag, $function_to_remove, $priority = 10)
    {
        $r = false;
        if (isset($this->filters[$tag])) {
            $r = $this->filters[$tag]->removeFilter($tag, $function_to_remove, $priority);
            if (!$this->filters[$tag]->callbacks) {
                unset($this->filters[$tag]);
            }
        }

        return $r;
    }

    /**
     * Remove all of the hooks from a filter.
     *
     * @param string    $tag      The filter to remove hooks from.
     * @param int|false $priority Optional. The priority number to remove. Default false.
     *
     * @return true True when finished.
     */
    public function removeAllFilters($tag, $priority = false)
    {
        if (isset($this->filters[$tag])) {
            $this->filters[$tag]->removeAllFilters($priority);
            if (!$this->filters[$tag]->hasFilters()) {
                unset($this->filters[$tag]);
            }
        }

        return true;
    }

    /**
     * Hooks a function on to a specific action.
     *
     * Actions are the hooks that the WordPress core launches at specific points
     * during execution, or when specific events occur. Plugins can specify that
     * one or more of its PHP functions are executed at these points, using the
     * Action API.
     *
     * @param string   $tag             The name of the action to which the $function_to_add is hooked.
     * @param callable $function_to_add The name of the function you wish to be called.
     * @param int      $priority        Optional. Used to specify the order in which the functions
     *                                  associated with a particular action are executed. Default 10.
     *                                  Lower numbers correspond with earlier execution,
     *                                  and functions with the same priority are executed
     *                                  in the order in which they were added to the action.
     * @param int      $accepted_args   Optional. The number of arguments the function accepts. Default 1.
     *
     * @return true Will always return true.
     */
    public function addAction($tag, $function_to_add, $priority = 10, $accepted_args = 1)
    {
        return $this->addFilter($tag, $function_to_add, $priority, $accepted_args);
    }

    /**
     * Execute functions hooked on a specific action hook.
     *
     * This function invokes all functions attached to action hook `$tag`. It is
     * possible to create new action hooks by simply calling this function,
     * specifying the name of the new hook using the `$tag` parameter.
     *
     * You can pass extra arguments to the hooks, much like you can with `applyFilters()`.
     *
     * Example usage:
     *
     *     // The action callback function.
     *     function example_callback( $arg1, $arg2 ) {
     *         // (maybe) do something with the args.
     *     }
     *     add_action( 'example_action', 'example_callback', 10, 2 );
     *
     *     /*
     *      * Trigger the actions by calling the 'example_callback()' function
     *      * that's hooked onto `example_action` above.
     *      *
     *      * - 'example_action' is the action hook.
     *      * - $arg1 and $arg2 are the additional arguments passed to the callback.
     *     $value = do_action( 'example_action', $arg1, $arg2 );
     *
     * @global Hook[] $this->filters         Stores all of the filters and actions.
     * @global string[]  $this->currentFilter Stores the list of current filters with the current one last.
     *
     * @param string $tag The name of the action to be executed.
     *                    ...$arg Optional. Additional arguments which are passed on to the
     *                    functions hooked to the action. Default empty.
     *
     * @return void
     */
    public function doAction($tag)
    {
        $arg = $all_args = func_get_args();
        array_shift($arg); // remove tag action

        // Do 'all' actions first.
        if (isset($this->filters['all'])) {
            $this->currentFilter[] = $tag;
            $this->callAllHook($all_args);
        }

        if (!isset($this->filters[$tag])) {
            if (isset($this->filters['all'])) {
                array_pop($this->currentFilter);
            }
            return;
        }

        if (!isset($this->filters['all'])) {
            $this->currentFilter[] = $tag;
        }

        if (empty($arg)) {
            $arg[] = '';
        } elseif (is_array($arg[0]) && 1 === count($arg[0]) && isset($arg[0][0]) && is_object($arg[0][0])) {
            // Backward compatibility for PHP4-style passing of `array( &$this )` as action `$arg`.
            $arg[0] = $arg[0][0];
        }

        $this->filters[$tag]->doAction($arg);

        array_pop($this->currentFilter);
    }

    /**
     * Checks if any action has been registered for a hook.
     *
     * When using the `$function_to_check` argument, this function may return a non-boolean value
     * that evaluates to false (e.g. 0), so use the `===` operator for testing the return value.
     *
     * @see hasFilter() has_action() is an alias of hasFilter().
     *
     * @param string         $tag               The name of the action hook.
     * @param callable|false $function_to_check Optional. The callback to check for. Default false.
     *
     * @return bool|int If `$function_to_check` is omitted, returns boolean for whether the hook has
     *                  anything registered. When checking a specific function, the priority of that
     *                  hook is returned, or false if the function is not attached.
     */
    public function hasAction($tag, $function_to_check = false)
    {
        return $this->hasFilter($tag, $function_to_check);
    }

    /**
     * Removes a function from a specified action hook.
     *
     * This function removes a function attached to a specified action hook. This
     * method can be used to remove default functions attached to a specific filter
     * hook and possibly replace them with a substitute.
     *
     * @param string   $tag                The action hook to which the function to be removed is hooked.
     * @param callable $function_to_remove The name of the function which should be removed.
     * @param int      $priority           Optional. The priority of the function. Default 10.
     *
     * @return bool Whether the function is removed.
     */
    public function removeAction($tag, $function_to_remove, $priority = 10)
    {
        return $this->removeFilter($tag, $function_to_remove, $priority);
    }

    /**
     * Remove all of the hooks from an action.
     *
     * @param string    $tag      The action to remove hooks from.
     * @param int|false $priority The priority number to remove them from. Default false.
     *
     * @return true True when finished.
     */
    public function removeAllActions($tag, $priority = false)
    {
        return $this->removeAllFilters($tag, $priority);
    }

    /**
     * Retrieve the name of the current filter or action.
     *
     * @global string[] $wp_current_filter Stores the list of current filters with the current one last
     *
     * @return string Hook name of the current filter or action.
     */
    public function currentFilter()
    {
        return end($this->currentFilter);
    }

    /**
     * Retrieve the name of the current action.
     *
     * @return string Hook name of the current action.
     */
    public function currentAction()
    {
        return $this->currentFilter();
    }

    /**
     * Call the 'all' hook, which will process the functions hooked into it.
     *
     * The 'all' hook passes all of the arguments or parameters that were used for
     * the hook, which this function was called for.
     *
     * This function is used internally for apply_filters(), do_action(), and
     * do_action_ref_array() and is not meant to be used from outside those
     * functions. This function does not check for the existence of the all hook, so
     * it will fail unless the all hook exists prior to this function call.
     *
     * @global Hook[] $this->filters Stores all of the filters and actions.
     *
     * @param array $args The collected parameters from the hook that was called.
     *
     * @return void
     */
    private function callAllHook($args)
    {
        $this->filters['all']->doAllHook($args);
    }
}
