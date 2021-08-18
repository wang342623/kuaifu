<?php
namespace app\index\model;

use think\Model;

class Company extends Model
{
    public static function getCompany($field,$company_id){
        $rt = self::field($field)
            ->where('company_id',$company_id)
            ->find();
        return $rt;
    }

}