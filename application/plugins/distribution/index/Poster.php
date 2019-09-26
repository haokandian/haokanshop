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
use app\plugins\distribution\service\PosterService;

/**
 * 分销 - 海报
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Poster extends Common
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
        // 等级列表
        $poster = PosterService::UserPoster();

        // 海报更新版本号，防止缓存
        $user_poster_images_ver = session('user_poster_images_ver');
        if($user_poster_images_ver != null)
        {
            $poster['data'] .= '#ver='.$user_poster_images_ver;
        }
        $this->assign('poster', $poster);

        // 分享地址
        $this->assign('user_share_url', PosterService::UserShareUrl($this->user['id']));

        // 二维码地址
        $qrcode = PosterService::UserShareQrcodeCreate($this->user['id'], $this->user['add_time']);
        $this->assign('user_share_qrode', $qrcode['data']);
        
        $this->assign('params', $params);
        return $this->fetch('../../../plugins/view/distribution/index/poster/index');
    }

    /**
     * 海报刷新
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-08T23:28:49+0800
     * @param    [array]          $params [输入参数]
     */
    public function refresh()
    {
        // 是否ajax请求
        if(!IS_AJAX)
        {
            return $this->error('非法访问');
        }

        // 开始处理
        $params['user'] = $this->user;
        return PosterService::UserPosterRefresh($params);
    }
}
?>