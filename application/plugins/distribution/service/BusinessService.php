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
namespace app\plugins\distribution\service;

use think\Db;
use app\service\PluginsService;
use app\service\ResourcesService;
use app\service\UserService;
use app\service\PaymentService;
use app\plugins\distribution\service\BaseService;

/**
 * 分销 - 业务服务层
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class BusinessService
{
    /**
     * 获取用户团队列表
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-09T22:50:14+0800
     * @param    [array]          $params [输入参数]
     */
    public static function UserTeamList($params = [])
    {
        $m = isset($params['m']) ? intval($params['m']) : 0;
        $n = isset($params['n']) ? intval($params['n']) : 10;
        $where = empty($params['where']) ? [] : $params['where'];
        $field = empty($params['field']) ? 'id,username,nickname,mobile,email,avatar,add_time' : $params['field'];
        $order_by = empty($params['order_by']) ? 'id desc' : $params['order_by'];
        $is_privacy = isset($params['is_privacy']) ? (boolean) $params['is_privacy'] : true;

        $data = Db::name('User')->field($field)->where($where)->limit($m, $n)->order($order_by)->select();
        if(!empty($data))
        {
            foreach($data as &$v)
            {
                // 用户信息处理
                $v = UserService::GetUserViewInfo($v['id'], $v, $is_privacy);

                // 加入时间
                $v['add_time_text'] = date('Y-m-d H:i', $v['add_time']);

                // 当前用户下一级总数
                $v['referrer_count'] = self::UserTeamTotal(self::UserTeamWhere(['user'=>$v]));

                // 当前用户下一级消费总金额
                $find_where = [
                    ['u.referrer', '=', $v['id']],
                    ['o.status', 'in', [2,3,4]],
                ];
                $v['find_order_total'] = PriceNumberFormat(Db::name('Order')->alias('o')->join(['__USER__'=>'u'], 'o.user_id=u.id')->where($find_where)->sum('o.total_price'));

                // 订单有效金额
                $order_where = [
                    ['user_id', '=', $v['id']],
                    ['status', 'in', [2,3,4]],
                ];
                $v['order_total'] = PriceNumberFormat(Db::name('Order')->where($order_where)->sum('total_price'));
            }
        }
        return DataReturn('处理成功', 0, $data);
    }

    /**
     * 用户团队列表条件
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-09T22:53:46+0800
     * @param    [array]          $params [输入参数]
     */
    public static function UserTeamWhere($params = [])
    {
        $where = [
            ['is_delete_time', '=', 0],
        ];

        // 关键字根据订单筛选
        if(!empty($params['keywords']))
        {
            $user_ids = Db::name('User')->where('username|nickname|mobile|email', '=', $params['keywords'])->column('id');
            if(!empty($user_ids))
            {
                $where[] = ['referrer', 'in', $user_ids];
            } else {
                // 无数据条件，避免搜索条件没有数据造成的错觉
                $where[] = ['id', '=', 0];
            }
        }

        // 用户
        if(!empty($params['user']))
        {
            $where[] = ['referrer', '=', $params['user']['id']];
        }

        // 更多条件
        if(isset($params['is_more']) && $params['is_more'] == 1)
        {
            // 性别
            if(isset($params['gender']) && $params['gender'] > -1)
            {
                $where[] = ['gender', '=', intval($params['gender'])];
            }

            // 时间
            if(!empty($params['time_start']))
            {
                $where[] = ['add_time', '>', strtotime($params['time_start'])];
            }
            if(!empty($params['time_end']))
            {
                $where[] = ['add_time', '<', strtotime($params['time_end'])];
            }
        }

        return $where;
    }

    /**
     * 用户团队总数
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-09T23:10:43+0800
     * @param    [array]          $where [条件]
     */
    public static function UserTeamTotal($where)
    {
        return (int) Db::name('User')->where($where)->count();
    }

    /**
     * 获取用户订单列表
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-09T22:50:14+0800
     * @param    [array]          $params [输入参数]
     */
    public static function UserOrderList($params = [])
    {
        $m = isset($params['m']) ? intval($params['m']) : 0;
        $n = isset($params['n']) ? intval($params['n']) : 10;
        $where = empty($params['where']) ? [] : $params['where'];
        $field = empty($params['field']) ? 'o.id,o.user_id,o.status,o.total_price,o.refund_price,o.client_type,o.add_time,u.username,u.nickname,u.mobile,u.email,u.avatar' : $params['field'];
        $order_by = empty($params['order_by']) ? 'o.id desc' : $params['order_by'];
        $is_privacy = isset($params['is_privacy']) ? (boolean) $params['is_privacy'] : true;

        $data = Db::name('Order')->alias('o')->join(['__USER__'=>'u'], 'o.user_id=u.id')->field($field)->where($where)->limit($m, $n)->order($order_by)->select();
        if(!empty($data))
        {
            $order_status_list = lang('common_order_user_status');
            $common_platform_type = lang('common_platform_type');
            foreach($data as &$v)
            {
                // 用户信息处理
                $v = UserService::GetUserViewInfo($v['user_id'], $v, $is_privacy);

                // 订单状态
                $v['status_text'] = $order_status_list[$v['status']]['name'];

                // 订单时间
                $v['add_time_text'] = date('Y-m-d H:i', $v['add_time']);

                // 支付方式
                $v['payment_name'] = ($v['status'] <= 1) ? null : PaymentService::OrderPaymentName($v['id']);

                // 客户端
                $v['client_type_name'] = isset($common_platform_type[$v['client_type']]) ? $common_platform_type[$v['client_type']]['name'] : '';
            }
        }
        return DataReturn('处理成功', 0, $data);
    }

    /**
     * 用户订单列表条件
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-09T22:53:46+0800
     * @param    [array]          $params [输入参数]
     */
    public static function UserOrderWhere($params = [])
    {
        $where = [];

        // 关键字根据订单筛选
        if(!empty($params['keywords']))
        {
            $order_ids = Db::name('Order')->where('order_no', '=', $params['keywords'])->column('id');
            if(!empty($order_ids))
            {
                $where[] = ['o.id', 'in', $order_ids];
            } else {
                $user_ids = Db::name('User')->where('username|nickname|mobile|email', '=', $params['keywords'])->column('id');
                if(!empty($user_ids))
                {
                    $where[] = ['u.referrer', 'in', $user_ids];
                } else {
                    // 无数据条件，避免搜索条件没有数据造成的错觉
                    $where[] = ['u.id', '=', 0];
                }
            }
        }

        // 用户
        if(!empty($params['user']))
        {
            $where[] = ['u.referrer', '=', $params['user']['id']];
            $where[] = ['o.status', 'in', [2,3,4]];
        }

        // 指定用户id
        if(!empty($params['uid']))
        {
            $where[] = ['o.user_id', '=', intval($params['uid'])];
        }

        // 更多条件
        if(isset($params['is_more']) && $params['is_more'] == 1)
        {
            // 是否有退款
            if(isset($params['is_refund']) && $params['is_refund'] > -1)
            {
                if($params['is_refund'] == 0)
                {
                    $where[] = ['o.refund_price', '<=', 0];
                } else {
                    $where[] = ['o.refund_price', '>', 0];
                }
            }

            // 订单状态
            if(isset($params['status']) && $params['status'] > -1)
            {
                $where[] = ['o.status', '=', intval($params['status'])];
            }

            // 来源
            if(!empty($params['client_type']))
            {
                $where[] = ['o.client_type', '=', $params['client_type']];
            }

            // 支付方式
            if(isset($params['payment_id']) && $params['payment_id'] > -1)
            {
                $where[] = ['o.payment_id', '=', intval($params['payment_id'])];
            }

            // 时间
            if(!empty($params['time_start']))
            {
                $where[] = ['o.add_time', '>', strtotime($params['time_start'])];
            }
            if(!empty($params['time_end']))
            {
                $where[] = ['o.add_time', '<', strtotime($params['time_end'])];
            }
        }

        return $where;
    }

    /**
     * 用户订单总数
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-09T23:10:43+0800
     * @param    [array]          $where [条件]
     */
    public static function UserOrderTotal($where)
    {
        return (int) Db::name('Order')->alias('o')->join(['__USER__'=>'u'], 'o.user_id=u.id')->where($where)->count();
    }

    /**
     * 获取用户收益明细列表
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-09T22:50:14+0800
     * @param    [array]          $params [输入参数]
     */
    public static function UserProfitList($params = [])
    {
        $m = isset($params['m']) ? intval($params['m']) : 0;
        $n = isset($params['n']) ? intval($params['n']) : 10;
        $where = empty($params['where']) ? [] : $params['where'];
        $field = empty($params['field']) ? '*' : $params['field'];
        $order_by = empty($params['order_by']) ? 'id desc' : $params['order_by'];
        $user_type = isset($params['user_type']) ? $params['user_type'] : 'user';

        $data = Db::name('PluginsDistributionProfitLog')->field($field)->where($where)->limit($m, $n)->order($order_by)->select();
        if(!empty($data))
        {
            $common_platform_type = lang('common_platform_type');
            foreach($data as &$v)
            {
                // 用户信息
                $v['user'] = ($user_type == 'admin') ? UserService::GetUserViewInfo($v['user_id']) : [];

                // 添加时间
                $v['add_time_text'] = date('Y-m-d H:i', $v['add_time']);

                // 更新时间
                $v['upd_time_text'] = ($v['upd_time'] > 0) ? date('Y-m-d H:i', $v['upd_time']) : '';
            }
        }
        return DataReturn('处理成功', 0, $data);
    }

    /**
     * 用户收益明细列表条件
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-09T22:53:46+0800
     * @param    [array]          $params [输入参数]
     */
    public static function UserProfitWhere($params = [])
    {
        $where = [];

        // 关键字根据订单筛选
        if(!empty($params['keywords']))
        {
            $order_ids = Db::name('Order')->where('order_no', '=', $params['keywords'])->column('id');
            if(!empty($order_ids))
            {
                $where[] = ['order_id', 'in', $order_ids];
            } else {
                $user_ids = Db::name('User')->where('username|nickname|mobile|email', '=', $params['keywords'])->column('id');
                if(!empty($user_ids))
                {
                    $where[] = ['user_id', 'in', $user_ids];
                } else {
                    // 无数据条件，避免搜索条件没有数据造成的错觉
                    $where[] = ['id', '=', 0];
                }
            }
        }

        // 用户
        if(!empty($params['user']))
        {
            $where[] = ['user_id', '=', $params['user']['id']];
        }

        // 指定用户id
        if(!empty($params['uid']))
        {
            $where[] = ['user_id', '=', intval($params['uid'])];
        }

        // 更多条件
        if(isset($params['is_more']) && $params['is_more'] == 1)
        {
            // 是否有退款
            if(isset($params['is_refund']) && $params['is_refund'] > -1)
            {
                $where[] = ['is_refund', '=', intval($params['is_refund'])];
            }

            // 级别
            if(isset($params['level']) && $params['level'] > 0)
            {
                $where[] = ['level', '=', intval($params['level'])];
            }

            // 时间
            if(!empty($params['time_start']))
            {
                $where[] = ['add_time', '>', strtotime($params['time_start'])];
            }
            if(!empty($params['time_end']))
            {
                $where[] = ['add_time', '<', strtotime($params['time_end'])];
            }
        }
        return $where;
    }

    /**
     * 用户收益明细总数
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-09T23:10:43+0800
     * @param    [array]          $where [条件]
     */
    public static function UserProfitTotal($where)
    {
        return (int) Db::name('PluginsDistributionProfitLog')->where($where)->count();
    }
}
?>