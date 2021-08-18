<?php
namespace app\ghpay\controller;

use app\common\controller\GhpayCommon;
use app\index\model\AuthOrder;
use think\Db;

class Ghpay extends GhpayCommon{
    //微信小程序支付
    public function wxMiniPay(){



        //生成订单号
        $bill = new AuthOrder();
        $order_id = $bill->addAuthOrder(['company_id' => 0]);
        if (!$order_id) {
            $data['code'] = 10004;
            $data['msg'] = '订单信息有误请重试！';
            return json($data);
        }
        $orderId= $order_id;
        Db::table('cloud_auth_order')->where('id', $order_id)->delete();

        //私钥注意，需要带-----BEGIN RSA PRIVATE KEY-----和-----END RSA PRIVATE KEY-----，且为PCKS1格式。勿使用PCKS8格式。
        $prikey = "-----BEGIN RSA PRIVATE KEY-----\n". wordwrap($GLOBALS['PAY_CONFIG']['prikey'], 64, "\n", true) ."\n-----END RSA PRIVATE KEY-----";
        $url = "com.icbc.upay.wxpay.unifiedorder";

        $apiClient = new \ApiClient($prikey);
        $apiRequest = new \ApiRequest("https://web.zj.icbc.com.cn/api", $url, $GLOBALS['PAY_CONFIG']['appid']);
        $apiRequest->setRequestField("instId",$GLOBALS['PAY_CONFIG']['appid']);
        $apiRequest->setRequestField("subInstId",$GLOBALS['PAY_CONFIG']['subinstid']); //子商户编号
        $apiRequest->setRequestField("deviceInfo",'Mini1');//设备号
        $apiRequest->setRequestField("body",'认证充值');//商品描述
        $apiRequest->setRequestField("attach",'123');//商户自定义参数
        $apiRequest->setRequestField("orderNo",$orderId);//订单号
        $apiRequest->setRequestField("amount",'0.01');//总金额
        $apiRequest->setRequestField("spbillCreateIp",getIpInfo());//终端ip
        $apiRequest->setRequestField("notifyUrl",$GLOBALS['PAY_CONFIG']['pay_notify_url']);//通知地址
        $apiRequest->setRequestField("tradeType",'JSAPI_MINI');//交易类型
        $apiRequest->setRequestField("subOpenid",'oZMbZ5GJTTwsqx7OaSAmL4zjjJQw');//用户子标识
        $apiRequest->setRequestField("stlFlag",'00');//
//        $apiRequest->setRequestField("appid",$GLOBALS['PAY_CONFIG']['wxappid']);//appid

        $apiResponse = $apiClient->execute($apiRequest);
        $rt= json_decode($apiResponse->getSignBlk(),true);
        $check = $apiResponse->isCheckValid();

        echo "<pre>";
        print_r($apiRequest->getRequestFields());
        echo "</pre>";
        echo "<pre>";
        print_r($check);
        echo "</pre>";
        echo "<pre>";
        print_r($rt);
        echo "</pre>";

    }


}