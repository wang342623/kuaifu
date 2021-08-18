<?php
namespace app\index\controller;


use app\common\controller\GhpayCommon;

class Ghpay extends GhpayCommon{

    public function index(){

        //私钥注意，需要带-----BEGIN RSA PRIVATE KEY-----和-----END RSA PRIVATE KEY-----，且为PCKS1格式。勿使用PCKS8格式。
        $prikey = "-----BEGIN RSA PRIVATE KEY-----\n". wordwrap($GLOBALS['PAY_CONFIG']['prikey'], 64, "\n", true) ."\n-----END RSA PRIVATE KEY-----";

//        $url = "http://114.255.225.27/api";
        $apiClient=new \ApiClient($prikey);
        $apiRequest = new \ApiRequest("http://114.255.225.27/api", '', $GLOBALS['PAY_CONFIG']['appid']);
//        $apiRequest->setRequestField("instId",$GLOBALS['PAY_CONFIG']['appid']);
//        $apiRequest->setRequestField("subInstId","002");
//        $apiRequest->setRequestField("oriOrderNo",'123');
//        $apiRequest->setRequestField("oriTxDate",'');
            $apiRequest->setRequestField("version",'1.0.0.0');
            $apiRequest->setRequestField("charset",'');
            $apiRequest->setRequestField("merid",'test');
            $apiRequest->setRequestField("trancode",'');
            $apiRequest->setRequestField("reqdata",'');


        $apiResponse = $apiClient->execute($apiRequest);
        $rt= json_decode($apiResponse->getSignBlk(),true);
        $check = $apiResponse->isCheckValid();
        echo "<pre>";
        print_r(['rt'=>$rt,'check'=>$check]);
        echo "</pre>";

    }


}