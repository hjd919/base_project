<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Channels_model extends BF_Model
{
    protected $table_name       = 'channels';
    protected $return_insert_id = true;
    const CHANNELS_CACHEKEY     = 'l_channels';

    public function __construct()
    {
        parent::__construct();
    }

    // 获取需要监控的渠道
    public function check_channel()
    {
        $redis = $this->get_redis();
        // 获取队头渠道id
        $channel_id = $redis->lpop(self::CHANNELS_CACHEKEY);
        if (!$channel_id) {
            // 不存在渠道缓存，查询mysql
            // PS：修改渠道时，删除缓存
            $channels = $this->find_all();
            if (!$channels) {
                // 这个也没有！
                return 'mysql不存在渠道表数据';
            }
            // 缓存mysql数据到缓存中
            foreach ($channels as $key => $channel) {
                if (0 == $key) {
                    $channel_id = $channel->id;
                } else {
                    $redis->rpush(self::CHANNELS_CACHEKEY, $channel->id);
                }
            }
        }
        // 把队头渠道id放回队尾，循环监控
        $redis->rpush(self::CHANNELS_CACHEKEY, $channel_id);

        $channel = $this->select('slug,name')->find($channel_id);
        if (!$channel) {
            return 'mysql不存在渠道表数据';
        }

        return ['channel_id' => $channel_id, 'channel_name' => $channel->slug, 'channel_cname' => $channel->name];
    }

    public function insert($data = null)
    {
        $channel_id = parent::insert($data);

        $redis = $this->get_redis();
        $redis->lpush(self::CHANNELS_CACHEKEY, $channel_id);

        return $channel_id;
    }
}
