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
namespace app\api\controller;

use app\service\SearchService;
use app\service\GoodsService;
use app\service\UserStoreService;

/**
 * 商品搜索
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Store extends Common
{
    /**
     * [__construct 构造方法]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-03T12:39:08+0800
     */
    public function __construct()
    {
        // 调用父类前置方法
        parent::__construct();
    }

    /**
     * 小店查询
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-07-12
     * @desc    description
     */
    public  function Index()
    {
        // 搜索记录
        $this->data_post['user_id'] = isset( $this->data_request['user_id']) ?  $this->data_request['user_id'] : 0;

        $goods =  UserStoreService::getContent($this->data_post['user_id']);

        return $goods;
       // SearchService::SearchAdd($this->data_post);

        /*
        // 获取数据
        $ret = SearchService::GoodsList($this->data_post);

        // 分类
        if(!empty($this->data_post['category_id']))
        {
            $ret['data']['category'] = GoodsService::GoodsCategoryRow(['id'=>$this->data_post['category_id']]);
        } else {
            $ret['data']['category'] = [];
        }

        return $ret;
        */
    }


    /**
     * 小店设置
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-07-12
     * @desc    description
     */
    public  function Set()
    {

        // 搜索记录
        $this->data_post['user_id'] = isset( $this->data_post['user_id']) ?  $this->data_post['user_id'] : 0;

        $goods =  UserStoreService::getContent($this->data_post['user_id']);

        return $goods;

    }
}
?>