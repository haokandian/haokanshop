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
namespace app\plugins\distribution\index;

use app\plugins\distribution\index\Common;
use app\plugins\distribution\service\BaseService;
use app\plugins\distribution\service\StatisticalService;

/**
 * 分销 - 首页
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Index extends Common
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
        // 用户收益总额
        $this->assign('user_profit_price', BaseService::UserProfitTotal($this->user['id']));

        // 图表-收益
        $profit = StatisticalService::UserProfitFifteenTodayTotal(['user'=>$this->user]);
        $this->assign('profit_chart', $profit['data']);

        // 图表-推广用户
        $user = StatisticalService::UserExtensionFifteenTodayTotal(['user'=>$this->user]);
        $this->assign('user_chart', $user['data']);
        
        $this->assign('params', $params);
        return $this->fetch('../../../plugins/view/distribution/index/index/index');
    }
}
?>