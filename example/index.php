<?php

use EnjoyValidator\Validator;

require dirname(__DIR__).'/vendor/autoload.php';

Validator::globalMessageTpls([
    'required' => '{field}不能为空',
    'number' => '{field}只能包含纯数字',
    'in' => '{field}不正确',
    'item' => '{field}不正确',
    'min' => '{field}最小{0}',
    'max' => '{field}最大{0}',
    'between' => '{field}须在{0}和{1}之间',
    'date' => '{field}须是日期格式',
    'email' => '{field}须是邮箱格式',
    'gt' => '{field}须大于{0}',
    'gte' => '{field}须大于等于{0}',
    'lt' => '{field}须小于{0}',
    'lte' => '{field}须大于等于{0}',
]);

Validator::globalAlias([
    'user_id' => '用户id',
    'order_id' => '订单id',
    'email' => '邮箱',
    'start_date' => '开始日期',
    'end_date' => '结束日期',
]);

try {
    $data = Validator::make([
        'order_id' => 123456789,
        'user_ids' => '11,12,13',
        'email' => '1194316669@qq.com',
    ])->handle([
        'order_id' => 'required|number',
        'start_date' => 'date|to_date_time_start',
        'end_date' => 'required|date',
        'optional_field' => 'optional',
        'default_field' => 'default:1',
        'user_ids' => 'to_array|item:number'
    ])->data();
    print_r($data);
} catch (Exception $e) {
    echo $e->getMessage(). PHP_EOL;
}
