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
namespace app\plugins\distribution;

use think\Controller;
use app\plugins\distribution\service\ProfitService;

/**
 * 分销 - 钩子入口
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Hook extends Controller
{
    /**
     * 应用响应入口
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-09T14:25:44+0800
     * @param    [array]       $params [输入参数]
     */
    public function run($params = [])
    {
        if(!empty($params['hook_name']))
        {
            $ret = '';
            switch($params['hook_name'])
            {
                // 用户中心左侧导航
                case 'plugins_service_users_center_left_menu_handle' :
                    $ret = $this->UserCenterLeftMenuHandle($params);
                    break;

                // 顶部小导航右侧-我的商城
                case 'plugins_service_header_navigation_top_right_handle' :
                    $ret = $this->CommonTopNavRightMenuHandle($params);
                    break;

                // 订单完成新增收益
                case 'plugins_service_order_status_change_history_success_handle' :
                    if(!empty($params['data']) && isset($params['data']['new_status']) && $params['data']['new_status'] == 4 && !empty($params['order_id']))
                    {
                        $ret = ProfitService::OrderProfitInc($params);
                    }
                    break;

                // 订单售后审核成功
                case 'plugins_service_order_aftersale_audit_handle_end' :
                    $ret = ProfitService::OrderChange($params);
                    break;

                default :
                    $ret = '';
            }
            return $ret;
        }
    }

    /**
     * 用户中心左侧菜单处理
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-04-11
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function UserCenterLeftMenuHandle($params = [])
    {
        $params['data']['business']['item'][] = [
            'name'      =>  '我的分销',
            'url'       =>  PluginsHomeUrl('distribution', 'index', 'index'),
            'contains'  =>  ['indexindex', 'profitindex', 'orderindex', 'teamindex', 'posterindex', 'introduceindex'],
            'is_show'   =>  1,
            'icon'      =>  'am-icon-share-alt',
        ];
    }

    /**
     * 顶部小导航右侧-我的商城
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-04-11
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function CommonTopNavRightMenuHandle($params = [])
    {
        array_push($params['data'][1]['items'], [
            'name'  => '我的分销',
            'url'   => PluginsHomeUrl('distribution', 'index', 'index'),
        ]);
    }
}
?>