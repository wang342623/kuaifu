<?php
namespace app\index\controller;


use app\index\model\Company;
use app\index\model\PhoneAuthentication;

use think\Config;
use think\Controller;

/**
 * Class WxApp
 * @package app\index\controller
 * 微信认证小程序
 */
class Wxapp extends Controller{

    //小程序 api
    public function index(){

    }
    //根据code获取openid
    public function get_openid(){
           $code= $this->request->get('code','','trim,htmlspecialchars,strip_tags');
            //获取 wxapp配置
            $wxapp_config=config('WxApp_Config');
            if(empty($code) || !isset($code)){
                echo json_encode(['code' =>'40004','msg'=>'param is undefined']);
                exit;
            }
            $url = "https://api.weixin.qq.com/sns/jscode2session?appid={$wxapp_config['AppId']}&secret={$wxapp_config['AppSecret']}&js_code={$code}&grant_type=authorization_code";
            $res = https_request($url);
            mfile($res,'open_id','wangha','time_outs');
            echo  $res;
            exit;
    }
    //电话号解密
    public function decode_mobile(){
        $sessionkey=$this->request->get('sessionkey','','trim,htmlspecialchars,strip_tags');
        $iv=$this->request->get('iv','','trim,htmlspecialchars,strip_tags');
        $encryptedData=$this->request->get('encryptedData','','trim,htmlspecialchars,strip_tags');
        $company_id=$this->request->get('company_id','','trim,htmlspecialchars,strip_tags');
        if(empty($company_id)){
            echo json_encode(['code' =>'40004','msg'=>'company_id is undefined']);
            exit;
        }
        $wxapp_config=config('WxApp_Config');
        $wxbiz=new WxBiz($wxapp_config['AppId'],$sessionkey);
        $wx_data= $wxbiz->decryptData($encryptedData,$iv,$data);
        mfile($this->request->get(),'data','wangha','timeout');
        mfile($wx_data,'error_code','wangha','timeout');
        if($wx_data !== 0){
            echo json_encode(['code' =>'40003','msg'=>'sessionkey timeout']);
            exit;
        }

        //获取到手机号 下一步流程 ？？？？
        $mobile=json_decode($data,true);
        $auth=PhoneAuthentication::getPhoneAuthentication($company_id,$mobile['phoneNumber'],2);
        if($auth){
            echo json_encode(['code' =>'40004','msg'=>'The company has been certified']);
            exit;
        }
        $rts=PhoneAuthentication::getPhoneAuthentication($company_id,$mobile['phoneNumber'],1);
        if(!$rts){
            if($mobile['phoneNumber']){
                $mobile_data=[
                    'mobile'=>$mobile['phoneNumber'],
                    'company_id'=>$company_id,
                    'type'=>1
                ];
                $phone_authentication=new PhoneAuthentication();
                $rt=$phone_authentication->addPhoneAuthentication($mobile_data);
            }
        }
        mfile($mobile,'$mobile','wangha','timeout');
        echo json_encode(['code' =>'200','msg'=>$mobile['phoneNumber']]);
        exit;
    }
    //快服账号信息获取
    public function getCompany(){
        $company_id=$this->request->get('company_id','','trim,htmlspecialchars,strip_tags');

        if(empty($company_id)){
            echo json_encode(array('code'=>'40004','msg'=>'company_id is undefined'));
            exit;
        }
        /*$company_info=Company::getCompany('master_account,company_name',$company_id);
        if(!$company_info){
            echo json_encode(array('code'=>'50002','msg'=>'company is null'));
            exit;
        }*/
        $redis=CI_redis();
        $saas_com_info=$redis->hgetall('saas.company.info.{'.$company_id.'}');

        if(empty($saas_com_info)){
            echo json_encode(array('code'=>'50002','msg'=>'company is null'));
            exit;
        }
        $data=[
            'code'=>'200',
            'data'=>[
                'account'=>$saas_com_info['account']?:'',
                'company_name' =>$saas_com_info['company_name']?:'',
            ],
        ];

        echo json_encode($data);
        exit;
    }

