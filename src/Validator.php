<?php

namespace EnjoyValidator;

use InvalidArgumentException;

class Validator
{
    /**
     * 数据
     */
    private $data;

    /**
     * 规则处理器
     */
    private $ruleHandler;

    /**
     * 所有验证规则
     */
    private $ruleGroup = [];

    /**
     * 字段验证规则信息
     */
    private $fields = [];

    /**
     * 全局字段别名 + 当前字段别名
     */
    private $alias = [];

    /**
     * 全局字段别名
     */
    private static $globalAlias = [];

    /**
     * 规则处理器获取方法
     */
    private static $ruleResolver = null;

    /**
     * 外部注册的规则
     */
    private static $registeredRules = [];

    /**
     * 消息
     */
    private $messages = [];

    /**
     * 全局字段别名
     */
    private static $globalMessages = [];

    /**
     * 全局消息模板
     */
    private static $globalMessageTpls = [];

    /**
     * 全局消息模板 + 当前消息模板
     */
    private $messageTpls = [];

    /**
     * 直接采取抛异常的方式
     */
    private $throwErr = false;

    /**
     * 错误信息
     */
    private $errors = [];

    /**
     * 当字段为空时，也必须验证的规则
     */
    const MUST = [
        'required',
        'required_with',
        'required_without',

        'to_array',
        'default',
        'null',
    ];

    static $registeredMust = [];

    public function __construct($data)
    {
        $this->data = $data;
        $this->ruleHandler = self::$ruleResolver ? call_user_func(self::$ruleResolver, $this) : new Rule($this);

        $this->messages = self::$globalMessages;
        $this->alias = self::$globalAlias;
        $this->messageTpls = self::$globalMessageTpls;
    }

    /**
     * 工厂方法
     */
    public static function make($data, $rules = [], $messages = [], $alias = [])
    {
        $v = new self($data);

        if ($messages) {
            $v->messages($messages);
        }

        if ($rules) {
            $v->rules($rules);
        }

        if ($alias) {
            $v->alias($alias);
        }

        return $v;
    }

    /**
     * 获取数据值
     */
    public function getValue($field)
    {
        return isset($this->data[$field]) ? $this->data[$field] : null;
    }

    /**
     * 设置数据值
     */
    public function setValue($field, $value = null)
    {
        $this->data[$field] = $value;

        return $this;
    }

    /**
     * 只获取已经经过验证处理的数据
     */
    public function data()
    {
        return array_intersect_key($this->data, $this->fields);
    }

    /**
     * 获取全部的数据
     */
    public function allData()
    {
        return $this->data;
    }

    /**
     * 以字段为维度进行验证
     * @throws InvalidArgumentException
     */
    public function onField($field, $rule, ...$args)
    {
        $this->fields[$field][$rule] = $args;

        $value = $this->getValue($field);
        if (Helper::blank($value) && !in_array($rule, array_merge(self::MUST, self::$registeredMust))) return;

        if ($this->valid($field, $value, $rule, $args)) return;

        /**
         * 根据规则得到错误提示信息
         */
        $msg = "{$field} cannot pass the rule of {$rule}";
        $msgKey = $field . '.' . $rule;
        if (isset($this->messages[$msgKey])) {
            $msg = $this->messages[$msgKey];
        } elseif (isset($this->messages[$field])) {
            $msg = $this->messages[$field];
        } elseif (isset($this->messageTpls[$rule])) {
            $fieldName = isset($this->alias[$field]) ? $this->alias[$field] : $field;
            $args['args'] = implode(',', $args);
            $args['field'] = $fieldName;
            $msg = preg_replace_callback('/{([a-z\d_]+)}/', function ($matches) use ($args) {
                $key = $matches[1];
                if ('other' === $key) {
                    $other = $args[0];
                    return isset($this->alias[$other]) ? $this->alias[$other] : $other;
                }
                return $args[$key];
            }, $this->messageTpls[$rule]);
        }

        if ($this->throwErr) {
            throw new InvalidArgumentException($msg);
        }

        $this->errors[$field][] = $msg;
    }

