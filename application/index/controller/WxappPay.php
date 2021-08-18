<?php
namespace app\index\controller;


use app\common\controller\WxPayCommon;
use app\index\model\AuthOrder;
use app\index\model\Bill;
use app\index\model\Company;
use think\Db;

class WxappPay extends WxPayCommon{

    public function wxpay(){
        // 公共配置
        $params = new \Yurun\PaySDK\Weixin\Params\PublicParams();
        $params->appID = $GLOBALS['PAY_CONFIG']['appid'];
        $params->mch_id = $GLOBALS['PAY_CONFIG']['mch_id'];
        $params->key = $GLOBALS['PAY_CONFIG']['key'];
        $open_id=$this->request->get('openid','','trim,htmlspecialchars,strip_tags');
        $company_id= $this->request->get('company_id','','trim,htmlspecialchars,strip_tags');
        $auth_phone= $this->request->get('auth_phone','','trim,htmlspecialchars,strip_tags');
//        //检查公司是否存在
//        $company_info=Company::getCompany(true,$company_id);
//        if(!$company_info){
//            $data['code'] = 40002;
//            $data['msg'] = '订单信息有误请重试！';
//            return json($data);
//        }


        if(empty($auth_phone)){
            $data['code'] = 40002;
            $data['msg'] = '订单信息有误请重试！';
            return json($data);
        }
        mfile($open_id,'open_Id','wangha','test');
        // SDK实例化，传入公共配置
        $pay = new \Yurun\PaySDK\Weixin\SDK($params);
        // 支付接口
        $request = new \Yurun\PaySDK\Weixin\JSAPI\Params\Pay\Request();

        $bill = new AuthOrder();
        $order_id = $bill->addAuthOrder(['company_id' => 0]);
        if (!$order_id) {
            $data['code'] = 10004;
            $data['msg'] = '订单信息有误请重试！';
            return json($data);
        }
        $orderId= $order_id;
        Db::table('cloud_auth_order')->where('id', $order_id)->delete();

        $ip=getIpInfo();
        $request->body = '充值认证'; // 商品描述
        $request->out_trade_no = 'wxapp'.$orderId; // 订单号
        $request->total_fee = 1; // 订单总金额，单位为：分
        $request->spbill_create_ip = $ip; // 客户端ip
        $request->notify_url = $GLOBALS['PAY_CONFIG']['pay_notify_url']; // 异步通知地址
        $request->openid = $open_id; // 必须设置openid
        $request->attach = $company_id.'|'.$auth_phone; //公司id | 认证手机号

        // 调用接口
        $result = $pay->execute($request);

        mfile($result,'jsapi111','wangha','test');
        if ($pay->checkResult())
        {
            $request = new \Yurun\PaySDK\Weixin\JSAPI\Params\JSParams\Request();
            $request->prepay_id = $result['prepay_id'];
            $jsapiParams = $pay->execute($request);
            mfile($jsapiParams,'jsapi','wangha','test');
            // 最后需要将数据传给js，使用WeixinJSBridge进行支付
            echo json_encode($jsapiParams);
            exit();
        }
    }



}