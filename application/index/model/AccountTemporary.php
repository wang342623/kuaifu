<?php
namespace app\index\model;

use think\Model;

class AccountTemporary extends Model{

    protected $autoWriteTimestamp ='datetime';
    protected $updateTime = 'add_time';
    //暂存金额表
    public function addTemporary($data){
        try{
            if(empty($data)){
                return false;
            }
         return $this->isUpdate(false)->allowField(true)->save($data);
        }catch (\Exception $e){
            $this->error = $e->getMessage();
            return false;
        }

    }

}