<?php

namespace app\index\controller;


use app\common\controller\AliPayCommon;
use app\index\model\AccountTemporary;
use app\index\model\BarcodePayment;
use app\index\model\Bill;
use app\index\model\Company;
use app\index\model\CompanyAccount;
use app\index\model\Worker;
use think\App;
use think\Controller;
use think\Db;
use think\Log;
use think\Request;

class Index extends AliPayCommon
{
    public function index()
    {
       echo PHP_VERSION;
    }

    public function pailpay()
    {
        //接参
        $post = $this->request->post('', [], 'trim,htmlspecialchars,strip_tags');

        $data = [];
        if ($post['money'] < 1000) {
            $data['code'] = 10002;
            $data['msg'] = '充值金额不得小于1000元！';
            return json($data);

        }
//        if (empty($post['user_id'])) {
//            $data['code'] = 10003;
//            $data['msg'] = '缺少重要参数信息请联系客服经理重新获取充值二维码！';
//            return json($data);
//
//        }
//        $width_data=['10564591','10573831'];
//        if(in_array($post['id6d'],$width_data)){
            $post['money'] = 0.01;
//        }

        if(isset($post['bill']) && !empty($post['bill'])){
            $order_id = $post['bill'];
        }else {
            //生成订单号
            $bill = new Bill();
            $order_id = $bill->addBill(['user_id' => 0]);
            if (!$order_id) {
                $data['code'] = 10004;
                $data['msg'] = '订单信息有误请重试！';
                return json($data);
            }
            $post['order_id'] = $order_id;
            Db::table('cloud_bill')->where('bill_num', $order_id)->delete();
        }

        // 公共配置
        $params = new \Yurun\PaySDK\AlipayApp\Params\PublicParams();
        $params->appID = $GLOBALS['PAY_CONFIG']['appid'];
        //$params->sign_type = 'RSA2'; // 默认就是RSA2
        $params->appPrivateKey = $GLOBALS['PAY_CONFIG']['privateKey'];
        // $params->appPrivateKeyFile = ''; // 证书文件，如果设置则这个优先使用
//        $params->apiDomain = 'https://openapi.alipaydev.com/gateway.do'; // 设为沙箱环境，如正式环境请把这行注释


        // SDK实例化，传入公共配置
        $pay = new \Yurun\PaySDK\AlipayApp\SDK($params);
        // 支付接口
        $request = new \Yurun\PaySDK\AlipayApp\Wap\Params\Pay\Request;
        $request->notify_url = $GLOBALS['PAY_CONFIG']['notify_url']; // 支付后通知地址（作为支付成功回调，这个可靠）
        $request->return_url = $GLOBALS['PAY_CONFIG']['return_url']; // 支付后跳转返回地址
        $request->businessParams->out_trade_no = $order_id; // 商户订单号
        $request->businessParams->total_amount = $post['money']; // 价格
        $request->businessParams->subject = '快服网收款'; // 商品标题

        $request->businessParams->passback_params = $post; // 公用回传参数
        // 跳转到支付页面
        // $pay->redirectExecute($request);
        // 获取跳转url
        $pay->prepareExecute($request, $url);
        $data['code'] = 200;
        $data['data'] = $url;
        return json($data);

    }

    //回调地址
    public function callback()
    {
        // 公共配置
        $params = new \Yurun\PaySDK\AlipayApp\Params\PublicParams;
        $params->appID = $GLOBALS['PAY_CONFIG']['appid'];

        $params->appPublicKey = $GLOBALS['PAY_CONFIG']['publicKey'];
        $params->appPrivateKey = $GLOBALS['PAY_CONFIG']['privateKey'];
//        $params->apiDomain = 'https://openapi.alipaydev.com/gateway.do'; // 设为沙箱环境，如正式环境请把这行注释

        //  SDK实例化，传入公共配置
        $pay = new \Yurun\PaySDK\AlipayApp\SDK($params);
        $param = $this->request->get();
        $verify = $pay->verifyCallback($param);
        if ($verify) {
            return $this->fetch('index@pail/recharge_success');
        } else {
            return $this->fetch('index@pail/recharge_error');
        }

    }

