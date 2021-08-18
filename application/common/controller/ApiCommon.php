<?php
namespace app\common\controller;

use think\Controller;
use think\Request;

class ApiCommon extends Controller
{
    protected $data;


    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $key=$this->request->get('key','','trim,htmlspecialchars,strip_tags');
        if(empty($key)){
           return a_ret_err('KEY ERROR','50002');
        }

        $data=$this->request->post('',[],'trim,htmlspecialchars,strip_tags');
        if(empty($data['cmd'])){
            return a_ret_err('CMD ERROR','50002');
        }

        try{
            $start=strpos($data['cmd'],'_');
            if(!$start){
                return a_ret_err('CMD ERROR ERROR','50002');
            }

            action('api/');
        }catch (\Exception $e){


        }




    }



}