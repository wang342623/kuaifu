<?php
namespace app\index\controller;


use aop\AopClient;
use aop\request\AlipaySystemOauthTokenRequest;
use app\index\model\PhoneAuthentication;
use think\Controller;

class Aliapp extends Controller{
    //获取支付宝user_id
    public function getOpenid(){

        $code = $this->request->param('authCode');
        if(empty($code)){
            return json(['code'=>'4004','msg'=>'code empty']);
        }
        $c = new AopClient();
        $c->appId = "2021002149689986";       // APPID
        $c->rsaPrivateKey = "MIIEpAIBAAKCAQEAqr/qoQzqf086/YSLGeiE3n2ffQ98Og7DxDeqbBTrjamCPe3xO+TpJWu5S7TMbJnkydx9/tMgF34dyucK8Zg5Vhd5sS3A6ylq1dW2lbmr0ZbdPJR89pYibVKHXtNXeJKRUeZoyNF66IwrNF0QPDldwEH79O5YBJCTyojS4Z24+gI8iyft+zR2kaaIe1leM81zqckCFqG7LKy2/VEvEsSGm8B2P0ykHuPiSZwi0wgl+IdrwTlUx9vj5nyJTZNGA0/C6G0b6PLWSIPExZgJ9dYOTjOT0Ygcu3P0cKU0XWHMrLorSpEj81ZmesQPGWwWyr6KVYYfyumf3V+RPYBid8s18wIDAQABAoIBACVNKcenF69aMqvhgbXWOviT/vyGHoBca135Pyy/YTogVsiKq5GHD3vSTbeBNstez0Fd/tRlcPRQ49dIo/ZlZ9kr5bTUJvn4oVM3jdylpCh0Sb6LPcVsxPaW+eW4S7qWYlK/ABMm2C/nNyUIU5ykd5uhjpZSXz4YC4FKZK9PEnLjIIkOvyTcOzXj9LzCRdAH+sCGwZn6a7RossWEpnydQsIBUk80HkNdCrqzFOP+nZKsPws33l3si2yGQSups3B1vAPbkmdPzxhwm0TmQt+X9RSEIfaD03afNSjM/vBl0/6r3ERJsDjoukYFHGFPuB7nLEajWQJTlls762Jq4D4b1IkCgYEA4uw0Lrcbk2S7Kk5DnkY7WcGJHThXlCSQ1dBHGtzFmwZNkiKTiFUslJoKo74aIAvuv0pqFCRvj5ahlWvxFCyqhjeVJkSCp6/gTFiu3Y2jHqhN1D786a6pdBmYogH4JQcX3k8yUlb4gZBta5LlxUnfcXmOPJwfxAJmOYKLcUM0Nd8CgYEAwKEOmb273riQ2AVxZY2Ev4Kx+LPyisGUZKry3pl0GsjRq/mPy6MtZ54eugQBZVTKkPkStNmDqFnEW1LSU/zs2/Z2ChUMRkQab9VloNBbnvF+bJyUI+WaIzdqRZ+UIfqSEOZOvBt7iZESBdg9BF9JN3mBWAEOeSgEgdVqcXucem0CgYA4IJhfnabSdC3fLvCGq3RupPIXKiJvYAP6/sM3n7e4unxhUvAO3pJiRx+ulIy9tHXfFrbaOJDngSiCXuqIROwAuqrHhlaSx2vBNoY6ApiAMrzdbJ795dfAAbzBBZ+s+O4sbIZT23MoCOMnonP2smj0Fk7aKvuobbd/LgzzfN3jHQKBgQC/NmDG7hdyo6VsOiLxWsR+Ul8V8JJ/eF80epkxrAQbnmK7orRBwU4OAGCh5932129o70XJbe2KDOmkeZc9NeFtC77qjTAtA+d2Qc9rDckVAsWrTNakt4MFGDoOuLST8iZSTjuz8Ff2G6JXWOpgz7FfNubVE99pFvc6Zj4OPlT7FQKBgQCnQIyJR+tQIXsMIEVWfAFbeHvD8oLmrrX3j5anf8SKeqmohpS5ArD6MshicweGaVShCUrD0qFYkMGBBCSqpznyfYLGu5l3WOF3OwcmRmCYgrHUfUMbFKmJUToe5QCQbLkpshPMVubs9yiGOHA7xjrAlhJEx9C/PopooMGpg/O7Ew==";      // 生成的RSA私钥
        $c->alipayrsaPublicKey = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAtUlJb7RmspKICxul7RNUPDv3K/HdNPWHwCUQQ1W5vTujKE2T3fkinBb6xz9u/FGt23IePOJpbTw53ckI/raMUx3NqMc8OMXRVq8AbD+Ebe1YOwNF9iOA2jUaznWir84t2uim7I4g1u/+GX1v+KGyl6Pzpv+DEl8KpxwYoFT6nVFfEZBys1waH0/j8EVpjfEpJGROPj8LsfyIzfJM84seTQlRdwGIPFIUus9n1oXGxRNmPiiecXhjcEynOyuItYn87A4Gk10LR85DgITK8s02ARFTyzn2I/VliwxMRHaqPBk7L1dcpi38Qqvhgjw/xgVcKWnoXjqQ2hOUv9ERw9i3aQIDAQAB";   // 生成的RSA公钥
        $c->signType= "RSA2";
        $request= new AlipaySystemOauthTokenRequest();
        $request -> setCode($code); //前端传来的code
        $request -> setGrantType("authorization_code");
        $response= $c->execute($request);
        $user_id =$response -> alipay_system_oauth_token_response -> user_id;
        mfile($user_id,'user_id','wangha','user_id');
        return json(['user_id'=>$user_id]);

    }


    //手机号解密存下
    public function decode_mobile(){
        //获取手机号解密
        $time=time();
        mfile($time,'time1','timelog','log');
        $str=$this->request->get('str');
        $company_id=$this->request->get('company_id','','trim,htmlspecialchars,strip_tags');
        if(empty($company_id)){
            echo json_encode(['code' =>'40004','msg'=>'company_id is undefined']);
            exit;
        }
        $content = json_decode($str,true);

        $aesKey='jTK1oJWBOZSvZOKD/7z4dg==';
        $result=openssl_decrypt(base64_decode($content['response']), 'AES-128-CBC', base64_decode($aesKey),OPENSSL_RAW_DATA);

        //获取到手机号 下一步流程 ？？？？
        $mobile=json_decode($result,true);


        if($mobile['code'] != '10000'){
            echo json_encode(['code' =>'40005','msg'=>'decode mobile error']);
            exit;
        }
        $auth=PhoneAuthentication::getPhoneAuthentication($company_id,$mobile['mobile'],2);
        if($auth){
            echo json_encode(['code' =>'40004','msg'=>'The company has been certified']);
            exit;
        }
        $rts=PhoneAuthentication::getPhoneAuthentication($company_id,$mobile['mobile'],1);
        if(!$rts){
            if($mobile['mobile']){
                $mobile_data=[
                    'mobile'=>$mobile['mobile'],
                    'company_id'=>$company_id,
                    'type'=>1
                ];
                $phone_authentication=new PhoneAuthentication();
                $rt=$phone_authentication->addPhoneAuthentication($mobile_data);
            }
        }
        mfile(time()-$time,'time2','timelog','log');

        echo json_encode(['code' =>'200','msg'=>$mobile['mobile']]);
        exit;
    }


}