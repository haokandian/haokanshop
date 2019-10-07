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
          $row =   Db::name('store')->where($where)->find();


          $goods_array =   json_decode($row['goods'],true);

          foreach( $goods_array as &$good){
              if(!empty($good['goods_id']))
               $good['goods']  =  Db::name('goods')->field("id,title,images")->where(['id'=> $good['goods_id'] ])->select();


          }

        $row['goods'] = json_encode($goods_array,320);

        return  $row;
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





    static  function delContent($params=[]){
        if(!empty($params['id']))  $data['id'] = $params['id'];
        if(!empty($params['type']))   $data['type'] = $params['type'];
        if(!empty($params['cname']))  $data['cname'] = $params['cname'];
        if(!empty($params['goods_id']))   $data['goods_id'] = $params['goods_id'];
        if(!empty($params['uid']))  $data['uid'] = $params['uid'];
        $del =false;


        $row =   Db::name('store')->where(['uid'=> $data['uid']])->find();


        if( $params['type'] =="cname" ){

            $good_array = json_decode($row["goods"],true);
              foreach($good_array as $key => &$cate){
                   if($cate['cname'] == $data['cname'] ){

                       if(count( $cate['goods_id'] )  > 0){
                           return DataReturn('删除失败，请先删除分类下的商品',-1);
                       }


                     unset($good_array[$key]);
                     $del =true;
                   }

              }
             $good_array=    array_values($good_array);

              $good_string = json_encode($good_array,320);


              if($del == false){
                  return DataReturn('删除失败，系统可能无此分类',-1);
              }
             //删除分类
            $data = Db::name('store')->where([ 'uid'=>intval($params['uid']) ])->update(['goods'=>$good_string]);
           if($data){
               return DataReturn('删除成功',0);
           }

        }

        if( $params['type'] == "goods" ){
            $del_good_array =   explode(",",$data['goods_id']);
            $store_row = Db::name('store')->where([ 'uid'=>intval($params['uid']) ])->find();

            $good_array = json_decode($row["goods"],true);
            $del =false;
            foreach($good_array as $g_k=> &$cate){
                 foreach($del_good_array as  $j=>&$key){ //循环要删除的goods

                    if( !empty($cate['goods_id'])  ){

                        foreach($cate['goods_id']  as  $i=>&$item){
                            if($item == $key){
                               // var_dump($item);
                                unset($del_good_array[$j]); //清除他们
                                unset($good_array[$g_k]['goods_id'][$i] );
                              $del =   Db::name('store_goods')->where([ 'sid'=>intval($store_row['id']),'gid'=>intval($item) ])->delete();
                            }
                        }
                    }
                }


            }
            $good_array=    array_values($good_array);

            $good_string = json_encode($good_array,320);

            //删除分类
            $data = Db::name('store')->where([ 'uid'=>intval($params['uid']) ])->update(['goods'=>$good_string]);
            if($del){
              return DataReturn('删除成功',0);
            }else{
                return DataReturn('删除不成功',-1);
            }
        }



    }





    /*
     *
     *
     *
     *
     * {
     *  [ cid: 大衣，
     * "good_id":["6","6","5","11"]]
     * ,
     *   cid:"歹意"
     *   "歹意":[],"短裤":["11"],"短裤二":["11"]}
     *
     *
     *
     * */

    static  function  addGoods($params=[]){
        if(!empty($params['cate_name']))  $data['cate_name'] = $params['cate_name'];
        if(!empty($params['goods_id']))  $data['goods_id'] = $params['goods_id'];
        if(!empty($params['uid']))  $data['uid'] = $params['uid'];
        $store_data = Db::name('store')->where([ 'uid'=>intval( $data['uid']) ])->find();
          //先查阅是否已经有此商品
        $store_goods = Db::name('store_goods')->where([ 'sid'=>intval( $store_data['id'] ) ,'gid'=> intval( $data['goods_id'] )   ])->find();
        if($store_goods){
            return DataReturn('已有此商品，不许添加', -1);
        }
        $goods_array = json_decode($store_data['goods'],true);


        //如果没有分类名称取最后一个分类做为名称
        if(empty($params['cate_name'])){
            $length =  count($goods_array);
            $data['cate_name'] = $params['cate_name'] = $goods_array[$length-1]['cname'];
        }


        $find = false;
        foreach($goods_array as &$item ){
            if(!empty($item['cname']) && $item['cname'] == $data['cate_name'] ){
                $find =true;
                $item['goods_id'][] = $data['goods_id'] ;

            }
        }

        if($find ==false) $goods_array[]=['cname'=>$data['cate_name'] ,'goods_id'=>[ $data['goods_id'] ] ];


        //写入到store goods 表
       Db::name('store_goods')->insert(['sid'=>$store_data['id'] ,'gid'=> intval( $data['goods_id'] )]);


        $goods_string = json_encode($goods_array,320);
        return  Db::name('store')->where([ 'uid'=>intval($params['uid']) ])->update( ['goods'=> $goods_string ]);

    }


    static  function  addCate($params=[]){
        if(!empty($params['cate_name']))  $data['cate_name'] = $params['cate_name'];
        if(!empty($params['uid']))  $data['uid'] = $params['uid'];
        $store_data = Db::name('store')->where([ 'uid'=>intval( $data['uid']) ])->find();

        $goods_array = json_decode($store_data['goods'],true);
        if( array_key_exists($data['cate_name'] ,$goods_array ) ){
            return DataReturn('已经有此状态', -1);
        }


        foreach($goods_array as &$item ){
            if(!empty($item['cname']) && $item['cname'] == $data['cate_name'] ){
                $find =true;
                //$item['goods_id'][] = $data['goods_id'] ;
                return DataReturn('已经有此状态', -1);

            }
        }



        $goods_array[]=['cname'=>$data['cate_name'] ,'goods_id'=>[] ];
        $update['goods'] =json_encode($goods_array,320);
        return   $data = Db::name('store')->where([ 'uid'=>intval($params['uid']) ])->update($update);
    }

}
?>