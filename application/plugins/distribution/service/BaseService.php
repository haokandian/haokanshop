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

/**
 * 分销 - 基础服务层
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class BaseService
{
    // 基础数据附件字段
    public static $base_config_attachment_field = [
        'default_level_images',
        'default_qrcode_logo',
    ];

    // 是否开启
    public static $distribution_is_enable_list = [
        0 => ['value' => 0, 'name' => '关闭', 'checked' => true],
        1 => ['value' => 1, 'name' => '开启'],
    ];

    // 分销层级
    public static $distribution_level_list = [
        0 => ['value' => 0, 'name' => '一级分销'],
        1 => ['value' => 1, 'name' => '二级分销'],
        2 => ['value' => 2, 'name' => '三级分销', 'checked' => true],
    ];

    // 边框样式
    public static $distribution_border_style_list = [
        0 => ['value' => 0, 'name' => '正方形', 'class' => '', 'checked' => true],
        1 => ['value' => 1, 'name' => '圆角', 'class' => 'am-radius',],
        2 => ['value' => 2, 'name' => '圆形', 'class' => 'am-circle',],
    ];

    public static $font_path = ROOT.'public'.DS.'static'.DS.'plugins'.DS.'css'.DS.'distribution'.DS.'ttfs'.DS.'Alibaba-PuHuiTi-Regular.ttf';

    /**
     * 分销等级数据列表
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-04-22
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function LevelDataList($params = [])
    {
        // 数据字段
        $data_field = 'level_list';

        // 获取数据
        $ret = PluginsService::PluginsData('distribution', self::$base_config_attachment_field);
        $data = (empty($ret['data']) || empty($ret['data'][$data_field])) ? [] : $ret['data'][$data_field];

        // 数据处理
        if(!isset($params['is_handle_data']) || $params['is_handle_data'] == 1)
        {
            return self::LevelDataHandle($data, $ret['data'], $params);
        }
        return DataReturn('处理成功', 0, $data);
    }

    /**
     * 分销等级数据列表处理
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-04-27T01:08:23+0800
     * @param    [array]                   $data   [等级数据]
     * @param    [array]                   $base   [基础配置数据]
     * @param    [array]                   $params [输入参数]
     */
    public static function LevelDataHandle($data, $base = [], $params = [])
    {
        if(!empty($data))
        {
            $common_is_enable_tips = lang('common_is_enable_tips');
            foreach($data as &$v)
            {
                // 是否启用
                $v['is_enable_text'] = $common_is_enable_tips[$v['is_enable']]['name'];
                
                // 图片地址,不存在使用默认配置或系统默认
                $v['images_url_old'] = $v['images_url'];
                if(empty($v['images_url']))
                {
                    $v['images_url'] = empty($base['default_level_images']) ? config('shopxo.attachment_host').'/static/plugins/images/distribution/default-level.png' : $base['default_level_images'];
                } else {
                    $v['images_url'] = ResourcesService::AttachmentPathViewHandle($v['images_url']);
                }

                // 时间
                $v['operation_time_time'] = empty($v['operation_time']) ? '' : date('Y-m-d H:i:s', $v['operation_time']);
                $v['operation_time_date'] = empty($v['operation_time']) ? '' : date('Y-m-d', $v['operation_time']);
            }
        }

        // 是否读取单条
        if(!empty($params['get_id']) && isset($data[$params['get_id']]))
        {
            $data = $data[$params['get_id']];
        }

        return DataReturn('处理成功', 0, $data);
    }

    /**
     * 分销等级数据保存
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-04-22
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function LevelDataSave($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'length',
                'key_name'          => 'name',
                'checked_data'      => '1,30',
                'error_msg'         => '名称长度 1~30 个字符',
            ],
            [
                'checked_type'      => 'isset',
                'key_name'          => 'rules_min',
                'error_msg'         => '请填写消费最小金额',
            ],
            [
                'checked_type'      => 'isset',
                'key_name'          => 'rules_max',
                'error_msg'         => '请填写消费最大金额',
            ],
            [
                'checked_type'      => 'max',
                'key_name'          => 'level_rate_one',
                'checked_data'      => 100,
                'is_checked'        => 1,
                'error_msg'         => '一级返佣比例 0~100 的数字',
            ],
            [
                'checked_type'      => 'max',
                'key_name'          => 'level_rate_two',
                'checked_data'      => 100,
                'is_checked'        => 1,
                'error_msg'         => '二级返佣比例 0~100 的数字',
            ],
            [
                'checked_type'      => 'max',
                'key_name'          => 'level_rate_three',
                'checked_data'      => 100,
                'is_checked'        => 1,
                'error_msg'         => '三级返佣比例 0~100 的数字',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 请求参数
        $p = [
            [
                'checked_type'      => 'eq',
                'key_name'          => 'rules_min',
                'checked_data'      => $params['rules_max'],
                'error_msg'         => '消费最小金额不能最大金额相等',
            ],
            [
                'checked_type'      => 'eq',
                'key_name'          => 'rules_max',
                'checked_data'      => $params['rules_min'],
                'error_msg'         => '消费最大金额不能最小金额相等',
            ],
        ];
        if(intval($params['rules_max']) > 0)
        {
            $p[] = [
                'checked_type'      => 'max',
                'key_name'          => 'rules_min',
                'checked_data'      => intval($params['rules_max']),
                'error_msg'         => '消费最小金额不能大于消费最大金额['.intval($params['rules_max']).']',
            ];
            $p[] = [
                'checked_type'      => 'min',
                'key_name'          => 'rules_max',
                'checked_data'      => intval($params['rules_min']),
                'error_msg'         => '消费最大金额不能小于消费最小金额['.intval($params['rules_min']).']',
            ];
        }
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 数据字段
        $data_field = 'level_list';

        // 附件
        $data_fields = ['images_url'];
        $attachment = ResourcesService::AttachmentParams($params, $data_fields);

        // 数据
        $data = [
            'name'                  => $params['name'],
            'rules_min'             => $params['rules_min'],
            'rules_max'             => $params['rules_max'],
            'images_url'            => $attachment['data']['images_url'],
            'level_rate_one'        => isset($params['level_rate_one']) ? intval($params['level_rate_one']) : 0,
            'level_rate_two'        => isset($params['level_rate_two']) ? intval($params['level_rate_two']) : 0,
            'level_rate_three'      => isset($params['level_rate_three']) ? intval($params['level_rate_three']) : 0,
            'is_enable'             => isset($params['is_enable']) ? intval($params['is_enable']) : 0,
            'operation_time'        => time(),
        ];

        // 原有数据
        $ret = PluginsService::PluginsData('distribution', self::$base_config_attachment_field, false);

        // 数据id
        $data['id'] = (empty($params['id']) || empty($ret['data']) || empty($ret['data'][$data_field][$params['id']])) ? date('YmdHis').GetNumberCode(6) : $params['id'];
        $ret['data'][$data_field][$data['id']] = $data;

        // 保存
        return PluginsService::PluginsDataSave(['plugins'=>'distribution', 'data'=>$ret['data']]);
    }

    /**
     * 数据删除
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-12-18
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function DataDelete($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'id',
                'error_msg'         => '操作id有误',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 数据字段
        $data_field = empty($params['data_field']) ? 'data_list' : $params['data_field'];

        // 原有数据
        $ret = PluginsService::PluginsData('distribution', self::$base_config_attachment_field, false);
        $ret['data'][$data_field] = (empty($ret['data']) || empty($ret['data'][$data_field])) ? [] : $ret['data'][$data_field];

        // 删除操作
        if(isset($ret['data'][$data_field][$params['id']]))
        {
            unset($ret['data'][$data_field][$params['id']]);
        }
        
        // 保存
        return PluginsService::PluginsDataSave(['plugins'=>'distribution', 'data'=>$ret['data']]);
    }

    /**
     * 数据状态更新
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-12-18
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function DataStatusUpdate($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'id',
                'error_msg'         => '操作id有误',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'field',
                'error_msg'         => '操作字段有误',
            ],
            [
                'checked_type'      => 'in',
                'key_name'          => 'state',
                'checked_data'      => [0,1],
                'error_msg'         => '状态有误',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 数据字段
        $data_field = empty($params['data_field']) ? 'data_list' : $params['data_field'];

        // 原有数据
        $ret = PluginsService::PluginsData('distribution', self::$base_config_attachment_field, false);
        $ret['data'][$data_field] = (empty($ret['data']) || empty($ret['data'][$data_field])) ? [] : $ret['data'][$data_field];

        // 删除操作
        if(isset($ret['data'][$data_field][$params['id']]) && is_array($ret['data'][$data_field][$params['id']]))
        {
            $ret['data'][$data_field][$params['id']][$params['field']] = intval($params['state']);
            $ret['data'][$data_field][$params['id']]['operation_time'] = time();
        }
        
        // 保存
        return PluginsService::PluginsDataSave(['plugins'=>'distribution', 'data'=>$ret['data']]);
    }

    /**
     * 海报数据保存
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-04-22
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function PpsterDataSave($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'backdrop',
                'error_msg'         => '请上传海报背景图片',
            ],
            [
                'checked_type'      => 'isset',
                'key_name'          => 'avatar_width',
                'error_msg'         => '请设置头像宽度',
            ],
            [
                'checked_type'      => 'min',
                'key_name'          => 'avatar_width',
                'checked_data'      => 30,
                'error_msg'         => '头像宽度尺寸 30~300 之间',
            ],
            [
                'checked_type'      => 'max',
                'key_name'          => 'avatar_width',
                'checked_data'      => 300,
                'error_msg'         => '头像宽度尺寸 30~300 之间',
            ],
            [
                'checked_type'      => 'in',
                'key_name'          => 'avatar_border_style',
                'checked_data'      => array_column(self::$distribution_border_style_list, 'value'),
                'error_msg'         => '头像样式数据值有误',
            ],
            [
                'checked_type'      => 'isset',
                'key_name'          => 'qrcode_width',
                'error_msg'         => '请设置二维码宽度尺寸',
            ],
            [
                'checked_type'      => 'min',
                'key_name'          => 'qrcode_width',
                'checked_data'      => 60,
                'error_msg'         => '二维码宽度尺寸 60~300 之间',
            ],
            [
                'checked_type'      => 'max',
                'key_name'          => 'qrcode_width',
                'checked_data'      => 300,
                'error_msg'         => '二维码宽度尺寸 60~300 之间',
            ],
            [
                'checked_type'      => 'in',
                'key_name'          => 'qrcode_border_style',
                'checked_data'      => array_column(self::$distribution_border_style_list, 'value'),
                'error_msg'         => '二维码样式数据值有误',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 数据字段
        $data_field = 'poster_data';

        // 附件
        $data_fields = ['backdrop'];
        $attachment = ResourcesService::AttachmentParams($params, $data_fields);

        // 数据
        $data = [
            'backdrop'              => $attachment['data']['backdrop'],
            'avatar_width'          => intval($params['avatar_width']),
            'qrcode_width'          => intval($params['qrcode_width']),

            'avatar_top'            => isset($params['avatar_top']) ? intval($params['avatar_top']) : 0,
            'avatar_left'           => isset($params['avatar_left']) ? intval($params['avatar_left']) : 0,

            'nickname_top'          => isset($params['nickname_top']) ? intval($params['nickname_top']) : 0,
            'nickname_left'         => isset($params['nickname_left']) ? intval($params['nickname_left']) : 0,

            'qrcode_top'            => isset($params['qrcode_top']) ? intval($params['qrcode_top']) : 0,
            'qrcode_left'           => isset($params['qrcode_left']) ? intval($params['qrcode_left']) : 0,

            'avatar_border_style'   => isset($params['avatar_border_style']) ? intval($params['avatar_border_style']) : 0,
            'qrcode_border_style'   => isset($params['qrcode_border_style']) ? intval($params['qrcode_border_style']) : 0,

            'nickname_color'        => empty($params['nickname_color']) ? '' : $params['nickname_color'],
            'nickname_auto_center'  => isset($params['nickname_auto_center']) ? intval($params['nickname_auto_center']) : 0,
            'operation_time'        => time(),
        ];

        // 原有数据
        $ret = PluginsService::PluginsData('distribution', self::$base_config_attachment_field, false);

        // 保存
        $ret['data'][$data_field] = $data;
        return PluginsService::PluginsDataSave(['plugins'=>'distribution', 'data'=>$ret['data']]);
    }

    /**
     * 分享海报数据
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-04-22
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function PosterData($params = [])
    {
        // 数据字段
        $data_field = 'poster_data';

        // 获取数据
        $ret = PluginsService::PluginsData('distribution', self::$base_config_attachment_field);
        $data = (empty($ret['data']) || empty($ret['data'][$data_field])) ? [] : $ret['data'][$data_field];

        // 数据处理
        if(!isset($params['is_handle_data']) || $params['is_handle_data'] == 1)
        {
            if(isset($data['backdrop']))
            {
                // 图片地址
                $data['backdrop_old'] = $data['backdrop'];
                $data['backdrop'] = ResourcesService::AttachmentPathViewHandle($data['backdrop']);
            }
        }

        return DataReturn('处理成功', 0, $data);
    }

    /**
     * 获取用户分销数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-06-05
     * @desc    description
     * @param   [int]                   $user_id           [用户id]
     * @param   [boolean]               $is_recalculation  [是否重新计算]
     */
    public static function UserDistributionLevel($user_id, $is_recalculation = false)
    {
        $level = [];
        $base = PluginsService::PluginsData('distribution', self::$base_config_attachment_field);
        if(!empty($base['data']['level_list']))
        {
            // 消费总额（已支付订单）
            $value = self::UserEffectiveOrderTotalPrice($user_id);

            // 匹配相应的等级
            $level_list = self::LevelDataHandle($base['data']['level_list'], $base['data']);
            foreach($level_list['data'] as $rules)
            {
                if(isset($rules['is_enable']) && $rules['is_enable'] == 1)
                {
                    // 0-*
                    if($rules['rules_min'] <= 0 && $rules['rules_max'] > 0 && $value < $rules['rules_max'])
                    {
                        $level = $rules;
                        break;
                    }

                    // *-*
                    if($rules['rules_min'] > 0 && $rules['rules_max'] > 0 && $value >= $rules['rules_min'] && $value < $rules['rules_max'])
                    {
                        $level = $rules;
                        break;
                    }

                    // *-0
                    if($rules['rules_max'] <= 0 && $rules['rules_min'] > 0 && $value > $rules['rules_min'])
                    {
                        $level = $rules;
                        break;
                    }
                }
            }
        }
        return DataReturn('处理成功', 0, $level);
    }

    /**
     * 用户有效订单消费金额
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-05T22:08:34+0800
     * @param    [int]                   $user_id [用户id]
     */
    public static function UserEffectiveOrderTotalPrice($user_id)
    {
        // 订单状态（0待确认, 1已确认/待支付, 2已支付/待发货, 3已发货/待收货, 4已完成, 5已取消, 6已关闭）
        $where = [
            ['user_id', '=', $user_id],
            ['status', 'in', [2,3,4]],
        ];
        return (float) Db::name('Order')->where($where)->sum('total_price');
    }

    /**
     * 用户收益总额
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-05T22:08:34+0800
     * @param    [int]                   $user_id [用户id]
     */
    public static function UserProfitTotal($user_id)
    {
        return PriceNumberFormat(Db::name('PluginsDistributionProfitLog')->where(['user_id'=>$user_id])->sum('profit_price'));
    }

    /**
     * 海报清空
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-12T20:36:38+0800
     * @param   [array]           $params [输入参数]
     */
    public static function PpsterDelete($params = [])
    {
        $dir = ROOT.'public'.DS.'static'.DS.'upload'.DS.'images'.DS.'plugins_distribution'.DS.'poster';
        if(is_dir($dir))
        {
            // 是否有权限
            if(!is_writable($dir))
            {
                return DataReturn('目录没权限', -1);
            }

            // 删除目录
            \base\FileUtil::UnlinkDir($dir);
        }

        return DataReturn('操作成功', 0);
    }


    /**
     * 商品海报数据
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-04-22
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function PosterGoodsData($params = [])
    {
        // 数据字段
        $data_field = 'poster_goods_data';

        // 获取数据
        $ret = PluginsService::PluginsData('distribution', self::$base_config_attachment_field);
        $data = (empty($ret['data']) || empty($ret['data'][$data_field])) ? [] : $ret['data'][$data_field];

        return DataReturn('处理成功', 0, $data);
    }

    /**
     * 商品海报数据保存
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-04-22
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function PpsterGoodsDataSave($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'length',
                'key_name'          => 'bottom_left_text',
                'checked_data'      => '10',
                'is_checked'        => 1,
                'error_msg'         => '底部左侧文本不超过 10 个字符',
            ],
            [
                'checked_type'      => 'length',
                'key_name'          => 'bottom_right_text',
                'checked_data'      => '6',
                'is_checked'        => 1,
                'error_msg'         => '底部右侧文本不超过 6 个字符',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 数据字段
        $data_field = 'poster_goods_data';

        // 数据
        $data = [
            'goods_title_text_color'    => empty($params['goods_title_text_color']) ? '' : $params['goods_title_text_color'],
            'goods_simple_text_color'   => empty($params['goods_simple_text_color']) ? '' : $params['goods_simple_text_color'],

            'bottom_left_text'          => empty($params['bottom_left_text']) ? '' : $params['bottom_left_text'],
            'bottom_left_text_color'    => empty($params['bottom_left_text_color']) ? '' : $params['bottom_left_text_color'],

            'bottom_right_text'         => empty($params['bottom_right_text']) ? '' : $params['bottom_right_text'],
            'bottom_right_text_color'   => empty($params['bottom_right_text_color']) ? '' : $params['bottom_right_text_color'],
        ];

        // 原有数据
        $ret = PluginsService::PluginsData('distribution', self::$base_config_attachment_field, false);

        // 保存
        $ret['data'][$data_field] = $data;
        return PluginsService::PluginsDataSave(['plugins'=>'distribution', 'data'=>$ret['data']]);
    }

    /**
     * 商品海报清空
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-12T20:36:38+0800
     * @param    [array]           $params [输入参数]
     */
    public static function PpsterGoodsDelete($params = [])
    {
        $path = ROOT.'public'.DS.'static'.DS.'upload'.DS.'images'.DS.'plugins_distribution'.DS;
        $dir_all = ['poster_goods_qrcode', 'poster_goods'];
        foreach($dir_all as $v)
        {
            if(is_dir($path.$v))
            {
                // 是否有权限
                if(!is_writable($path.$v))
                {
                    return DataReturn('目录没权限['.$path.$v.']', -1);
                }

                // 删除目录
                \base\FileUtil::UnlinkDir($path.$v);
            }
        }
        return DataReturn('操作成功', 0);
    }
}
?>