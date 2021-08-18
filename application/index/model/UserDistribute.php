<?php
namespace app\index\model;


use think\Model;

class UserDistribute extends Model{

    public static function get_user_distribute($com_id){
        return self::alias('u')->field('i.user_name,i.id,i.nick_name,u.distribute_time')
            ->join('cloud_inner_user as i','u.inner_user_id=i.id')
            ->where('u.cloud_company_id',$com_id)
            ->find();
    }

}