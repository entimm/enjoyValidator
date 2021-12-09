<?php

namespace EnjoyValidator;

use DateTime;

class Rule
{
    /**
     * 验证处理器
     * @var Validator
     */
    private $validator;

    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * 不能为空
     */
    public function ruleRequired($value)
    {
        return !Helper::blank($value);
    }

    /**
     * 值相等
     */
    public function ruleEq($value, $field, $thatValue)
    {
        return $value == $thatValue;
    }

    /**
     * 值不相等
     */
    public function ruleNotEq($value, $field, $thatValue)
    {
        return $value != $thatValue;
    }

    /**
     * 小于
     */
    public function ruleLt($value, $field, $thatValue)
    {
        return $value < $thatValue;
    }

    /**
     * 小于等于
     */
    public function ruleLte($value, $field, $thatValue)
    {
        return $value <= $thatValue;
    }

    /**
     * 大于
     */
    public function ruleGt($value, $field, $thatValue)
    {
        return $value > $thatValue;
    }

    /**
     * 大于等于
     */
    public function ruleGte($value, $field, $thatValue)
    {
        return $value >= $thatValue;
    }

    /**
     * 同意
     */
    public function ruleAccepted($value)
    {
        $acceptable = array('yes', 'on', 1, '1', true);

        return !Helper::blank($value) && in_array($value, $acceptable, true);
    }

    /**
     * 数组类型
     */
    public function ruleArr($value)
    {
        return is_array($value);
    }

    /**
     * json字符串
     */
    public function ruleJson($value, $field)
    {
        $arr = json_decode($value, true);

        if ($arr) {
            $this->validator->setValue($field, $arr);
        }

        return !!$arr;
    }

    /**
     * 数字类型
     */
    public function ruleNumeric($value)
    {
        return is_numeric($value);
    }

    /**
     * 整数（可负数）
     */
    public function ruleInteger($value)
    {
        return preg_match('/^([0-9]|-?[1-9][0-9]*)$/i', $value);
    }

    /**
     * 纯数字（非负数）
     */
    public function ruleNumber($value)
    {
        return preg_match('/^([0-9]|[1-9][0-9]*)$/i', $value);
    }

    /**
     * 多个非负纯数字，逗号分隔
     * 可设置非负数
     */
    public function ruleMultiNumber($value)
    {
        // 严格模式（不支持前缀+）
        return preg_match('/^(\d+,)*\d+$/', $value);
    }

    /**
     * 字符串长度相等
     */
    public function ruleLength($value, $field, $thatLength)
    {
        return $this->stringLength($value) == $thatLength;
    }

    /**
     * 不小于（数字、数组计数、字符长度）
     */
    public function ruleMin($value, $field, $min)
    {
        if (is_countable($value)) $value = count($value);
        elseif (is_string($value)) $value = strlen($value);

        return $min <= $value;
    }

    /**
     * 不大于（数字、数组计数、字符长度）
     */
    public function ruleMax($value, $field, $max)
    {
        if (is_countable($value)) $value = count($value);
        elseif (is_string($value)) $value = strlen($value);

        return $max >= $value;
    }

    /**
     * 区间（数字、数组计数、字符长度）
     */
    public function ruleBetween($value, $field, $min, $max)
    {
        if (is_countable($value)) $value = count($value);
        elseif (is_string($value)) $value = strlen($value);

        return $max >= $value && $value >= $min;
    }

    /**
     * 等于另一个字段
     */
    public function ruleEqThan($value, $field, $another)
    {
        return $value == $this->validator->getValue($another);
    }

    /**
     * 等于另一个字段
     */
    public function ruleNotEqThan($value, $field, $another)
    {
        return $value != $this->validator->getValue($another);
    }

    /**
     * 小于另一个字段
     */
    public function ruleLtThan($value, $field, $another)
    {
        return $value < $this->validator->getValue($another);
    }

    /**
     * 小于等于另一个字段
     */
    public function ruleLteThan($value, $field, $another)
    {
        return $value <= $this->validator->getValue($another);
    }

    /**
     * 大于另一个字段
     */
    public function ruleGtThan($value, $field, $another)
    {
        return $value > $this->validator->getValue($another);
    }

    /**
     * 大于等于另一个字段
     */
    public function ruleGteThan($value, $field, $another)
    {
        return $value >= $this->validator->getValue($another);
    }

