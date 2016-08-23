<?php
/**
 * 公共函数
 */
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * 获取图片
 */
function get_img($img)
{
    // 默认图片
    if (!$img) {
        return 'https://avatar.tower.im/cbe52980802d45918702b79c6501a1eb';
    }
    // 全路径
    if (strpos($img, 'http') !== false) {
        return $img;
    }
    //阿里云
    return 'https://avatar.tower.im/cbe52980802d45918702b79c6501a1eb';
}

/**
 * 替换cp串中的
 * idfa,ip,back
 */
function replace_cp_params($origin_str, $idfa, $ip, $callback_url = '')
{
    $origin_str = str_replace('{idfa}', $idfa, $origin_str);
    $origin_str = str_replace('{ip}', $ip, $origin_str);
    return str_replace('{back}', $callback_url, $origin_str);
}

/**
 * 获取ip的地理位置
 */
function get_ip_lookup($ip = '')
{
    $res = @file_get_contents('http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=js&ip=' . $ip);
    if (empty($res)) {return false;}
    $jsonMatches = array();
    preg_match('#\{.+?\}#', $res, $jsonMatches);
    if (!isset($jsonMatches[0])) {return false;}
    $json = json_decode($jsonMatches[0], true);
    if (isset($json['ret']) && 1 == $json['ret']) {
        $json['ip'] = $ip;
        unset($json['ret']);
    } else {
        return false;
    }
    return $json;
}

/**
 * 抓取网络图片
 * @param  string $url      网络图片地址
 * @param  string $filename 存储文件名，绝对路径
 * @return string           $filename
 */
function grab_img($url, $filename = "")
{
    if ("" == $url) {
        return false;
    }

    if ("" == $filename) {
        $ext = strrchr($url, ".");
        if (".gif" != $ext && ".jpg" != $ext && ".png" != $ext) {
            return false;
        }

        $filename = date("YmdHis") . $ext;
    }

    ob_start();
    readfile($url);
    $img = ob_get_contents();
    ob_end_clean();
    $size = strlen($img);

    $fp2 = @fopen($filename, "a");
    fwrite($fp2, $img);
    fclose($fp2);

    return $filename;
}

/**
 * 分转换为元
 */
function to_yuan($fen)
{
    return sprintf('%.2f', $fen / 100);
}

/**
 * 友好时间显示
 * @param $time
 * @return bool|string
 */
function friend_date($time)
{
    if (is_string($time)) {
        $time = strtotime($time);
    }
    if (!$time) {
        return false;
    }

    $fdate = '';
    $d     = time() - intval($time);
    $ld    = $time - mktime(0, 0, 0, 0, 0, date('Y')); //得出年
    $md    = $time - mktime(0, 0, 0, date('m'), 0, date('Y')); //得出月
    $byd   = $time - mktime(0, 0, 0, date('m'), date('d') - 2, date('Y')); //前天
    $yd    = $time - mktime(0, 0, 0, date('m'), date('d') - 1, date('Y')); //昨天
    $dd    = $time - mktime(0, 0, 0, date('m'), date('d'), date('Y')); //今天
    $td    = $time - mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')); //明天
    $atd   = $time - mktime(0, 0, 0, date('m'), date('d') + 2, date('Y')); //后天
    if (0 == $d) {
        $fdate = '刚刚';
    } else {
        switch ($d) {
            case $d < $atd:
                $fdate = date('Y年m月d日', $time);
                break;
            case $d < $td:
                $fdate = '后天' . date('H:i', $time);
                break;
            case $d < 0:
                $fdate = '明天' . date('H:i', $time);
                break;
            case $d < 60:
                $fdate = $d . '秒前';
                break;
            case $d < 3600:
                $fdate = floor($d / 60) . '分钟前';
                break;
            case $d < $dd:
                $fdate = floor($d / 3600) . '小时前';
                break;
            case $d < $yd:
                $fdate = '昨天' . date('H:i', $time);
                break;
            case $d < $byd:
                $fdate = '前天' . date('H:i', $time);
                break;
            case $d < $md:
                $fdate = date('m月d日 H:i', $time);
                break;
            case $d < $ld:
                $fdate = date('m月d日', $time);
                break;
            default:
                $fdate = date('Y年m月d日', $time);
                break;
        }
    }
    return $fdate;
}

/**
 * 记录日志
 */
