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

use app\service\PaymentService;
use app\service\OrderService;
use app\plugins\distribution\service\BusinessService;
/**
 * 我的订单
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Team extends Common
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

        // 是否登录
        $this->IsLogin();
    }
    
    /**
     * [Index 获取订单列表]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2017-02-22T16:50:32+0800
     */
    public function Index()
    {
        // 参数
        $params = $this->data_post;
        $params['user'] = $this->user;
        $params['user_type'] = 'user';

        // 分页
        $number = 10;
        $page = max(1, isset($this->data_post['page']) ? intval($this->data_post['page']) : 1);
        // 条件
        $where = BusinessService::UserTeamWhere($params);

        // 获取总数
        $total = BusinessService::UserTeamTotal($where);

        $page_total = ceil($total/$number);
        $start = intval(($page-1)*$number);

        // 分页
      $page_params = array(
            'number'    =>  $number,
            'total'     =>  $total,
            'where'     =>  $params,
            'page'      =>  isset($params['page']) ? intval($params['page']) : 1,
            //'url'       =>  PluginsHomeUrl('distribution', 'team', 'index'),
        );

        //$page = new \base\Page($page_params);
       // $this->assign('page_html', $page->GetPageHtml());

        // 获取列表
        $data_params = array(
            'm'         => $start,
            'n'         => $number,
            'where'     => $where,
        );
        $data = BusinessService::UserTeamList($data_params);
        //$this->assign('data_list', $data['data']);




        // 参数
        $this->assign('params', $params);



        // 返回数据
        $result = [
            'total'             =>  $total,
            'page_total'        =>  $page_total,
            'data'              =>  $data['data'],
            'start'   =>              $start
          //  'payment_list'      =>  $payment_list,
        ];
        return DataReturn('success', 0, $result);

    }

    /**
     * 首页
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-07T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function  profit($params = [])
    {
        // 用户
        $params['user'] = $this->user;
        $params['user_type'] = 'user';

        // 分页
        $number = 10;
        $page = max(1, isset($this->data_post['page']) ? intval($this->data_post['page']) : 1);

        // 条件
        $where = BusinessService::UserProfitWhere($params);

        // 获取总数
        $total = BusinessService::UserProfitTotal($where);
        $page_total = ceil($total/$number);
        $start = intval(($page-1)*$number);

        // 分页
        $page_params = array(
            'number'    =>  $number,
            'total'     =>  $total,
            'where'     =>  $params,
            'page'      =>  isset($params['page']) ? intval($params['page']) : 1,
            //'url'       =>  PluginsHomeUrl('distribution', 'profit', 'index'),
        );
       // $page = new \base\Page($page_params);
      //  $this->assign('page_html', $page->GetPageHtml());

        // 获取列表
        $data_params = array(
            'm'         =>$start,
            'n'         => $number,
            'where'     => $where,
        );
        $data = BusinessService::UserProfitList($data_params);
       // $this->assign('data_list', $data['data']);



        // 返回数据
        $result = [
            'total'             =>  $total,
            'page_total'        =>  $page_total,
            'data'              =>  $data['data'],
            'start'   =>              $start
            //  'payment_list'      =>  $payment_list,
        ];
        return DataReturn('success', 0, $result);


        // 参数
    //    $this->assign('params', $params);
      //  return $this->fetch('../../../plugins/view/distribution/index/profit/index');
    }


}
?>