<?php
namespace App\Traits;

trait Sanitize
{
    private final function sanitize($source, $name, $filters, $noRecursive=false, $defaultValue=null)
    {
        $value = null;
        if (is_array($source)) {
            if (array_key_exists($name, $source)) {
                $value = $this->filter->sanitize($source[$name], $filters, $noRecursive);
            }
        } else {
            $value = $this->filter->sanitize($source, $filters, $noRecursive);
        }
        if (empty($value)) {
            return $this->filter->sanitize($defaultValue, $filters, $noRecursive);
        }
        return $value;
    }
}
