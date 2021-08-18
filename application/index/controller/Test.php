<?php

namespace app\index\controller;
use App\Http\Controllers\CURL;
use think\Controller;
header("Content-type: text/html; charset=utf-8");
class Test extends Controller{


    public function index(){
        $param=$this->request->post();
        $data=[
            'company_id'=>$param['company_id'],
            'category_id'=>$param['category_id'],
            'max_money'=>$param['max_money'],
            'appid'=>20210715975154,
            'ef_time'=>$param['ef_time'],
            'cmd'=>'set_deduction',
            'coupons_name'=>$param['coupons_name']
        ];
        ksort($data);
        $strs= implode('',$data);

        $iv='9842785737770000';
        $key='bWG31vGm';

        $str=encryptAes7($strs,$key,$iv);
        $data['sign']=$str;
        $re= https_request('kfadmin.71baomu.com/api',$data);
        echo "<pre>";
        print_r($re);
        echo "</pre>";
    }


    public function setAppid(){

        $param=$this->request->post();
        $data=[
            'name'=>'云客服',
            'appid'=>20210715975154,
            'cmd'=>'set_app_config',
        ];
        ksort($data);
        $strs= implode('',$data);

        $iv='9842785737770000';
        $key='bWG31vGm';

        $str=encryptAes7($strs,$key,$iv);
        $data['sign']=$str;
        $re= https_request('kfadmin.71baomu.com/api',$data);
        echo "<pre>";
        print_r($re);
        echo "</pre>";
    }

    public function aaac(){
        $a=[
           ['id'=>'1','name'=>'wang'],
            ['id'=>'2','name'=>'wang2'],
        ];
        echo json_encode($a);
    }

    public function service(){
//        $url = 'http://api.saas.71baomu.com/service';
        $url = 'http://saas7master.71baomu.com/service';

        $row = array(
            '53kf_token' => 'Aj|uU620cjJ`53kf',
            "cmd" => "new_applications",
            "company_id" => '72301740',
            "id6d" => '10210639',
            "protocol" => "2.0",
            "versions" => "2.0",
            "facilitator" => "3.0",
        );
        $json = https_request($url,$row);
        echo "<pre>";
        print_r(json_decode($json,true));
        echo "</pre>";
    }

    public function response(){
      $redis=  CI_redis();
      $redis -> set('wang_1','aaa1');
      $redis->EXPIRE('wang_1','10');
    }
}