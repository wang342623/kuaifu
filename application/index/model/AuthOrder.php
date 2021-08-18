<?php
namespace app\index\model;


use think\Model;

class AuthOrder extends Model{

    public static function getAuthOrder($field,$order_num){
        $rt = self::field($field)
            ->where('order_num',$order_num)
            ->find();
        return $rt;
    }

    public static function getAuthAccount($open_id,$company_id){
        $rt = self::field(true)->where('open_id',$open_id)->where('company_id',$company_id)->count();
        return $rt;
    }

    public function addAuthOrder($data){
        try{
            if(empty($data)){
                return  false;
            }
            $this->isUpdate(false)->allowField(true)->save($data);
            return $this->getLastInsID();
        }catch (\Exception $e){
            $this->error = $e->getMessage();
            return false;
        }

    }

}