<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Products extends Api_Controller
{
    protected $load_db = true;

    public function __construct()
    {
        parent::__construct();
    }

    // 产品列表
    public function product_list()
    {
        $channel_id = $this->input->get_post('channel_id');
        $page       = $this->input->get_post('page');
        $limit      = 15;
        $offset     = ($page - 1) * $limit;

        $this->load->model('products_model');
        $products = $this->products_model
            ->limit($limit, $offset)
            ->as_array()
            ->find_all();
        if (!$products) {
            die_json([]);
        }

        // 获取新品缓存集合
        $redis        = $this->get_redis();
        $new_products = $redis->sMembers($this->products_model->new_products_cachekey($channel_id));

        // TODO获取关键词提醒集合

        foreach ($products as $key => &$product) {
            if ($new_products && in_array($product['ad_id'], $new_products)) {
                $product['is_new'] = 1;
            } else {
                $product['is_new'] = 0;
            }
        }

        die_json($products);
    }

}
