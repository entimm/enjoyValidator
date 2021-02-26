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
    public function ruleRequired($field, $value)
    {
        return !Helper::blank($value);
    }

    /**
     * 值相等
     */
    public function ruleEq($field, $value, $thatValue)
    {
        return $value == $thatValue;
    }

    /**
     * 值不相等
     */
    public function ruleNotEq($field, $value, $thatValue)
    {
        return $value != $thatValue;
    }

    /**
     * 小于
     */
    public function ruleLt($field, $value, $thatValue)
    {
        return $value < $thatValue;
    }

    /**
     * 小于等于
     */
    public function ruleLte($field, $value, $thatValue)
    {
        return $value <= $thatValue;
    }

    /**
     * 大于
     */
    public function ruleGt($field, $value, $thatValue)
    {
        return $value > $thatValue;
    }

    /**
     * 大于等于
     */
    public function ruleGte($field, $value, $thatValue)
    {
        return $value >= $thatValue;
    }

    /**
     * 同意
     */
    public function ruleAccepted($field, $value)
    {
        $acceptable = array('yes', 'on', 1, '1', true);

        return !Helper::blank($value) && in_array($value, $acceptable, true);
    }

    /**
     * 数组类型
     */
    public function ruleArr($field, $value)
    {
        return is_array($value);
    }

    /**
     * json字符串
     */
    public function ruleJson($field, $value)
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
    public function ruleNumeric($field, $value)
    {
        return is_numeric($value);
    }

    /**
     * 整数（可负数）
     */
    public function ruleInteger($field, $value)
    {
        return preg_match('/^([0-9]|-?[1-9][0-9]*)$/i', $value);
    }

    /**
     * 纯数字（非负数）
     */
    public function ruleNumber($field, $value)
    {
        return preg_match('/^([0-9]|[1-9][0-9]*)$/i', $value);
    }

    /**
     * 多个非负纯数字，逗号分隔
     * 可设置非负数
     */
    public function ruleMultiNumber($field, $value)
    {
        // 严格模式（不支持前缀+）
        return preg_match('/^(\d+,)*\d+$/', $value);
    }

    /**
     * 字符串长度相等
     */
    public function ruleLength($field, $value, $thatLength)
    {
        return $this->stringLength($value) == $thatLength;
    }

    /**
     * 数字不小于
     */
    public function ruleMin($field, $value, $min)
    {
        return $min <= $value;
    }

    /**
     * 数字不大于
     */
    public function ruleMax($field, $value, $max)
    {
        return $max >= $value;
    }

    /**
     * 区间（数字、数组计数、字符长度）
     */
    public function ruleBetween($field, $value, $min, $max)
    {
        return $max >= $value && $value >= $min;
    }

    /**
     * 字符长度不小于
     */
    public function ruleLengthMin($field, $value, $min)
    {
        return $min <= $this->stringLength($value);
    }

    /**
     * 字符长度不大于
     */
    public function ruleLengthMax($field, $value, $max)
    {
        return $max >= $this->stringLength($value);
    }

    /**
     * 字符长度区间
     */
    public function ruleLengthBetween($field, $value, $min, $max)
    {
        $length = $this->stringLength($value);

        return $max >= $length && $length >= $min;
    }

    /**
     * 数组项数不小于
     */
    public function ruleCountMin($field, $value, $min)
    {
        return $min <= count($value);
    }

    /**
     * 数组项数不大于
     */
    public function ruleCountMax($field, $value, $max)
    {
        return $max >= count($value);
    }

    /**
     * 数组项数区间
     */
    public function ruleCountBetween($field, $value, $min, $max)
    {
        $count = count($value);

        return $max >= $count && $count >= $min;
    }

    /**
     * 等于另一个字段
     */
    public function ruleEqThan($field, $value, $another)
    {
        return $value == $this->validator->getValue($another);
    }

    /**
     * 等于另一个字段
     */
    public function ruleNotEqThan($field, $value, $another)
    {
        return $value != $this->validator->getValue($another);
    }

    /**
     * 小于另一个字段
     */
    public function ruleLtThan($field, $value, $another)
    {
        return $value < $this->validator->getValue($another);
    }

    /**
     * 小于等于另一个字段
     */
    public function ruleLteThan($field, $value, $another)
    {
        return $value <= $this->validator->getValue($another);
    }

    /**
     * 大于另一个字段
     */
    public function ruleGtThan($field, $value, $another)
    {
        return $value > $this->validator->getValue($another);
    }

    /**
     * 大于等于另一个字段
     */
    public function ruleGteThan($field, $value, $another)
    {
        return $value >= $this->validator->getValue($another);
    }

    /**
     * 值在数组中
     */
    public function ruleIn($field, $value, ...$args)
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
    public function ruleListContains($field, $value, $item)
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
    public function ruleNotIn($field, $value, ...$args)
    {
        return !$this->ruleIn($field, $value, ...$args);
    }

    /**
     * 字符串包含
     */
    public function ruleContains($field, $value, $subStr)
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
    public function ruleSubset($field, $value, array $parentList)
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
    public function ruleContainsUnique($field, $value)
    {
        if (!is_array($value)) {
            return false;
        }

        return $value === array_unique($value, SORT_REGULAR);
    }

    /**
     * IP
     */
    public function ruleIp($field, $value)
    {
        return filter_var($value, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * IP4
     */
    public function ruleIpv4($field, $value)
    {
        return filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
    }

    /**
     * IP6
     */
    public function ruleIpv6($field, $value)
    {
        return filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    }

    /**
     * email
     */
    public function ruleEmail($field, $value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * ascii
     */
    public function ruleAscii($field, $value)
    {
        if (function_exists('mb_detect_encoding')) {
            return mb_detect_encoding($value, 'ASCII', true);
        }

        return 0 === preg_match('/[^\x00-\x7F]/', $value);
    }

    /**
     * url
     */
    public function ruleUrl($field, $value)
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
    public function ruleAlpha($field, $value)
    {
        return preg_match('/^([a-z])+$/i', $value);
    }

    /**
     * 纯字母+数字
     */
    public function ruleAlphaNum($field, $value)
    {
        return preg_match('/^([a-z0-9])+$/i', $value);
    }

    /**
     * 纯字母+数字+'-'+'_'
     */
    public function ruleAlphaDash($field, $value)
    {
        if (is_array($value)) {
            return false;
        }

        return preg_match('/^([-a-z0-9_])+$/i', $value);
    }

    /**
     * 正则
     */
    public function ruleRegex($field, $value, $regex)
    {
        return preg_match($regex, $value);
    }

    /**
     * 日期
     */
    public function ruleDate($field, $value)
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
    public function ruleDateFormat($field, $value, $format)
    {
        $parsed = date_parse_from_format($format, $value);

        return $parsed['error_count'] === 0 && $parsed['warning_count'] === 0;
    }

    /**
     * 日期之前
     */
    public function ruleBefore($field, $value, $thatValue)
    {
        $time1 = ($value instanceof DateTime) ? $value->getTimestamp() : strtotime($value);
        $time2 = ($thatValue instanceof DateTime) ? $thatValue->getTimestamp() : strtotime($thatValue);

        return $time1 < $time2;
    }

    /**
     * 日期之后
     */
    public function ruleAfter($field, $value, $thatValue)
    {
        $time1 = ($value instanceof DateTime) ? $value->getTimestamp() : strtotime($value);
        $time2 = ($thatValue instanceof DateTime) ? $thatValue->getTimestamp() : strtotime($thatValue);

        return $time1 > $time2;
    }

    /**
     * 日期跨度不大于
     */
    public function ruleTimeScopeMax($field, $value, $another, $period)
    {
        $another = $this->validator->getValue($another);

        return strtotime($value) - strtotime($another) <= $period;
    }

    /**
     * 日期跨度不小于
     */
    public function ruleTimeScopeMin($field, $value, $another, $period)
    {
        $another = $this->validator->getValue($another);

        return strtotime($value) - strtotime($another) >= $period;
    }

    /**
     * 验证的字段必须可以转换为 Boolean 类型
     */
    public function ruleBoolean($field, $value)
    {
        return in_array($value, [true, false, 1, 0, '1', '0']);
    }

    /**
     * 对象类型归属
     */
    public function ruleInstanceOf($field, $value, $thatValue)
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
    public function ruleToArray($field, $value, $type = 1)
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
    public function ruleItem($field, $valueArr, $anotherRule, ...$args)
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
    public function ruleRequiredWith($field, $value, $another)
    {
        return !Helper::blank($this->validator->getValue($another)) && !Helper::blank($value);
    }

    /**
     * 当字段2不在时，字段1要在
     */
    public function ruleRequiredWithout($field, $value, $another)
    {
        return Helper::blank($this->validator->getValue($another)) && !Helper::blank($value);
    }

    /**
     * 默认值
     */
    public function ruleDefault($field, $value, $default = '')
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
    public function ruleToDateTimeStart($field, $value)
    {
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value)) {
            $this->validator->setValue($field, $value.' 00:00:00');
        }

        return true;
    }

    /**
     * datetime end
     */
    public function ruleToDateTimeEnd($field, $value)
    {
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value)) {
            $this->validator->setValue($field, $value.' 23:59:59');
        }

        return true;
    }

    /**
     * 默认值null
     */
    public function ruleAssign($field, $value, $assignValue = '')
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
    public function ruleAssignWith($field, $value, $another)
    {
        $this->validator->setValue($field, $this->validator->getValue($another));

        return true;
    }

    /**
     * 可选
     */
    public function ruleOptional($field, $value)
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
