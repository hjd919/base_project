<?php

defined('BASEPATH') or die('Access restricted!');

class My_redis extends Redis
{
    private static $_instance;

    public function __construct()
    {
    }

    //单例方法
    public static function get_instance()
    {
        if (!isset(self::$_instance)) {
            $config = array();
            $CI     = &get_instance();
            $CI->config->load('redis');
            $config = $CI->config->item('redis');
            // if ($CI->config->load('redis', true, true)) {
            //     $config += $CI->config->item('redis');
            // }

            // $config = array_merge(self::$_default_config, $config);

            // $this->_redis = new Redis();
            $c               = __CLASS__;
	    //$config = $config['redis'];
            self::$_instance = new $c;
	    self::$_instance->connect($config['host'], $config['port'], $config['timeout']);
	
            /*try
            {
                if ($config['socket_type'] === 'unix') {
                    $success = self::$_instance->connect($config['socket']);
                } else // tcp socket
                {
                    $success = self::$_instance->connect($config['host'], $config['port'], $config['timeout']);
                }

                if (!$success) {
                    log_message('debug', 'Cache: Redis connection refused. Check the config.');
                    return false;
                }
            } catch (RedisException $e) {
                log_message('debug', 'Cache: Redis connection refused (' . $e->getMessage() . ')');
                return false;
            }*/

            if (isset($config['password'])) {
                self::$_instance->auth($config['password']);
            }

            self::$_instance->select($config['db']);	

            // Initialize the index of serialized values.
            /*$serialized = self::$_instance->sMembers('_ci_redis_serialized');
            if (!empty($serialized)) {
                $this->_serialized = array_flip($serialized);
            }*/

        }
        return self::$_instance;
    }

    public function __clone()
    {
        trigger_error('Clone is not allow', E_USER_ERROR);
    }
}
