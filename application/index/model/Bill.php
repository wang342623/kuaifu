<?php
namespace app\index\model;

use think\Model;

class Bill extends Model
{
    public function addBill($data){
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