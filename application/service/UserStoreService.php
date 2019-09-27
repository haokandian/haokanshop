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



    static  function setContent($params=[]){
        if(!empty($params['id']))  $data['id'] = $params['id'];
        if(!empty($params['store_name']))  $data['store_name'] = $params['store_name'];
        if(!empty($params['status']))   $data['status'] = $params['status'];
        if(!empty($params['goods']))  $data['goods'] = $params['goods'];
        if(!empty($params['banner']))   $data['banner'] = $params['banner'];
        if(!empty($params['uid']))  $data['uid'] = $params['uid'];

        if( empty($params['id'] ) ){ //添加操作
           return   $data = Db::name('store')->insertGetId($data);

        }else{ //有id 更新操作
            return   $data = Db::name('store')->where([ 'id'=>intval($params['id']) ])->update($data);
        }

    }

}
?>