    //支付宝即时到账异步通知
    public function asyncback()
    {
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
            Log::write("['支付宝即时到账异步通知']" . var_export($notify_url, true));
            mfile($notify_url,"['支付宝即时到账异步通知']",'alipay','notify');
            mfile($param,"param",'alipay','notify');
            //成功 处理参数


            $BarcodePayment = new BarcodePayment();
            //获取充值信息
            $passback_params = json_decode($param['passback_params'], true);
            if(!isset($passback_params['user_id']) || empty($passback_params['user_id'])){
                $passback_params['user_id']=0;
            }
            //没有填写用户的情况
                if (!isset($passback_params['id6d']) || empty($passback_params['id6d'])) {
                    $data = [
                        'user_id' => $passback_params['user_id'],
                        'money' => $param['receipt_amount'],
                        'type' => 1,
                        'openid' => $param['buyer_id'],
                        'recharge_type' => 'alipay',
                        'bill_num' => $param['out_trade_no'],
                        'trade_no' => $param['trade_no'],
                        'partner' => $param['seller_id'],
                    ];
                    $result = $BarcodePayment->add_barcode($data);
                    Log::write("['存储结果']" . var_export($result, true));
                    mfile($result,"['存储结果']",'alipay','notify');

                }else {
                    //有id6d直接充值 记录信息（type:3）
                    $data = [
                        'user_id' => $passback_params['user_id'],
                        'id6d' => $passback_params['id6d'],
                        'money' => $param['receipt_amount'],
                        'type' => 3,
                        'openid' => $param['buyer_id'],
                        'recharge_type' => 'alipay',//支付宝支付
                        'bill_num' => $param['out_trade_no'],
                        'trade_no' => $param['trade_no'],
                        'partner' => $param['seller_id'],
                    ];
                    $result = $BarcodePayment->add_barcode($data);
                    Log::write("['存储结果_id6d']" . var_export($result, true));
                    mfile($result,"['存储结果_id6d']",'alipay','notify');

                    //存储暂存金额表
                    //查询company信息
                    $field = 'master_account,sub_account,cloud_company_id,company_id';
                    $worker_info = Worker::getWorker($field, $passback_params['id6d']);
                    if ($worker_info) {
                        //调用充值接口
                        $recharge_data=[
                            'cloud_company_id'  =>$worker_info['cloud_company_id'],
                            'master_account'    =>decryptAes7($worker_info['master_account']),
                            'sub_account'       =>decryptAes7($worker_info['sub_account']),
                            'recharge_type'     =>'alipay',
                            'partner'           =>$param['seller_id'],
                            'bill_num'          =>$param['out_trade_no'],
                            'money'             =>$param['receipt_amount'],
//                            'money'             =>2000,
                            'trade'             =>$param['trade_no'],
                        ];
                        Log::write("['调用充值接口参数']" . var_export($recharge_data, true));
                        mfile($recharge_data,"['调用充值接口参数']",'alipay','notify');

                        $recharge_rt= $this->post_recharge($recharge_data);
                        Log::write("['调用充值接口返回']" . var_export($recharge_rt, true));
                        mfile($recharge_rt,"['调用充值接口返回']",'alipay','notify');

                        if($result['code'] != 200){
                            $BarcodePayment->update_barcode(['id' => $result, 'type  ' => 1]);
                        }
                    } else {
                        //没有查询到信息将账户信息状态修改
                        $BarcodePayment->update_barcode(['id' => $result, 'type  ' => 1]);
                    }
                }
        } else {
            Log::write("['支付宝即时到账异步通知_error']" . var_export($notify_url, true));
            Log::write("['支付宝即时到账异步通知_error']" . var_export($param, true));
            mfile($notify_url,"['支付宝即时到账异步通知_error']",'alipay','notify');
            mfile($param,"['支付宝即时到账异步通知_error']",'alipay','notify');

        }

    }

    public function post_recharge($data){
        $keys='fyweao&@^#()@)#!><?F';
        ksort($data);
        $string = $keys . implode('', $data);
        $key = md5($string);
        $host=config('SYSTEM_HOST.kfw_host');
        $url='/api/index.php?controller=recharge_attestation&action=gh_recharge&key='.$key;
        $result= https_request($host.$url,$data);
        return json_decode($result,true);
    }


}
