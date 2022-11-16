<?php

namespace Duplicator\Installer\Utils\Tests;

class MessageCustomizerItem
{
    private $checkCallback;
    private $applyCallback;

    /**
     * @param callable|bool $checkCallback callback or bool whether to apply customization
     * @param callable      $applyCallback the customizations to be applied
     */
    public function __construct($checkCallback, $applyCallback)
    {
        if (!is_bool($checkCallback) && !is_callable($checkCallback)) {
            throw new \Exception("check callback must be either bool or callable");
        }
        $this->checkCallback = $checkCallback;

        if (!is_callable($applyCallback)) {
            throw new \Exception("customization callback must be callable");
        }
        $this->applyCallback = $applyCallback;
    }

    /**
     * @param mixed $input necessary input to check condition
     * @return bool
     */
    public function conditionSatisfied($input)
    {
        return (is_bool($this->checkCallback) && $this->checkCallback) || call_user_func($this->checkCallback, $input);
    }

    /**
     * @param string $string  string to be customized
     * @param mixed  $context context about what to apply
     * @return false|mixed
     */
    public function apply($string, $context)
    {
        return call_user_func($this->applyCallback, $string, $context);
    }
}
