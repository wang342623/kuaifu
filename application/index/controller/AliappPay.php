<?php
namespace app\index\controller;

use aop\AopClient;
use aop\request\AlipaySystemOauthTokenRequest;
use app\common\controller\AliAppCommon;
use app\index\model\AuthOrder;
use app\index\model\Company;
use app\index\model\PhoneAuthentication;
use think\Db;
use think\Log;

class AliappPay extends AliAppCommon{

    public function aliappPay(){

        $user_id = $this->request->param('user_id','','trim,htmlspecialchars,strip_tags');
        $auth_phone = $this->request->param('auth_phone','','trim,htmlspecialchars,strip_tags');
        $company_id= $this->request->param('company_id','','trim,htmlspecialchars,strip_tags');

        if(empty($user_id)){
            return json(['code'=>'4004','msg'=>'user_id empty']);
        }
        if(empty($auth_phone)){
            return json(['code'=>'4004','msg'=>'auth_phone empty']);
        }
        //检查公司是否存在
//        $company_info=Company::getCompany(true,$company_id);
//        if(!$company_info){
//            $data['code'] = 40002;
//            $data['msg'] = '订单信息有误请重试！';
//            return json($data);
//        }

//        $user_id='2088022681662014'; //测试
// 公共配置
        $params = new \Yurun\PaySDK\AlipayApp\Params\PublicParams();
        $params->appID = $GLOBALS['PAY_CONFIG']['appid'];
        $params->appPrivateKey = $GLOBALS['PAY_CONFIG']['privateKey'];

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

// SDK实例化，传入公共配置
        $pay = new \Yurun\PaySDK\AlipayApp\SDK($params);

// 支付接口
        $request = new \Yurun\PaySDK\AlipayApp\MiniApp\Params\Pay\Request();
        $request->notify_url = $GLOBALS['PAY_CONFIG']['notify_url']; // 支付后通知地址（作为支付成功回调，这个可靠）
        $request->businessParams->out_trade_no = 'ali'.$orderId; // 商户订单号
        $request->businessParams->total_amount = 0.01; // 价格
// 买家用户号需要授权，请用：https://github.com/Yurunsoft/YurunOAuthLogin
        $request->businessParams->buyer_id = $user_id; // 传入买家的支付宝唯一用户号（2088开头的16位纯数字）
        $request->businessParams->subject = '充值认证'; // 商品标题

        $passback_params = json_encode(['auth_phone'=>$auth_phone,'company_id'=>$company_id]);
        $request->businessParams->passback_params = urlencode($passback_params); // 公用回传参数
// 调用接口
        $result = $pay->execute($request);

        mfile($result,'apppay','wangha','alipay_app');
        mfile($pay->checkResult(),'checkresult','wangha','alipay_app');
        mfile($pay->getError(),'$pay->getError()','wangha','alipay_app');
        mfile($pay->getErrorCode(),'$pay->getErrorCode()','wangha','alipay_app');
        return json(['code'=>'200','data'=>['trade_no'=>$result['alipay_trade_create_response']['trade_no']]]);
//        var_dump('error:', $pay->getError(), 'error_code:', $pay->getErrorCode());
    }

    public function asyncback(){
        // 公共配置
        $params = new \Yurun\PaySDK\AlipayApp\Params\PublicParams;
        $params->appID = $GLOBALS['PAY_CONFIG']['appid'];
        $params->appPublicKey = $GLOBALS['PAY_CONFIG']['publicKey'];
        $params->appPrivateKey = $GLOBALS['PAY_CONFIG']['privateKey'];
//        $params->apiDomain = 'https://openapi.alipaydev.com/gateway.do'; // 设为沙箱环境，如正式环境请把这行注释
        // SDK实例化，传入公共配置
        $pay = new \Yurun\PaySDK\AlipayApp\SDK($params);
        $param = $this->request->post();
        $notify_url = $pay->verifyCallback($param);
        // 通知验证成功，可以通过POST参数来获取支付宝回传的参数

        if ($notify_url) {
            mfile($notify_url, "['支付宝即时到账异步通知']", 'alipayapp', 'notify');
            mfile($param, "['支付宝即时到账异步通知param']", 'alipayapp', 'notify');

            //成功 处理参数
            $passback_params=json_decode(urldecode($param['passback_params']),true);
            mfile($passback_params, "['passback_params']", 'alipayapp', 'notify');
            $company_id=$passback_params['company_id'];
            $auth_phone = $passback_params['auth_phone'];

            if(!empty($company_id)){
                    $order_num = $param['out_trade_no'];

                    $info= AuthOrder::getAuthOrder('open_id',$order_num);
                    if($info){
                        mfile($info,$order_num . "订单存在", "alipayapp", "notify");
                        return true;
                    }
                    $auth_order_data=[
                        "id"=>substr($order_num,1),
                        "order_num"=>$order_num,
                        "company_id"=>$company_id,
                        "open_id"=>$param['buyer_id'],
                        "transaction_id"=>$param["trade_no"],
                        "pay_type"=>"wxpay",
                        "total_fee"=>$param['invoice_amount'] ,
                        "pay_time"=>date("Y-m-d H:i:s"),
                        "status"=>0,
                    ];
                    $auth_order=  new AuthOrder();
                    $auth=$auth_order->addAuthOrder($auth_order_data);
                    if($auth){
                        $is_attes= AuthOrder::getAuthAccount($param['buyer_id'],$company_id);
                        mfile($is_attes, $company_id . "认证信息", "alipayapp", "notify");
                        if($is_attes==1){
                            $host=config('SYSTEM_HOST.aqp_api');
                            $url="/authapplet/domain/applets/setAuthToPayment";
                            $auth_data=[
                                "company_id"=>$company_id,
                                "open_id"=>$param['buyer_id'],
                                'auth_phone'=>$auth_phone,
                                "auth_type"=>1,
                                "sign"=>md5("fyweao&@^#()@)#!><?F".$auth_phone.'1'.$company_id.$param['buyer_id'])
                            ];
                            $re=json_decode(https_request($host.$url,$auth_data),true);
//                        if($re['success'] == 1){
                            //修改认证信息
                            $phone_authentication= new PhoneAuthentication();
                            $phone_tr=$phone_authentication->updatePhoneAuthentication($company_id,$auth_phone,['type'=>2]);
                            if(!$phone_tr){
                                mfile($phone_authentication->getError(),"sqls", "alipayapp", "notify");
                            }

//                        }
                            mfile($auth_data, $company_id . "添加认证参数", "alipayapp", "notify");
                            mfile("fyweao&@^#()@)#!><?F".$auth_phone.'2'.$company_id.$param['buyer_id'], $company_id . "添加认证参数", "wxparam", "wxparam");
                            mfile($re, $company_id . "添加认证成功", "alipayapp", "notify");
                        }
                        mfile($auth, $company_id . "添加订单成功", "alipayapp", "notify");
                        return true;
                    }elsE{
                        mfile($auth_order_data,  "添加订单失败", "alipayapp", "notify");
                        return false;
                    }
            }


        }

    }



}