function mylog($label, $data = null, $is_error = false)
{
    if ($is_error) {
        log_message('error', $label . '===' . var_export($data, true));
    } else {
        log_message('debug', $label . '===' . var_export($data, true));
    }
}

/*
 * 返回成功失败的数据格式
 */
if (!function_exists('res')) {

    function res($message, $code = null, $field = null)
    {
        // 失败
        if ($code) {
            return array('code' => $code, 'message' => $message);
        }

        // 成功
        if (!$code) {
            if (is_array($message)) {
                $message['code'] = 0;
                return $message;
            }
            return array('code' => 0, 'message' => $message);
        }
    }
}

/*
 * json格式输出
 */
if (!function_exists('die_json')) {

    function die_json($message, $code = 0)
    {
        if (is_array($message) || is_object($message)) {
            $data = array(
                'data'    => $message,
                'code'    => 0,
                'message' => 'ok',
            );
        } else {
            $data = array(
                'data'    => '',
                'code'    => $code,
                'message' => $message,
            );
        }
        //mylog('response', $data);

        $ci = &get_instance();
        $ci->benchmark->mark('api_end');
        mylog('elapsed_time', $ci->benchmark->elapsed_time('api_start', 'api_end'));

        header("Content-type: application/json");
        $data_str = json_encode($data, JSON_UNESCAPED_UNICODE);
        if (isset($_GET['callback'])) {
            $data_str = $_GET['callback'] . '(' . $data_str . ')';
        }
        die($data_str);
    }
}

if (!function_exists('generate_serial_days')) {
    /**
     * 生成连续的日期数组
     */
    function generate_serial_days($startDate, $day = 0)
    {
        if (!$startDate instanceof \DateTime) {
            $startDate = \DateTime::createFromFormat('Y-m-d', date('Y-m-d', strtotime($startDate)));
        }

        if (!$startDate) {
            throw new \InvalidArgumentException('日期格式错误');
        }

        $day     = intval($day);
        $dateArr = [$startDate->format('Y-m-d')];

        $day--;
        while ($day > 0) {
            $startDate->add(\DateInterval::createFromDateString('1 days'));
            $dateArr[] = $startDate->format('Y-m-d');
            $day--;
        }

        return $dateArr;
    }

}

if (!function_exists('auth_aide')) {
    /**
     * 认证小助手
     */
    function auth_aide()
    {
        $ci = &get_instance();
        if (ENVIRONMENT == 'development') {
            $idfa      = $ci->input->get('idfa');
            $uniqueid  = $idfa;
            $jailbreak = $idfa;
        } else {
            $sign      = $ci->input->get_request_header('X-YZ-SIGN', true);
            $idfa      = $ci->input->get_request_header('X-YZ-IDFA', true);
            $uniqueid  = $ci->input->get_request_header('X-YZ-UNIQUEID', true);
            $time      = $ci->input->get_request_header('X-YZ-TIME', true);
            $scheme    = $ci->input->get_request_header('X-YZ-SCHEME', true);
            $jailbreak = $ci->input->get_request_header('X-YZ-JAILBREAK', true);
            if (!$sign || !$idfa || !$uniqueid || !$time || !$scheme || null === $jailbreak) {
                die_json('error', 3012);
            }

            // 验证是否超时 半小时内有效
            if ($time + 600 < time()) {
                die_json('error', 3013);
            }

            $ci->load->library('sign');
            $input = [
                'uniqueid'  => $uniqueid,
                'idfa'      => $idfa,
                'time'      => $time,
                'scheme'    => $scheme,
                'jailbreak' => $jailbreak,
            ];
            if (!$ci->sign->compare($input, $sign)) {
                die_json('sign wrong', 3012);
            }
        }

        return [$idfa, $uniqueid, $jailbreak];
    }

}

if (!function_exists('auth_helper')) {
    /**
     * 认证小助手
     */
    function auth_helper()
    {
        $ci = &get_instance();

        $sign = $ci->input->get('signKey');
        $time = $ci->input->get('timeInterval');
        if (!$sign || !$time) {
            return false;
        }

        // 验证是否超时 半小时内有效
        if ($time + 600 < time()) {
            return false;
        }

        $ci->load->library('sign');
        $input = [
            'time' => $time,
            'sign' => $sign,
        ];
        if (!$ci->sign->compare($input, $sign)) {
            return false;
        }
        return true;
    }

}
