<?php
defined('BASEPATH') or die('Access restricted!');

if (!defined('NOTIFY_PATH')) {
    define('NOTIFY_PATH', APPPATH . 'libraries/notification/');
}

class My_notify
{
    protected $appkey           = '';
    protected $appMasterSecret  = '';
    protected $timestamp        = null;
    protected $validation_token = null;

    /**
     * 初始化alioss
     */
    public function __construct($config)
    {
        $this->appkey          = $config['key'];
        $this->appMasterSecret = $config['secret'];
        $this->timestamp       = strval(time());
    }

    public function channel_product($notify_type, $products, $channel)
    {
        if ('new_notify' == $notify_type) {
            $message = [
                'ticker'      => "'{$channel['channel_cname']}'出新产品了",
                'title'       => '监测app',
                'text'        => "'{$channel['channel_cname']}'出新产品了",
                'extra_field' => ['key' => 'channel_id', 'value' => $channel['channel_id']],
                'badge'       => count($products),
            ];
            $ci = &get_instance();
            $ci->show_log($message, '新品通知');
            $this->sendIOSBroadcast($message);
        }
    }

    /**
     * 发送安卓广播
     */
    public function sendAndroidBroadcast($message)
    {
        include_once NOTIFY_PATH . 'android/AndroidBroadcast.php';
        try {
            $brocast = new AndroidBroadcast();
            $brocast->setAppMasterSecret($this->appMasterSecret);
            $brocast->setPredefinedKeyValue("appkey", $this->appkey);
            $brocast->setPredefinedKeyValue("timestamp", $this->timestamp);
            $brocast->setPredefinedKeyValue("ticker", $message['ticker']);
            $brocast->setPredefinedKeyValue("title", $message['title']);
            $brocast->setPredefinedKeyValue("text", $message['text']);
            $brocast->setPredefinedKeyValue("after_open", "go_app");
            // Set 'production_mode' to 'false' if it's a test device.
            // For how to register a test device, please see the developer doc.
            $brocast->setPredefinedKeyValue("production_mode", "true");
            // [optional]Set extra fields
            $brocast->setExtraField($message['extra_field']['key'], $message['extra_field']['value']);
            // print("Sending broadcast notification, please wait...\r\n");
            $brocast->send();
            print("Sent SUCCESS\r\n");
        } catch (Exception $e) {
            print("Caught exception: " . $e->getMessage());
        }
    }

    /**
     * 发送IOS广播
     */
    public function sendIOSBroadcast($message = '')
    {
        include_once NOTIFY_PATH . 'ios/IOSBroadcast.php';
        try {
            $brocast = new IOSBroadcast();
            $brocast->setAppMasterSecret($this->appMasterSecret);
            $brocast->setPredefinedKeyValue("appkey", $this->appkey);
            $brocast->setPredefinedKeyValue("timestamp", $this->timestamp);

            $brocast->setPredefinedKeyValue("alert", $message['text']);
            $brocast->setPredefinedKeyValue("badge", isset($message['badge']) ? $message['badge'] : 0);
            $brocast->setPredefinedKeyValue("sound", "chime");
            // Set 'production_mode' to 'true' if your app is under production mode
            $brocast->setPredefinedKeyValue("production_mode", ENVIRONMENT == 'production' ? 'true' : "false");
            // Set customized fields
            $brocast->setCustomizedField($message['extra_field']['key'], $message['extra_field']['value']);
            // print("Sending broadcast notification, please wait...\r\n");
            $brocast->send();
            print("Sent SUCCESS\r\n");
        } catch (Exception $e) {
            print("Caught exception: " . $e->getMessage());
        }
    }

    public function sendIOSUnicast()
    {
        include_once NOTIFY_PATH . 'ios/IOSUnicast.php';
        try {
            $unicast = new IOSUnicast();
            $unicast->setAppMasterSecret($this->appMasterSecret);
            $unicast->setPredefinedKeyValue("appkey", $this->appkey);
            $unicast->setPredefinedKeyValue("timestamp", $this->timestamp);
            // Set your device tokens here
            $unicast->setPredefinedKeyValue("device_tokens", "f2c964eb84d21d97a9e1eba6304b86c6fca7cfc4642f893a1e17180eef1d6608");
            $unicast->setPredefinedKeyValue("alert", "IOS 单播测试");
            $unicast->setPredefinedKeyValue("badge", 0);
            $unicast->setPredefinedKeyValue("sound", "chime");
            // Set 'production_mode' to 'true' if your app is under production mode
            $unicast->setPredefinedKeyValue("production_mode", "false");
            // Set customized fields
            $unicast->setCustomizedField("test", "helloworld");
            print("Sending unicast notification, please wait...\r\n");
            $unicast->send();
            print("Sent SUCCESS\r\n");
        } catch (Exception $e) {
            print("Caught exception: " . $e->getMessage());
        }
    }
}
