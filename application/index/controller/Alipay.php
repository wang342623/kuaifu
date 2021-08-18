<?php
namespace app\index\controller;


use app\common\controller\AliPayCommon;
use app\index\model\Worker;

class Alipay extends AliPayCommon{
    /**
     * 支付宝支付页
     */
    public function index(){
        $id6d = $this->request->param('id6d','','trim,htmlspecialchars,strip_tags');
        $money = $this->request->param('money','','trim,htmlspecialchars,strip_tags');
        $time = $this->request->param('time','','trim,htmlspecialchars,strip_tags');
        $bill = $this->request->param('bill','','trim,htmlspecialchars,strip_tags');

        if($time." 23:59:59" < date('Y-m-d')){
            echo '<script>alert("二维码已失效");</script>';
            exit;
        }
        //获取用户账号信息
        $field='master_account,sub_account';
        $worker_info=Worker::getWorker($field,$id6d);
        $account=decryptAes7($worker_info['sub_account']);

        if(strlen($account) >20){
          $account=substr($account,0,8).'...'.substr($account,'-8');
        }
        $this->assign('bill',$bill?$bill:0);
        $this->assign('account',$account);
        $this->assign('money',$money);
        $this->assign('id6d',$id6d);

      return  $this->fetch();
    }


}