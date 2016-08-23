<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Memcached settings
| -------------------------------------------------------------------------
| Your Memcached servers can be specified below.
|
|    See: http://codeigniter.com/user_guide/libraries/caching.html#memcached
|
 */
$config['redis']['socket_type'] = 'tcp'; //`tcp` or `unix`
$config['redis']['socket']      = '/var/run/redis.sock'; // in case of `unix` socket type
$config['redis']['host']        = '60.205.58.24';
$config['redis']['password']    = 'Hjd2015Xiaozi~!@#';
$config['redis']['port']        = 6379;
$config['redis']['db']          = 5;
$config['redis']['timeout']     = 0;
