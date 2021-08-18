<?php
namespace app\index\controller;


use app\index\model\AuthOrder;
use app\index\model\Company;
use app\index\model\PhoneAuthentication;

class WxReplyPay extends \Yurun\PaySDK\Weixin\Notify\Pay
{
    public function __exec()
    {
        // TODO: Implement __exec() method.
    //        file_put_contents(__DIR__ . '/notify_result.txt', date('Y-m-d H:i:s') . ':' . var_export($this->data, true));
        mfile($this->data,'微信传来的参数','wxparam','wxparam');
        $result= $this->wxapppay($this->data);
        // 告诉微信我处理过了，不要再通过了
        if($result){
            $this->reply(true, 'OK');
        }

    }
    //认证回调处理
    public function wxapppay($data){

        if(!isset($data['result_code']) && $data['result_code'] != 'SUCCESS'){
            return false;
        }
        //回调处理
        $attach_arr=explode('|',$data['attach']);
        mfile($attach_arr,'自定义回调参数','wxparam','wxparam');
        $company_id = $attach_arr[0]?$attach_arr[0]:'';
        $auth_phone = $attach_arr[1]?$attach_arr[1]:'';

        if(!empty($company_id)){

                $order_num = $data['out_trade_no'];

                $info= AuthOrder::getAuthOrder('open_id',$order_num);
                if($info){
                    mfile($info,$order_num . "订单存在", "wxparam", "wxparam");
                    return true;
                }

                $auth_order_data=[
                    "id"=>substr($order_num,1),
                    "order_num"=>$order_num,
                    "company_id"=>$company_id,
                    "open_id"=>$data['openid'],
                    "transaction_id"=>$data["transaction_id"],
                    "pay_type"=>"wxpay",
                    "total_fee"=>$data['total_fee'] / 100,
                    "pay_time"=>date("Y-m-d H:i:s"),
                    "status"=>0,
                ];

                $auth_order=  new AuthOrder();
                $auth=$auth_order->addAuthOrder($auth_order_data);
                if($auth){
                    $is_attes= AuthOrder::getAuthAccount($data['openid'],$company_id);
                    mfile($is_attes, $company_id . "认证信息", "wxparam", "wxparam");
                    if($is_attes==1){
//                        $host="aqpt.71baomu.com";
                        $host=config('SYSTEM_HOST.aqp_api');
                        $url="/authapplet/domain/applets/setAuthToPayment";
                        $auth_data=[
                            "company_id"=>$company_id,
                            "open_id"=>$data['openid'],
                            'auth_phone'=>$auth_phone,
                            "auth_type"=>2,
                            "sign"=>md5("fyweao&@^#()@)#!><?F".$auth_phone.'2'.$company_id.$data['openid'])
                        ];
                        $re=json_decode(https_request($host.$url,$auth_data),true);
//                        if($re['success'] == 1){
                            //修改认证信息
                           $phone_authentication= new PhoneAuthentication();
                           $phone_tr=$phone_authentication->updatePhoneAuthentication($company_id,$auth_phone,['type'=>2]);
                           if(!$phone_tr){
                               mfile($phone_authentication->getError(),"sqls", "wxparam", "wxparam");
                           }

//                        }
                        mfile($auth_data, $company_id . "添加认证参数", "wxparam", "wxparam");
                        mfile("fyweao&@^#()@)#!><?F".$auth_phone.'2'.$company_id.$data['openid'], $company_id . "添加认证参数", "wxparam", "wxparam");
                        mfile($re, $company_id . "添加认证成功", "wxparam", "wxparam");
                    }
                    mfile($auth, $company_id . "添加订单成功", "wxparam", "wxparam");
                    return true;
                }elsE{
                    mfile($auth_order_data,  "添加订单失败", "wxparam", "wxparam");
                    return false;
                }

        }

    return true;
    }

}