    //验证当前账号是否认证
    public function check_account(){
        $time=time();
        mfile($time,'timelog1','timelog','log');
       $company_id=$this->request->get('company_id','','trim,htmlspecialchars,strip_tags');
        if(empty($company_id)){
            $data=['status'=>404,'msg'=>'company is null'];
            echo  json_encode($data);
            exit;
        }
       $redis= CI_redis();
       $com_info=$redis->hgetall("com.info.{".$company_id."}");


    //   if($com_info['expiration_date'] == '-1'){
         if(isset($com_info['ringUpVerification'])){
             if($com_info['ringUpVerification'] == '-1'){
                 $data=['code'=>201,'msg'=>'success'];
                 echo  json_encode($data);
                 exit;
             }
         }

        $phone_auth_info=PhoneAuthentication::getPhoneAuthentication($company_id,'',2);
        if($phone_auth_info){ //充过钱直接过
            //如果这个认证过那么调接口提升排序
            ob_end_clean();
            ob_start();
            $data=['code'=>200,'msg'=>'success'];
            echo  json_encode($data);
            $size = ob_get_length();
            header("HTTP/1.1 200 OK");
            header("Content-Length: $size");
            header("Connection: close");
            header("Content-Type: application/json;charset=utf-8");
            ob_end_flush();
            if(ob_get_length()){
                ob_flush();
            }
            flush();
            if (function_exists("fastcgi_finish_request")) {
                fastcgi_finish_request();
            }
            usleep(300000);
            ignore_user_abort(true);
            set_time_limit(300);

            if(isset($com_info['ringUpVerification']) && $com_info['ringUpVerification'] != 'n'){
                $res=$this->upPhone($company_id,$phone_auth_info['mobile']);
                mfile($res,'phone_up123','wangha','up_phone');
            }
        }else{
            $data=['code'=>402,'msg'=>'error'];
            echo  json_encode($data);
            exit;
        }

    }

    //修改一分钱充值状态
    public function savePhoneAuth(){
       $company_id= $this->request->get('company_id','','trim,htmlspecialchars,strip_tags');
       $auth_phone = $this->request->get('auth_phone','','trim,htmlspecialchars,strip_tags');
        if(empty($company_id) || empty($auth_phone)){
            $data=['code'=>40004,'msg'=>'param empty'];
            echo  json_encode($data);
            exit;
        }

        $phone_authentication= new PhoneAuthentication();
        $phone_tr=$phone_authentication->updatePhoneAuthentication($company_id,$auth_phone,['type'=>2]);
        if(!$phone_tr){
            $data=['code'=>200,'msg'=>'save null'];
            echo  json_encode($data);
            exit;
        }else{
            $data=['code'=>200,'msg'=>'success'];
            echo  json_encode($data);
            exit;
        }


    }


    public function upPhone($company_id,$auth_phone){

        $url=config('SYSTEM_HOST.call_api');
        $time=time();
        $data=[
            'phone'=>$auth_phone, //号码
            'third_id'=>$company_id, //号码对应的公司id
            'time'=>$time, //时间戳 秒
            'check'=> md5($auth_phone.$company_id.$time)//md5(phone+third_id+time)
        ];
        mfile($data,'phone_up','wangha','up_phone');
        $re = https_request($url,$data);
        mfile($re,'$re','wangha','up_phone');
        return $re;
    }


    public function getAuthPhone(){
        $auth_phone = config('auth_phone');

        echo json_encode(['code'=>'200','date'=>$auth_phone]);
        exit;
    }


    public function getcominfo(){


        new Client();
        $redis=CI_redis();
        $re=$redis->hgetall("com.info.{72034819}");
        echo "<pre>";
        print_r($re);
        echo "</pre>";
    }

}