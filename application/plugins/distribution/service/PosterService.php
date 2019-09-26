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

use app\service\PluginsService;
use app\service\ResourcesService;
use app\service\UserService;
use app\plugins\distribution\service\BaseService;

/**
 * 分销 - 海报服务层
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class PosterService
{
    // 海报宽度基准数
    private static $benchmark_width = 300;

    /**
     * 用户海报
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-06T21:09:38+0800
     * @param    [array]         $params [输入参数]
     */
    public static function UserPoster($params = [])
    {
        // 是否刷新
        $is_refresh = isset($params['is_refresh']) ? (boolean)$params['is_refresh'] : false;

        // 用户信息
        $user = UserService::LoginUserInfo();
        if(empty($user))
        {
            return DataReturn('请先登录', -400);
        }

        // 海报地址
        $poster_path = 'static'.DS.'upload'.DS.'images'.DS.'plugins_distribution'.DS.'poster'.DS.date('Y', $user['add_time']).DS.date('m', $user['add_time']).DS.date('d', $user['add_time']).DS;
        $poster_filename = date('YmdHis', $user['add_time']).$user['id'].'.png';
        $poster_dir = ROOT.'public'.DS.$poster_path.$poster_filename;

        // 已存在则直接返回
        if(file_exists($poster_dir) && $is_refresh === false)
        {
            return DataReturn('海报创建成功', 0, ResourcesService::AttachmentPathViewHandle(DS.$poster_path.$poster_filename));
        }

        // gd函数是否支持
        if(!function_exists('imagettftext'))
        {
            return DataReturn('imagettftext函数不支持', -1);
        }

        // 基础配置
        $base = PluginsService::PluginsData('distribution', BaseService::$base_config_attachment_field);
        if($base['code'] != 0)
        {
            return $base;
        }

        // 海报配置信息
        $poster = BaseService::PosterData();
        if(empty($poster['data']))
        {
            return DataReturn('海报未配置', -1);
        }

        // 海报背景
        $bg = imagecreatefromstring(file_get_contents($poster['data']['backdrop']));
        $bg_width = imagesx($bg);
        
        // 头像大小计算
        $avatar_width = empty($poster['data']['avatar_width']) ? 100 : $bg_width/(self::$benchmark_width/$poster['data']['avatar_width']);
        
        // 头像资源
        $av = imagecreatefromstring(file_get_contents($user['avatar']));
        
        // 调整大小
        $av = self::ImagesResize($user['avatar'], $av, $avatar_width, $avatar_width);

        // 收圆
        if($poster['data']['avatar_border_style'] == 1)
        {
            $av = self::ImagesAppearance($av, 'radius');
        } elseif($poster['data']['avatar_border_style'] == 2)
        {
            $av = self::ImagesAppearance($av, 'circle');
        }

        // 头像宇海报合并
        $bg = self::ImagesMerge($bg, $av, $avatar_width, $avatar_width, $poster['data']['avatar_left'], $poster['data']['avatar_top']);


        // 二维码创建
        $qrcode = self::UserShareQrcodeCreate($user['id'], $user['add_time'], $is_refresh);
        if($qrcode['code'] != 0)
        {
            return $qrcode;
        }

        // 二维码大小计算
        $qrcode_width = empty($poster['data']['qrcode_width']) ? 100 : $bg_width/(self::$benchmark_width/$poster['data']['qrcode_width']);
        
        // 二维码资源
        $av = imagecreatefromstring(file_get_contents($qrcode['data']));
        
        // 调整大小
        $av = self::ImagesResize($qrcode['data'], $av, $qrcode_width, $qrcode_width);

        // 收圆
        if($poster['data']['qrcode_border_style'] == 1)
        {
            $av = self::ImagesAppearance($av, 'radius');
        } elseif($poster['data']['qrcode_border_style'] == 2)
        {
            $av = self::ImagesAppearance($av, 'circle');
        }

        // 二维码宇海报合并
        $bg = self::ImagesMerge($bg, $av, $qrcode_width, $qrcode_width, $poster['data']['qrcode_left'], $poster['data']['qrcode_top']);

        // 用户名
        $bg = self::StringMerge($bg, $user['user_name_view'], $poster['data']['nickname_color'],  $poster['data']['nickname_auto_center'], $poster['data']['nickname_left'], $poster['data']['nickname_top']);

        // 目录不存在则创建
        if(\base\FileUtil::CreateDir(ROOT.'public'.DS.$poster_path) !== true)
        {
            return DataReturn('海报目录创建失败', -10);
        }

        // 存储图片
        imagepng($bg, $poster_dir);
        if(file_exists($poster_dir))
        {
            // 刷新缓存时间
            session('user_poster_images_ver', date('YmdHis'));
            
            return DataReturn('海报创建成功', 0, ResourcesService::AttachmentPathViewHandle(DS.$poster_path.$poster_filename));
        }
        return DataReturn('海报创建失败', -100);
    }

    /**
     * 用户分享二维码生成
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-04-19
     * @desc    description
     * @param   [int]          $user_id         [用户id]
     * @param   [int]          $user_add_time   [用户创建时间]
     * @param   [boolean]      $is_refresh      [是否刷新]
     */
    public static function UserShareQrcodeCreate($user_id, $user_add_time, $is_refresh = false)
    {
        if(!empty($user_id) && !empty($user_add_time))
        {
            // 自定义路径和名称
            $path = 'static'.DS.'upload'.DS.'images'.DS.'plugins_distribution'.DS.'qrcode'.DS.date('Y', $user_add_time).DS.date('m', $user_add_time).DS.date('d', $user_add_time).DS;
            $filename = date('YmdHis', $user_add_time).$user_id.'.png';

            // 二维码处理参数
            $params = [
                'path'      => DS.$path,
                'filename'  => $filename,
            ];

            // 不存在则创建
            if(!file_exists(ROOT.'public'.DS.$path.$filename) || $is_refresh === true)
            {
                // 基础配置
                $base = PluginsService::PluginsData('distribution', BaseService::$base_config_attachment_field);

                // url/logo
                $params['content'] = self::UserShareUrl($user_id);
                $params['logo'] = empty($base['data']['default_qrcode_logo']) ? '' : $base['data']['default_qrcode_logo'];

                // 创建二维码
                $ret = (new \base\Qrcode())->Create($params);
                if($ret['code'] != 0)
                {
                    return $ret;
                }
            }
            return DataReturn('处理成功', 0, ResourcesService::AttachmentPathViewHandle($params['path'].$params['filename']));
        }
        return DataReturn('用户id有误', -100);
    }

    /**
     * 获取用户分享url地址
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-08T21:08:47+0800
     * @param    [int]          $user_id [用户id]
     */
    public static function UserShareUrl($user_id)
    {
        return __MY_URL__.'?referrer='.UserService::UserReferrerEncryption($user_id);
    }

    /**
     * 字符串合并
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-08T18:25:39+0800
     * @param    [resource]          $bg                    [背景图像资源]
     * @param    [string]            $string                [被合并字符串]
     * @param    [string]            $nickname_color        [颜色]
     * @param    [string]            $nickname_auto_center  [是否自动计算居中位置]
     * @param    [int]               $string_left           [被合并字符串左侧距离]
     * @param    [int]               $string_top            [被合并字符串顶部距离]
     */
    public static function StringMerge($bg, $string, $nickname_color = '#666', $nickname_auto_center = 0, $string_left = null, $string_top = null)
    {
        // 背景图像尺寸
        $bg_width = imagesx($bg);
        $bg_height = imagesy($bg);

        // 字符串宽度计算
        $string_width = self::StringWidthCalculation($string);

        // 十六进制转RGB
        $rgb = HexToRgb($nickname_color);

        // 字符串颜色
        $color = imagecolorallocate($bg, $rgb['r'], $rgb['g'], $rgb['b']);

        // 左侧距离计算
        // 未设置则取中间数值
        if($string_left === null)
        {
            $string_left = ($bg_width/2)-($string_width/2);

        // 设置左侧距离则根据海报宽度计算距离
        } elseif($string_left > 0) {
            $string_left = $bg_width/(self::$benchmark_width/$string_left);
            if($nickname_auto_center == 1)
            {
                if($string_width > 180)
                {
                    $string_left -= ($string_width-180)/2;
                }
                if($string_width < 180)
                {
                    $string_left += (180-$string_width)/2;
                }
            }
        }

        // 顶部距离
        if($string_top > 0) {
            $string_top = $bg_height/(((self::$benchmark_width/$bg_width)*$bg_height)/$string_top);
        }
        $string_top += 30;

        // 字体路径
        $font_path = BaseService::$font_path;

        // 生成文本
        imagettftext($bg, 20, 0, $string_left, $string_top, $color, $font_path, $string);
        return $bg;
    }

    /**
     * 字符串宽度计算
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-08T18:24:09+0800
     * @param    [string]          $string [需要计算的字符串]
     */
    private static function StringWidthCalculation($string)
    {
        $width = 0;
        $alnum_count = 0;
        $char_count = 0;
        $len = mb_strlen($string, 'utf-8');
        for($i=0; $i<$len; $i++)
        {
            $temp = mb_substr($string, $i, 1, 'utf-8');
            if($temp == ' ' || ctype_alnum($temp))
            {
                $alnum_count++;
            } else {
                $char_count++;
            }
        }
        $width += $alnum_count*12;
        $width += $char_count*30;
        return $width;
    }

    /**
     * 图像合并
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-06T22:10:49+0800
     * @param    [resource]          $bg         [背景图像资源]
     * @param    [resource]          $img        [被合并图像资源]
     * @param    [int]               $img_width  [被合并图像宽度]
     * @param    [int]               $img_height [被合并图像高度]
     * @param    [int]               $img_left   [被合并图像左侧距离]
     * @param    [int]               $img_top    [被合并图像顶部距离]
     * @param    [int]               $opacity    [透明度（默认100）]
     */
    public static function ImagesMerge($bg, $img, $img_width, $img_height, $img_left = null, $img_top = null, $opacity = 100)
    {
        // 背景图像尺寸
        $bg_width = imagesx($bg);
        $bg_height = imagesy($bg);

        // 左侧距离计算
        // 未设置则取中间数值
        if($img_left === null)
        {
            $img_left = ($bg_width/2)-($img_width/2);

        // 设置左侧距离则根据海报宽度计算距离
        } elseif($img_left > 0) {
            $img_left = $bg_width/(self::$benchmark_width/$img_left);
        }

        // 顶部距离
        if($img_top > 0) {
            $img_top = $bg_height/(((self::$benchmark_width/$bg_width)*$bg_height)/$img_top);
        }

        // 合并操作
        imagecopymerge($bg, $img, $img_left, $img_top, 0, 0, $img_width, $img_height, $opacity);
        return $bg;
    }

    /**
     * 圆形图片
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-06T21:54:44+0800
     * @param    [resource]            $img    [图像资源]
     * @param    [string]              $type   [外观类型（圆形 circle, 圆角 radius）]
     */
    public static function ImagesAppearance($img, $type = 'circle')
    {
        $c = imagecolorallocate($img, 255, 0, 0);
        $w = imagesx($img);
        $h = imagesy($img);
        $tw = ($type == 'circle') ? $w : $w+(0.41*$w);
        $th = ($type == 'circle') ? $h : $h+(0.41*$h);
        imagearc($img, $w/2, $h/2, $tw, $th, 0, 360, $c);
        imagefilltoborder($img, 0, 0, $c, $c);
        imagefilltoborder($img, $w, 0, $c, $c);
        imagefilltoborder($img, 0, $h, $c, $c);
        imagefilltoborder($img, $w, $h, $c, $c);
        imagecolortransparent($img, $c);
        return $img;
    }

    /**
     * 图像大小改变
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-07T00:40:21+0800
     * @param    [string]              $filename    [图像地址（远程地址）]
     * @param    [resource]            $img         [图像资源]
     * @param    [int]                 $width       [设定的宽度]
     * @param    [int]                 $height      [设定的高度]
     */
    public static function ImagesResize($filename, $img, $width, $height)
    {
        // 获取后缀名
        $ext = explode('.', $filename);
        $ext = strtolower($ext[count($ext)-1]);

        // 原始尺寸
        $w = imagesx($img);
        $h = imagesy($img);

        // 创建彩色背景
        $thumb = imagecreatetruecolor($width, $height);

        // png背景设置为白色
        if($ext == 'png')
        {
            imagefilledrectangle($thumb, 0, 0, $width, $height, imagecolorallocate($thumb, 255, 255, 255));
        }
        imagecopyresampled($thumb, $img, 0, 0, 0, 0, $width, $height, $w, $h);
        return $thumb; 
    }

    /**
     * 用户海报刷新
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-08T23:35:26+0800
     * @param    [array]       $params [输入参数]
     */
    public static function UserPosterRefresh($params = [])
    {
        $ret = self::UserPoster(['is_refresh'=>true]);
        if($ret['code'] == 0)
        {
            return DataReturn('刷新成功', 0, $ret['data']);
        }
        return $ret;
    }
}
?>