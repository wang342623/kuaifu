<?php
namespace app\index\model;


use think\Model;

class BarcodePayment extends Model{

    protected $autoWriteTimestamp ='datetime';
    protected $updateTime = 'add_time';

    //客户经理二维码充值
    public function add_barcode($data){
        try{
            if(empty($data)){
                return false;
            }
             $this->isUpdate(false)->allowField(true)->save($data);
            return $this->getLastInsID();
        }catch (\Exception $e){
            $this->error = $e->getMessage();
            return false;
        }

    }
    //修改
    public function update_barcode($data){
        try{
            if(empty($data)){
                return false;
            }
           return $this->isUpdate(true)->allowField(true)->save($data);
        }catch (\Exception $e){
            $this->error = $e->getMessage();
            return false;
        }
    }


}