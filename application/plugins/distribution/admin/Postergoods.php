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
 * 商品海报 - 管理
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class PosterGoods extends Controller
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
        $pp = [
            'goods_id'  => 7,
        ];
        $ret = BaseService::PosterGoodsData();
        if($ret['code'] == 0)
        {
            $this->assign('data', $ret['data']);
            $this->assign('params', $params);
            return $this->fetch('../../../plugins/view/distribution/admin/postergoods/index');
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
        return BaseService::PpsterGoodsDataSave($params);
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
        return BaseService::PpsterGoodsDelete($params);
    }
}
?>