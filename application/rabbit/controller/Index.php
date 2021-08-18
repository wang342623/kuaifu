<?php
namespace app\rabbit\controller;

use app\common\library\queue\Rabbit;
use think\Controller;

class Index extends Controller{


    public function index(){

        Rabbit::getInstance()->product('abcdefg');

    }
}