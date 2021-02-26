<?php

namespace Validation;

class Helper
{
    /**
     * 空白
     */
    public static function blank($value)
    {
        if (null === $value) {
            return true;
        }

        if (is_string($value) && trim($value) === '') {
            return true;
        }

        return false;
    }
}