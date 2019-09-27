<?php
// +----------------------------------------------------------------------
// | ShopXO 国内领先企业级B2C免费开源电商系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011~2019 http://shopxo.net All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Devil
// +----------------------------------------------------------------------
namespace app\service;

use think\Db;
use think\facade\Hook;
use app\service\RegionService;
use app\service\SafetyService;
use app\service\ResourcesService;

/**
 * 用户商店
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class UserStoreService
{
    static function getContent($uid){
          $where = ["uid"=>$uid ];
           return   Db::name('store')->where($where)->find();
      }

    static  function getContentByUid($uid){
          $where =['uid'=> $uid];
       $field ='s.id as sid , s.store_name ,  c.id as cid, c.cate_name ,g.id as sg_id ,g.gid ';
       return    $data = Db::name('store')->alias('s')->rightJoin(['__STORE_CATE__'=>'c'], 'c.sid=s.id')->rightJoin(['__STORE_GOODS__'=>'g'],'c.id = g.scid') ->field($field)->where($where)->select();
     //   return   Db::name('store')->where($where)->find();
    }
}
?>