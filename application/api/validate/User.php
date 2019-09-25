<?php


namespace app\api\validate;


use think\Validate;

class User extends Validate
{
    protected $rule = [

        // 手机号码验证如下
//        'phoneNumber' => 'require|length:11|regex:/^1([38][0-9]|4[579]|5[0-3,5-9]|6[6]|7[0135678]|9[89])\d{8}$/'
        'mobile' => 'require|max:11|/^1[3-8]{1}[0-9]{9}$/'
    ];
}