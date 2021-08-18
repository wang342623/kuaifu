<?php
namespace app\index\model;

use think\Exception;
use think\Model;

class PhoneAuthentication extends Model{
    protected $autoWriteTimestamp ='datetime';
    protected $createTime = 'add_time';
    protected $updateTime = 'up_time';


    public static function getPhoneAuthentication($company_id,$mobile='',$type){
        $rt=self::field(true)
            ->where('company_id',$company_id);
            if(!empty($mobile)){
                $rt->where('mobile',$mobile);
            }
            $result=$rt->where('type',$type)
                    ->find();
        return $result;
    }

    //添加
    public function addPhoneAuthentication($data=[]){
        $this->updateTime=false;
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
    //修改
    public function updatePhoneAuthentication($company_id,$mobile,$data=[]){
        $this->updateTime='up_time';
        try{
            if(empty($data)){
                return false;
            }
            return $this->isUpdate(true)->allowField(true)->save($data,['company_id'=>$company_id,'mobile'=>$mobile]);
        }catch(\Exception $e){
            $this->error = $e->getMessage();
            return false;
        }
    }



}