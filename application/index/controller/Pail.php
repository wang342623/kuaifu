<?php
namespace app\index\controller;

use think\Controller;
use think\Log;

class Pail extends Controller{


    //微信/支付宝支付页面
    public function index(){
        $id6d = $this->request->param('id6d','','trim,htmlspecialchars,strip_tags');
        $user_id = $this->request->param('user_id','','trim,htmlspecialchars,strip_tags');
        $type = $this->request->param('type','1','trim,htmlspecialchars,strip_tags');
        $token = $this->request->param('token','','trim,htmlspecialchars,strip_tags');
        $time = $this->request->param('time','','trim,htmlspecialchars,strip_tags');
        $result=decryption($token,$user_id,$id6d,$time);

        if(!$result){
           echo '<script>alert("暂无权限访问");</script>';
           exit;
        }
        if($time." 23:59:59" < date('Y-m-d')){
            echo '<script>alert("二维码以失效");</script>';
            exit;
        }
        $this->assign('id6d',$id6d);
        $this->assign('user_id',$user_id);
        $this->assign('type',$type);

        return $this->fetch();
    }

    //成功显示页面
    public function recharge_success(){

        return $this->fetch();
    }
    //失败显示页面
    public function recharge_error(){

        return $this->fetch();
    }

}