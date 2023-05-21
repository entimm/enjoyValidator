# 项目介绍

EnjoyValidator是一个简单而强大的PHP验证器，用于验证数据的有效性和一致性。它提供了一套丰富的验证规则和灵活的验证方式，并可以根据规则进行数据转换。

特点：
- 简单易用：使用简洁的语法和直观的方法调用进行数据验证。
- 灵活多样：支持多种验证规则，包括必填字段、相等比较、大小比较、数组验证、JSON验证等。
- 可扩展性：支持自定义规则和消息模板，可以根据具体项目的需求进行扩展和定制。
- 错误处理：提供详细的错误信息和异常处理机制，方便开发人员进行错误处理和调试。

# 使用说明

1. 创建`Validator`对象

```php
$data = [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    // 其他数据字段...
];

$validator = new \EnjoyValidator\Validator($data);
```

2. 设置验证规则

可以通过`rules()`方法来设置验证规则，规则可以是一个关联数组，其中键表示要验证的字段，值表示该字段的验证规则。规则可以是字符串，多个规则可以用管道符（`|`）分隔，也可以是数组形式的规则。

```php
$rules = [
    'name' => 'required',
    'email' => 'required|email',
    'phone_no' => ['length_min' => 10],
    'default_field' => 'default:1',
    // 其他字段的验证规则...
];

$validator->rules($rules);
```

3. 执行验证

可以通过调用`handle()`方法来执行验证，该方法会根据设置的验证规则对数据进行验证，并将验证结果保存在验证器对象中。

```php
$validator->handle();
```

4. 获取验证结果

可以通过以下方法获取验证结果：

- `errors()`：获取所有字段的错误信息，返回一个关联数组，键是字段名，值是一个包含错误消息的数组。
- `firstError($field)`：获取指定字段的第一个错误消息，如果字段没有错误，则返回`false`。
- `throwErr()`：设置验证器在出现错误时抛出异常。

```php
$errors = $validator->errors();
$firstError = $validator->firstError('email');

if ($errors) {
    // 处理验证错误...
}
```

5. 自定义验证规则

可以使用`registerRule()`方法来注册自定义的验证规则。该方法接受三个参数：规则名称、验证规则的回调函数和一个可选的参数，指示该规则是否是必须验证的规则。

```php
\EnjoyValidator\Validator::registerRule('custom_rule', function ($value, $field, $validator) {
    // 自定义验证逻辑...
    return true; // 验证通过
});

$validator->rules([
    'field' => 'custom_rule',
]);
```

6. 其他功能

- 设置字段别名：可以使用`alias()`方法来设置字段的别名，方便在错误消息中使用更友好的字段名。
- 设置自定义消息：可以使用`messages()`方法来设置字段的自定义错误消息。
- 设置消息模板：可以使用`messageTpl()`方法来设置验证规则的自定义错误消息模板。

## 使用示例

以下是一个简单的使用示例：

```php
use EnjoyValidator\Validator;

// 要验证的数据
$data = [
    'username' => 'john_doe',
    'email' => 'john@example.com',
];

// 设置验证规则
$rules = [
    'username' => 'required|max:20',
    'email' => 'required|email',
];

// 创建验证器
$validator = Validator::make($data, $rules);

// 执行验证
$validator->handle();

// 获取错误信息
$errors = $validator
```

完整示例代码见 [/example/index.php](/example/index.php)