    /**
     * 值在数组中
     */
    public function ruleIn($value, $field, ...$args)
    {
        $isAssoc = array_values($args) !== $args;
        if ($isAssoc) {
            $args = array_keys($args);
        }

        return in_array($value, $args);
    }

    /**
     * 数组包含
     */
    public function ruleListContains($value, $field, $item)
    {
        $isAssoc = array_values($value) !== $value;
        if ($isAssoc) {
            $value = array_keys($value);
        }

        return in_array($item, $value);
    }

    /**
     * 值不在数组中
     */
    public function ruleNotIn($value, $field, ...$args)
    {
        return !$this->ruleIn($field, $value, ...$args);
    }

    /**
     * 字符串包含
     */
    public function ruleContains($value, $field, $subStr)
    {
        if (!is_string($subStr) || !is_string($value)) {
            return false;
        }

        if (function_exists('mb_strpos')) {
            $isContains = mb_strpos($value, $subStr) !== false;
        } else {
            $isContains = strpos($value, $subStr) !== false;
        }

        return $isContains;
    }

    /**
     * 子数组包含
     */
    public function ruleSubset($value, $field, array $parentList)
    {
        if (is_scalar($value) || $value === null) {
            return $this->ruleIn($field, $value, $parentList);
        }

        $intersect = array_intersect($value, $parentList);

        return array_diff($value, $intersect) === array_diff($intersect, $value);
    }

    /**
     * 数组有重复值
     */
    public function ruleContainsUnique($value)
    {
        if (!is_array($value)) {
            return false;
        }

        return $value === array_unique($value, SORT_REGULAR);
    }

