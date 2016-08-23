<?php defined('BASEPATH') or exit('No direct script access allowed');

class Base_Controller extends CI_Controller
{
    private $_redis = null;

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();

        $this->load->helper('base');
    }

    /**
     * 初始化视图方面的依赖
     */
    public function _init_view()
    {
        $this->load->helper(['url']);

        $this->load->library(['view']);

        $this->view->assign('img_prefix', base_url('public/image/'));

        // 阿里图片
        if ($this->debug) {
            $aliimg_prefix = 'http://juka-test.img-cn-beijing.aliyuncs.com';
        } else {
            $aliimg_prefix = 'http://xzhongbao.img-cn-beijing.aliyuncs.com';
        }
        $this->view->assign('aliimg_prefix', $aliimg_prefix);

        $this->view->assign('debug', $this->debug);

    }

    // 微信
    public function _load_wechat()
    {
        $this->config->load('wechat');
        $wechat_configs = $this->config->item('wechat');
        $this->load->library('wechat', $wechat_configs);
    }

    public function send_kefu_message($openid, $msg_content)
    {
        $this->_load_wechat();
        $message = ["touser" => $openid, "msgtype" => "text", "text" => ['content' => $msg_content]];
        $this->wechat->sendCustomMessage($message);
        if (!empty($this->wechat->errMsg)) {
            log_message('error', $this->wechat->errMsg);
        }

    }

    // 获取redis
    public function get_redis()
    {
        if (!$this->_redis) {
            $this->config->load('redis');
            $config = $this->config->item('redis');
            try {
                $this->_redis = new Redis();
                $this->_redis->connect($config['host'], $config['port'], $config['timeout']);

                if (isset($config['password'])) {
                    $this->_redis->auth($config['password']);
                }

                $this->_redis->select($config['db']);
            } catch (Exception $e) {
                throw new Exception("Error Processing Request" . $e->getMessage, 1);
            }

        }
        return $this->_redis;
    }

    public function load_view($tpl)
    {
        $this->load->view($tpl, $this->data);
    }

    public function show_error($error)
    {
        die($error);
    }

    public function show_log($log, $title = '', $is_stop = false)
    {
        echo "\r\n" . $title . "\r\n" . var_export($log, true) . "\r\n";
        echo "\r\n==============================\r\n";
        if ($is_stop) {
            die;
        }
    }

    public function show_stop($log, $title = '')
    {
        echo $title . "\r\n" . var_export($log, true) . "\r\n";
        echo "\r\n==============================\r\n";
        die;
    }
}

class Api_Controller extends Base_Controller
{
    protected $load_db = true;

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();

        // 加载类库
        if ($this->load_db) {
            $this->load->database();
        }
    }

}

class Cron_Controller extends Base_Controller
{
    protected $load_db = true;

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();

        // 加载类库
        if ($this->load_db) {
            $this->load->database();
        }
    }

}
