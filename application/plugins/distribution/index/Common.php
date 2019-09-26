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

use think\Controller;
use app\service\UserService;
use app\service\PluginsService;
use app\plugins\distribution\service\BaseService;

/**
 * 分销 - 公共
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Common extends Controller
{
    protected $user;
    protected $user_level;
    protected $plugins_base;

    /**
     * 构造方法
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-03-15
     * @desc    description
     */
    public function __construct()
    {
        parent::__construct();

        // 用户信息
        $this->user = UserService::LoginUserInfo();

        // 登录校验
        if(empty($this->user))
        {
            if(IS_AJAX)
            {
                exit(json_encode(DataReturn('登录失效，请重新登录', -400)));
            } else {
                return $this->redirect('index/user/logininfo');
            }
        }

        // 用户分销等级
        $user_level = BaseService::UserDistributionLevel($this->user['id']);
        if($user_level['code'] == 0)
        {
            // ajax判断是否有分销等级权限
            if(IS_AJAX && empty($user_level['data']))
            {
                exit(json_encode(DataReturn('当前没有分销权限', -10)));
            }

            $this->user_level = $user_level['data'];
            $this->assign('user_level', $this->user_level);
        }

        // 应用配置
        $plugins_base = PluginsService::PluginsData('distribution', BaseService::$base_config_attachment_field);
        $this->plugins_base = $plugins_base['data'];
        $this->assign('plugins_base_data', $this->plugins_base);
    }
}
?>