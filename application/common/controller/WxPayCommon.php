<?php
namespace app\common\controller;

use think\Controller;
use think\Request;

class WxPayCommon extends Controller{

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        date_default_timezone_set('Asia/Shanghai');
        $this->autoload();

    }

    public function autoload(){
        //引入类
        import('vendor.autoload', EXTEND_PATH);
        $GLOBALS['PAY_CONFIG'] = [

            'appid'			     => 'wx1676a80d208a4d8e',
            'mch_id'		     => '1543181311',
            'key'			     => 'CI92cdc8CJHFC76565c1llC30CwX5566',
            'appsecret'          =>'7960ec07783ff1799d31bbc5330315de',
//            'pay_notify_url'	 => 'https://scanpay.71baomu.com/index/Wxnotify',
            'pay_notify_url'	 => 'https://pay.kuaifu.com.cn/index/Wxnotify',
            'certPath'	         => __DIR__ . '/cert/wxpay_cert/apiclient_cert.pem',
            'keyPath'	         => __DIR__ . '/cert/wxpay_cert/apiclient_key.pem',
        ];

    }




    //获取openid方法
    public function getOpenid(){
        //通过code获得openid
        if (!isset($_GET['code'])) {
            //触发微信返回code码
//            $baseUrl = urlencode("http://wxapp.kuaifuwang.cn/WxPay/wxpay/money/$money/id6d/$id6d");
            $baseUrl = urlencode("http://scanpay.71baomu.com/index/WxappPay/wxpay/");
            $url = $this->_CreateOauthUrlForCode($baseUrl);

            Header("Location: $url");
            exit();
        } else {
            //获取code码，以获取openid
            $code = $_GET['code'];
            $openid = $this->getOpenidFromMp($code);
            $data=[];
            if($openid){
                $data = ["status" => 2, "openid" => $openid];
            }
            return $data;
        }

    }

    /**
     *
     * 构造获取code的url连接
     * @param string $redirectUrl 微信服务器回跳的url，需要url编码
     *
     * @return 返回构造好的url
     */
    private function _CreateOauthUrlForCode($redirectUrl)
    {

        $urlObj["appid"] = $GLOBALS['PAY_CONFIG']['appid'];
        $urlObj["redirect_uri"] = "$redirectUrl";
        $urlObj["response_type"] = "code";
        $urlObj["scope"] = "snsapi_base";
        $urlObj["state"] = "STATE" . "#wechat_redirect";
        $bizString = $this->ToUrlParams($urlObj);
        return "https://open.weixin.qq.com/connect/oauth2/authorize?" . $bizString;
    }
    /**
     *
     * 拼接签名字符串
     * @param array $urlObj
     *
     * @return 返回已经拼接好的字符串
     */
    private function ToUrlParams($urlObj)
    {
        $buff = "";
        foreach ($urlObj as $k => $v) {
            if ($k != "sign") {
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }

    /**
     *
     * 通过code从工作平台获取openid机器access_token
     * @param string $code 微信跳转回来带上的code
     *
     * @return openid
     */
    public function GetOpenidFromMp($code)
    {
        $url = $this->__CreateOauthUrlForOpenid($code);

        //初始化curl
        $ch = curl_init();
        $curlVersion = curl_version();

        $ua = "WXPaySDK/3.0.9 (" . PHP_OS . ") PHP/" . PHP_VERSION . " CURL/" . $curlVersion['version'] . " "
            . $GLOBALS['PAY_CONFIG']['mch_id'];

        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->curl_timeout);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
  //TODO：这里设置代理机器，只有需要代理的时候才设置，不需要代理，请设置为0.0.0.0和0
        $proxyHost = "0.0.0.0";
        $proxyPort = 0;
        if ($proxyHost != "0.0.0.0" && $proxyPort != 0) {
            curl_setopt($ch, CURLOPT_PROXY, $proxyHost);
            curl_setopt($ch, CURLOPT_PROXYPORT, $proxyPort);
        }
        //运行curl，结果以jason形式返回
        $res = curl_exec($ch);
        curl_close($ch);
        //取出openid
        $data = json_decode($res, true);
        $this->data = $data;
        $openid = $data['openid'];

        return $openid;
    }

    /**
     *
     * 构造获取open和access_toke的url地址
     * @param string $code，微信跳转带回的code
     *
     * @return 请求的url
     */
    private function __CreateOauthUrlForOpenid($code)
    {
        $config = new WxPayConfig();
        $urlObj["appid"] = $GLOBALS['PAY_CONFIG']['appid'];
        $urlObj["secret"] = $GLOBALS['PAY_CONFIG']['appsecret'];
        $urlObj["code"] = $code;
        $urlObj["grant_type"] = "authorization_code";
        $bizString = $this->ToUrlParams($urlObj);
        return "https://api.weixin.qq.com/sns/oauth2/access_token?" . $bizString;
    }




}