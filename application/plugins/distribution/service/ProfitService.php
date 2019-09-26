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
use app\service\UserService;
use app\service\MessageService;
use app\plugins\distribution\service\BaseService;
use app\plugins\wallet\service\WalletService;

/**
 * 分销 - 收益服务层
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class ProfitService
{
    /**
     * 订单完成收益增加
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-06-10
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function OrderProfitInc($params = [])
    {
        // 参数
        if(empty($params['order_id']))
        {
            return DataReturn('订单id有误', -1);
        }

        // 获取订单数据
        $order = Db::name('Order')->field('id,user_id,status,total_price,refund_price')->find(intval($params['order_id']));
        if(empty($order))
        {
            return DataReturn('订单不存在', -1);
        }

        // 状态校验
        if(!in_array($order['status'], [2,3,4]))
        {
            $order_status_list = lang('common_order_user_status');
            return DataReturn('订单状态无效['.$order_status_list[$order['status']]['name'].']', -1);
        }

        // 插件配置信息
        $base = PluginsService::PluginsData('distribution', BaseService::$base_config_attachment_field);

        // 是否开启内购
        if(isset($base['data']['self_buy']) && $base['data']['self_buy'] == 1)
        {
            // 内购使用一级分销分成
            $user_level = BaseService::UserDistributionLevel($order['user_id'], true);
            if($user_level['code'] == 0)
            {
                // 添加上一级收益
                self::ProfitInsert($order, $order['user_id'], $user_level['data']['level_rate_one'], 1);
            }
        }

        // 上一级用户id
        $user_id = Db::name('User')->where(['id'=>$order['user_id']])->value('referrer');
        if(!empty($user_id))
        {
            // 上一级分销等级
            $user_level = BaseService::UserDistributionLevel($user_id, true);
            if($user_level['code'] == 0 && !empty($user_level['data']))
            {
                // 添加上一级收益
                self::ProfitInsert($order, $user_id, $user_level['data']['level_rate_one'], 1);
            }

            // 分销层级
            $level = isset($base['data']['level']) ? intval($base['data']['level']) : 2;

            // 上二级用户id
            if($level > 0)
            {
                $user_id = Db::name('User')->where(['id'=>$user_id])->value('referrer');
                if(!empty($user_id))
                {
                    // 上二级分销等级
                    $user_level = BaseService::UserDistributionLevel($user_id, true);
                    if($user_level['code'] == 0 && !empty($user_level['data']))
                    {
                        // 添加上一级收益
                        self::ProfitInsert($order, $user_id, $user_level['data']['level_rate_two'], 2);
                    }

                    // 上三级用户id
                    if($level > 1)
                    {
                        $user_id = Db::name('User')->where(['id'=>$user_id])->value('referrer');
                        if(!empty($user_id))
                        {
                            // 上三级分销等级
                            $user_level = BaseService::UserDistributionLevel($user_id, true);
                            if($user_level['code'] == 0 && !empty($user_level['data']))
                            {
                                // 添加上一级收益
                                self::ProfitInsert($order, $user_id, $user_level['data']['level_rate_three'], 3);
                            }
                        }
                    }
                }
            }
            return DataReturn('操作成功', 0);
        }
        return DataReturn('无需处理', 0);
    }

    /**
     * 收益添加
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-06-10
     * @desc    description
     * @param   [array]             $order        [订单数据]
     * @param   [int]               $user_id      [受益人用户id]
     * @param   [int]               $rate         [收益比例]
     * @param   [int]               $level        [当前级别（1~3）]
     */
    private static function ProfitInsert($order, $user_id, $rate = 0, $level = 0)
    {
        // 日志数据
        $data = [
            'user_id'           => $user_id,
            'order_id'          => $order['id'],
            'order_user_id'     => $order['user_id'],
            'total_price'       => $order['total_price'],
            'refund_price'      => $order['refund_price'],
            'profit_price'      => ($rate > 0) ? PriceNumberFormat(($order['total_price']-$order['refund_price'])*($rate/100)) : 0.00,
            'rate'              => $rate,
            'is_refund'         => ($order['refund_price'] > 0) ? 1 : 0,
            'level'             => intval($level),
            'add_time'          => time(),
        ];
        $log_id = Db::name('PluginsDistributionProfitLog')->insertGetId($data);
        if($log_id > 0)
        {
            // 获取订单用户昵称
            $user = UserService::GetUserViewInfo($order['user_id'], '', true);

            // 消息通知
            $msg = $user['user_name_view'].'用户支付订单'.$data['total_price'].'元, 收益'.$data['profit_price'].'元';
            MessageService::MessageAdd($user_id, '分销收益新增', $msg, 0, $log_id);

            // 钱包变更
            WalletService::UserWalletMoneyUpdate($user_id, $data['profit_price'], 1, 'normal_money', 0, '分销收益新增');
        }
    }

    /**
     * 订单变更重新计算收益
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-06-10
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function OrderChange($params = [])
    {
        // 参数
        if(empty($params['order_id']))
        {
            return DataReturn('订单id有误', -1);
        }

        // 获取订单数据
        $order = Db::name('Order')->field('id,user_id,status,total_price,refund_price,order_no')->find(intval($params['order_id']));
        if(empty($order))
        {
            return DataReturn('订单不存在', -1);
        }

        // 获取收益数据
        $profit = Db::name('PluginsDistributionProfitLog')->where(['order_id'=>$order['id']])->select();
        if(!empty($profit))
        {
            foreach($profit as $v)
            {
                // 重新计算收益
                $data = [
                    'total_price'   => $order['total_price'],
                    'refund_price'  => $order['refund_price'],
                    'profit_price'  => ($v['rate'] > 0) ? PriceNumberFormat(($order['total_price']-$order['refund_price'])*($v['rate']/100)) : 0.00,
                    'upd_time'      => time(),
                ];
                $msg = $order['order_no'].'订单发生变更处理, 订单金额'.$data['total_price'].', 退款金额'.$data['refund_price'].', 收益'.$data['profit_price'].' / 原收益'.$v['profit_price'];
                $data['msg'] = $v['msg'].'['.$msg.']';
                if(Db::name('PluginsDistributionProfitLog')->where(['id'=>$v['id']])->update($data))
                {
                    // 收益金额不一致的时候变更
                    if($v['profit_price'] != $data['profit_price'])
                    {
                        // 描述标题
                        $msg_title = '分销收益变更';

                        // 消息通知
                        MessageService::MessageAdd($v['user_id'], $msg_title, $msg, 0, $v['id']);

                        // 钱包变更
                        if($v['profit_price'] > $data['profit_price'])
                        {
                            $money = $v['profit_price']-$data['profit_price'];
                            $type = 0;
                        } else {
                            $money = $data['profit_price']-$v['profit_price'];
                            $type = 1;
                        }
                        WalletService::UserWalletMoneyUpdate($v['user_id'], PriceNumberFormat($money), $type, 'normal_money', 0, $msg_title);
                    }
                }
            }
            return DataReturn('操作成功', 0);
        }
        return DataReturn('无需处理', 0);
    }
}
?>