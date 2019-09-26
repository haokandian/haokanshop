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
namespace app\plugins\distribution\admin;

use think\Controller;
use app\service\PaymentService;
use app\plugins\distribution\service\BusinessService;

/**
 * 分销 - 分销订单
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Order extends Controller
{
    /**
     * 首页
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-07T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function index($params = [])
    {
        // 分页
        $number = MyC('admin_page_number', 10, true);

        // 条件
        $where = BusinessService::UserOrderWhere($params);

        // 获取总数
        $total = BusinessService::UserOrderTotal($where);

        // 分页
        $page_params = array(
                'number'    =>  $number,
                'total'     =>  $total,
                'where'     =>  $params,
                'page'      =>  isset($params['page']) ? intval($params['page']) : 1,
                'url'       =>  PluginsAdminUrl('distribution', 'order', 'index'),
            );
        $page = new \base\Page($page_params);
        $this->assign('page_html', $page->GetPageHtml());

        // 获取列表
        $data_params = array(
            'm'         => $page->GetPageStarNumber(),
            'n'         => $number,
            'where'     => $where,
            'user_type' => 'admin',
        );
        $data = BusinessService::UserOrderList($data_params);
        $this->assign('data_list', $data['data']);

        // 静态数据
        $this->assign('common_is_text_list', lang('common_is_text_list'));
        $this->assign('common_platform_type', lang('common_platform_type'));
        $this->assign('common_order_admin_status', lang('common_order_admin_status'));

        // 支付方式
        $this->assign('payment_list', PaymentService::PaymentList());

        // 参数
        $this->assign('params', $params);
        return $this->fetch('../../../plugins/view/distribution/admin/order/index');
    }
}
?>