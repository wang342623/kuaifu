<?php
namespace app\index\model;

use think\Model;

class Worker extends Model{


    public static function getWorker($field,$id6d){
    $rt = self::field($field)
                ->where('id6d',$id6d)
                ->find();
    return $rt;
    }
}