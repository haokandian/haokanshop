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
use app\plugins\distribution\service\BusinessService;

/**
 * 分销 - 我的团队
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Team extends Common
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
        // 用户
        $params['user'] = $this->user;
        $params['user_type'] = 'user';
        
        // 分页
        $number = 10;

        // 条件
        $where = BusinessService::UserTeamWhere($params);

        // 获取总数
        $total = BusinessService::UserTeamTotal($where);

        // 分页
        $page_params = array(
                'number'    =>  $number,
                'total'     =>  $total,
                'where'     =>  $params,
                'page'      =>  isset($params['page']) ? intval($params['page']) : 1,
                'url'       =>  PluginsHomeUrl('distribution', 'team', 'index'),
            );
        $page = new \base\Page($page_params);
        $this->assign('page_html', $page->GetPageHtml());

        // 获取列表
        $data_params = array(
            'm'         => $page->GetPageStarNumber(),
            'n'         => $number,
            'where'     => $where,
        );
        $data = BusinessService::UserTeamList($data_params);
        $this->assign('data_list', $data['data']);

        // 参数
        $this->assign('params', $params);
        return $this->fetch('../../../plugins/view/distribution/index/team/index');
    }
}
?>