<?php
namespace app\index\model;


use think\Model;

class CompanyAccount extends Model{

    public static function getCompanyAccount($data){
         return self::field(true)->where('cloud_company_id',$data['cloud_company_id'])->find();
    }

    public function updateAccount($data,$id){
        try{
            if(empty($data)){
                return false;
            }
           return $this->isUpdate(true)->allowField(true)->save($data,$id);
        }catch(\Exception $e){
            $this->error = $e->getMessage();
            return false;
        }

    }
}