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
use app\plugins\distribution\service\BaseService;

/**
 * 海报 - 管理
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Poster extends Controller
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
        $ret = BaseService::PosterData();
        if($ret['code'] == 0)
        {
            // 边框样式
            $this->assign('distribution_border_style_list', BaseService::$distribution_border_style_list);

            // 是否开启
            $this->assign('distribution_is_enable_list', BaseService::$distribution_is_enable_list);

            $this->assign('data', $ret['data']);
            $this->assign('params', $params);
            return $this->fetch('../../../plugins/view/distribution/admin/poster/index');
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
        return BaseService::PpsterDataSave($params);
    }

    /**
     * 海报清空
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-12T20:34:52+0800
     * @param    [array]          $params [输入参数]
     */
    public function delete($params = [])
    {
        return BaseService::PpsterDelete($params);
    }
}
?>