    /**
     * 设置规则数据
     */
    public function rules($ruleGroup)
    {
        $this->ruleGroup = $ruleGroup;

        return $this;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function handle()
    {
        $ruleGroup = $this->ruleGroup;

        if (!$ruleGroup)  return $this;

        foreach ($ruleGroup as $field => $rules) {
            if (is_string($rules)) {
                $rules = explode('|', $rules);
            }
            foreach ($rules as $key => $rule) {
                list($rule, $args) = $this->parseRule($rule, $key);
                $this->onField($field, $rule, ...$args);
            }
        }

        return $this;
    }

    /**
     * 条件验证
     */
    public function when($conditions, $thenCb, $elseCb = null)
    {
        $valid = true;
        if (is_array($conditions)) {
            foreach ($conditions as $field => $rules) {
                if (is_string($rules)) {
                    $rules = explode('|', $rules);
                }
                foreach ($rules as $key => $rule) {
                    list($rule, $args) = $this->parseRule($rule, $key);
                    $value = $this->getValue($field);
                    if (!$this->valid($field, $value, $rule, $args)) {
                        $valid = false;
                        break;
                    }
                }
            }
        } else {
            $valid = (bool) $conditions;
        }

        if ($valid) {
            call_user_func($thenCb, $this);
        } elseif ($elseCb) {
            call_user_func($elseCb, $this);
        }

        return $this;
    }

    /**
     * 通过验证
     */
    protected function valid($field, $value, $rule, $args)
    {
        if (isset(self::$registeredRules[$rule])) {
            return call_user_func(self::$registeredRules[$rule], $value, $field, $this, ...$args);
        }

        $method = 'rule'.ucwords(implode(array_map(function ($item) {
                return ucwords($item);
            }, explode('_', $rule))));

        return call_user_func([$this->ruleHandler, $method], $field, $value, ...$args);
    }

    /**
     * 解析规则
     */
    protected function parseRule($rule, $name = null)
    {
        if (is_int($name)) {
            $parts = explode(':', $rule);
            $rule = $parts[0];

            $args = isset($parts[1]) ? explode(',', $parts[1]) : [];
            $args = array_map(function ($arg) {
                $argLower = strtolower($arg);
                if (in_array($argLower, ['true', 'false'])) {
                    return $arg === 'true';
                }

                return $arg;
            }, $args);
        } else {
            list($rule, $args) = [$name, [$rule]];
        }

        return [$rule, $args];
    }

    /**
     * 获取字段的验证信息
     */
    public function field($field = null)
    {
        if (null === $field) {
            return $this->fields;
        }

        return isset($this->fields[$field]) ? $this->fields[$field] : [];
    }

    /**
     * @return bool
     */
    public function throwErr()
    {
        return $this->throwErr = true;
    }

    /**
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }

    public function firstError($field = null)
    {
        if ($field) {
            return $this->errors[$field][0] ?? false;
        }

        return reset($this->errors)[0] ?? false;
    }

    /**
     * 以验证规则为维度进行验证
     * @throws InvalidArgumentException
     */
    public function onRule($rule, $fields, ...$args)
    {
        $fields = (array) $fields;
        foreach ($fields as $field) {
            $this->onField($field, $rule, ...$args);
        }
    }

    /**
     * 设置字段别名
     */
    public function alias($alias)
    {
        $this->alias = array_merge($this->alias, $alias);

        return $this;
    }

    /**
     * 设置全局消息模板
     */
    public static function globalAlias($alias)
    {
        self::$globalAlias = $alias;
    }

    /**
     * 设置自定义消息
     */
    public function messages($message)
    {
        $this->messages = array_merge($this->messages, $message);

        return $this;
    }

    /**
     * 设置全局自定义消息
     */
    public static function globalMessages($tpl)
    {
        self::$globalMessages = $tpl;
    }

    /**
     * 设置消息模板
     */
    public function messageTpl($tpl)
    {
        $this->messageTpls = array_merge($this->messageTpls, $tpl);

        return $this;
    }

    /**
     * 设置全局消息模板
     */
    public static function globalMessageTpls($tpl)
    {
        self::$globalMessageTpls = $tpl;
    }

    /**
     * 规则处理器获取方法
     */
    public static function ruleResolver($resolver)
    {
        self::$ruleResolver = $resolver;
    }

    /**
     * 注入规则验证器
     */
    public static function registerRule($name, callable $rule, $must = false)
    {
        self::$registeredRules[$name] = $rule;

        if ($must) {
            self::$registeredMust[] = $name;
        }
    }
}