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
use app\service\PluginsService;
use app\plugins\distribution\service\BaseService;
use app\plugins\distribution\service\StatisticalService;

/**
 * 分销 - 管理
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
        $ret = PluginsService::PluginsData('distribution', BaseService::$base_config_attachment_field, false);
        if($ret['code'] == 0)
        {
            // 静态资源设置
            $this->setresources();

            // 图表-收益
            $profit = StatisticalService::UserProfitFifteenTodayTotal();
            $this->assign('profit_chart', $profit['data']);

            // 图表-推广用户
            $user = StatisticalService::UserExtensionFifteenTodayTotal();
            $this->assign('user_chart', $user['data']);

            $this->assign('data', $ret['data']);
            return $this->fetch('../../../plugins/view/distribution/admin/admin/index');
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
        $ret = PluginsService::PluginsData('distribution', BaseService::$base_config_attachment_field, false);
        if($ret['code'] == 0)
        {
            // 静态资源设置
            $this->setresources();

            $this->assign('data', $ret['data']);
            return $this->fetch('../../../plugins/view/distribution/admin/admin/saveinfo');
        } else {
            return $ret['msg'];
        }
    }

    /**
     * 静态资源设置
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-06-04
     * @desc    description
     * @return  [type]          [description]
     */
    private function setresources()
    {
        // 分销层级
        $this->assign('distribution_level_list', BaseService::$distribution_level_list);

        // 是否开启
        $this->assign('distribution_is_enable_list', BaseService::$distribution_is_enable_list);
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
        // 等级
        $level = BaseService::LevelDataList(['is_handle_data'=>0]);
        $params['level_list'] = $level['data'];

        // 海报
        $poster = BaseService::PosterData(['is_handle_data'=>0]);
        $params['poster_data'] = $poster['data'];
        return PluginsService::PluginsDataSave(['plugins'=>'distribution', 'data'=>$params], BaseService::$base_config_attachment_field);
    }
}
?>