    /**
     * IP
     */
    public function ruleIp($value)
    {
        return filter_var($value, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * IP4
     */
    public function ruleIpv4($value)
    {
        return filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
    }

    /**
     * IP6
     */
    public function ruleIpv6($value)
    {
        return filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    }

    /**
     * email
     */
    public function ruleEmail($value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * ascii
     */
    public function ruleAscii($value)
    {
        if (function_exists('mb_detect_encoding')) {
            return mb_detect_encoding($value, 'ASCII', true);
        }

        return 0 === preg_match('/[^\x00-\x7F]/', $value);
    }

    /**
     * url
     */
    public function ruleUrl($value)
    {
        $validUrlPrefixes = ['http://', 'https://'];
        foreach ($validUrlPrefixes as $prefix) {
            if (strpos($value, $prefix) !== false) {
                return filter_var($value, FILTER_VALIDATE_URL) !== false;
            }
        }

        return false;
    }

    /**
     * 纯字母
     */
    public function ruleAlpha($value)
    {
        return preg_match('/^([a-z])+$/i', $value);
    }

    /**
     * 纯字母+数字
     */
    public function ruleAlphaNum($value)
    {
        return preg_match('/^([a-z0-9])+$/i', $value);
    }

    /**
     * 纯字母+数字+'-'+'_'
     */
    public function ruleAlphaDash($value)
    {
        if (is_array($value)) {
            return false;
        }

        return preg_match('/^([-a-z0-9_])+$/i', $value);
    }

    /**
     * 正则
     */
    public function ruleRegex($value, $field, $regex)
    {
        return preg_match($regex, $value);
    }

    /**
     * 日期
     */
    public function ruleDate($value)
    {
        $isDate = false;
        if ($value instanceof DateTime) {
            $isDate = true;
        } else {
            $isDate = strtotime($value) !== false;
        }

        return $isDate;
    }

    /**
     * 指定格式日期
     */
    public function ruleDateFormat($value, $field, $format)
    {
        $parsed = date_parse_from_format($format, $value);

        return $parsed['error_count'] === 0 && $parsed['warning_count'] === 0;
    }

    /**
     * 日期之前
     */
    public function ruleBefore($value, $field, $thatValue)
    {
        $time1 = ($value instanceof DateTime) ? $value->getTimestamp() : strtotime($value);
        $time2 = ($thatValue instanceof DateTime) ? $thatValue->getTimestamp() : strtotime($thatValue);

        return $time1 < $time2;
    }

    /**
     * 日期之后
     */
    public function ruleAfter($value, $field, $thatValue)
    {
        $time1 = ($value instanceof DateTime) ? $value->getTimestamp() : strtotime($value);
        $time2 = ($thatValue instanceof DateTime) ? $thatValue->getTimestamp() : strtotime($thatValue);

        return $time1 > $time2;
    }

    /**
     * 日期跨度不大于
     */
    public function ruleTimeScopeMax($value, $field, $another, $period)
    {
        $another = $this->validator->getValue($another);

        return strtotime($value) - strtotime($another) <= $period;
    }

    /**
     * 日期跨度不小于
     */
    public function ruleTimeScopeMin($value, $field, $another, $period)
    {
        $another = $this->validator->getValue($another);

        return strtotime($value) - strtotime($another) >= $period;
    }

    /**
     * 验证的字段必须可以转换为 Boolean 类型
     */
    public function ruleBoolean($value)
    {
        return in_array($value, [true, false, 1, 0, '1', '0']);
    }

    /**
     * 对象类型归属
     */
    public function ruleInstanceOf($value, $field, $thatValue)
    {
        $isInstanceOf = false;
        if (is_object($value)) {
            if (is_object($thatValue) && $value instanceof $thatValue) {
                $isInstanceOf = true;
            }
            if (get_class($value) === $thatValue) {
                $isInstanceOf = true;
            }
        }
        if (is_string($value)) {
            if (is_string($thatValue) && get_class($value) === $thatValue) {
                $isInstanceOf = true;
            }
        }

        return $isInstanceOf;
    }

    /**
     * 转分隔字符串为数组
     */
    public function ruleToArray($value, $field, $type = 1)
    {
        $separateSymbols = [
            1 => ',',
            2 => ';',
            3 => '|',
        ];

        $valueArr = array_filter(explode($separateSymbols[$type], $value), function ($value) {
            return !Helper::blank($value);
        }) ?: [];

        $this->validator->setValue($field, $valueArr);

        return true;
    }

    /**
     * 多个逗号分隔的相同规则验证，并替换成数组
     */
    public function ruleItem($valueArr, $field, $anotherRule, ...$args)
    {
        $anotherRule = implode(array_map(function ($item) {
            return ucwords($item);
        }, explode('_', $anotherRule)));
        $method = 'rule'.ucwords($anotherRule);

        foreach ($valueArr as $item) {
            if (!call_user_func([$this, $method], $field, $item, ...$args)) {
                return false;
            }
        }

        return true;
    }

    /**
     * 当字段2在时，字段1也要在
     */
    public function ruleRequiredWith($value, $field, $another)
    {
        return !Helper::blank($this->validator->getValue($another)) && !Helper::blank($value);
    }

    /**
     * 当字段2不在时，字段1要在
     */
    public function ruleRequiredWithout($value, $field, $another)
    {
        return Helper::blank($this->validator->getValue($another)) && !Helper::blank($value);
    }

    /**
     * 默认值
     */
    public function ruleDefault($value, $field, $default = '')
    {
        if (Helper::blank($value)) {
            switch ($default) {
                case '0':
                    $default = 0;
                    break;
                case 'null':
                    $default = null;
                    break;
            }

            $this->validator->setValue($field, $default);
        }

        return true;
    }

    /**
     * datetime start
     */
    public function ruleToDateTimeStart($value, $field)
    {
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value)) {
            $this->validator->setValue($field, $value.' 00:00:00');
        }

        return true;
    }

    /**
     * datetime end
     */
    public function ruleToDateTimeEnd($value, $field)
    {
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value)) {
            $this->validator->setValue($field, $value.' 23:59:59');
        }

        return true;
    }

    /**
     * 默认值null
     */
    public function ruleAssign($value, $field, $assignValue = '')
    {
        switch ($assignValue) {
            case '0':
                $assignValue = 0;
                break;
            case 'null':
                $assignValue = null;
                break;
        }
        $this->validator->setValue($field, $assignValue);

        return true;
    }

    /**
     * 赋值于另一字段的值
     */
    public function ruleAssignWith($value, $field, $another)
    {
        $this->validator->setValue($field, $this->validator->getValue($another));

        return true;
    }

    /**
     * 可选
     */
    public function ruleOptional($value)
    {
        return true;
    }

    /**
     * 字符长度
     */
    protected function stringLength($value)
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($value);
        }

        return strlen($value);
    }
}
