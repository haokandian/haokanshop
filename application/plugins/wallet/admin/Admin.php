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
namespace app\plugins\wallet\admin;

use think\Controller;
use app\service\PluginsService;
use app\plugins\wallet\service\BaseService;
use app\plugins\wallet\service\StatisticalService;

/**
 * 钱包插件 - 管理
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Admin extends Controller
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
        $ret = PluginsService::PluginsData('wallet', '', false);
        if($ret['code'] == 0)
        {            
            $this->assign('data', $ret['data']);

            // 统计数据
            $this->assign('statistical', StatisticalService::StatisticalData());

            return $this->fetch('../../../plugins/view/wallet/admin/admin/index');
        } else {
            return $ret['msg'];
        }
    }

    /**
     * 编辑页面
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-07T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function saveinfo($params = [])
    {
        $ret = PluginsService::PluginsData('wallet', '', false);
        if($ret['code'] == 0)
        {
            // 是否
            $is_whether_list =  [
                0 => array('value' => 0, 'name' => '否'),
                1 => array('value' => 1, 'name' => '是', 'checked' => true),
            ];
            $this->assign('is_whether_list', $is_whether_list);

            // 充值赠送类型
            $recharge_give_type_list =  [
                0 => array('value' => 0, 'name' => '固定金额', 'checked' => true),
                1 => array('value' => 1, 'name' => '比例'),
            ];
            $this->assign('recharge_give_type_list', $recharge_give_type_list);

            $this->assign('data', $ret['data']);
            return $this->fetch('../../../plugins/view/wallet/admin/admin/saveinfo');
        } else {
            return $ret['msg'];
        }
    }

    /**
     * 数据保存
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-07T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function save($params = [])
    {
        return PluginsService::PluginsDataSave(['plugins'=>'wallet', 'data'=>$params]);
    }
}
?>