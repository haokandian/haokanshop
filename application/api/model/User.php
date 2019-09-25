<?php

namespace app\api\model;

use think\Db;
use think\Model;

class User extends Model
{
//    protected $table = 'user';

    public function phoneLogin( $data )
    {
        return Db::name('user')->where('mobile', $data )->find();
    }

    public function signin( $data )
    {
        return Db::name('user')->insert( $data );
    }
}