<?php
defined('BASEPATH') or die('Access restricted!');

class Sign
{
    private $key;
    private $ci;

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->config->load('sign');
        $this->key = $this->ci->config->item('key');
    }

    /**
     * 计算签名
     */
    public function calculate($input)
    {
        $key      = $this->key;
        $signPars = "";
        ksort($input);
        foreach ($input as $k => $v) {
            if ("sign" != $k && "" !== $v) {
                $signPars .= $k . "=" . $v . "&";
            }
        }
        $signPars .= "key=" . $key;
		//mylog('etst',$signPars,1);
        $hash = strtolower(hash('sha256', $signPars));
        return $hash;
    }

    /**
     * 比较签名
     */
    public function compare($input, $sign)
    {
        $hash = $this->calculate($input);
        $rst  = $hash === $sign;
        return $rst;
    }
}
