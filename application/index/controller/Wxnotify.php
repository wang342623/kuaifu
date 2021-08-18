<?php
namespace app\index\controller;

use app\common\controller\WxPayCommon;

/**
 * Class Wxnotify
 * @package app\index\controller
 * 微信回调
 */

class Wxnotify extends WxPayCommon
{
  public function index(){
      $params = new \Yurun\PaySDK\Weixin\Params\PublicParams();
      $params->appID = $GLOBALS['PAY_CONFIG']['appid'];
      $params->mch_id = $GLOBALS['PAY_CONFIG']['mch_id'];
      $params->key = $GLOBALS['PAY_CONFIG']['key'];
    // SDK实例化，传入公共配置
      $sdk = new \Yurun\PaySDK\Weixin\SDK($params);

      $payNotify = new WxReplyPay();
      try
      {
          $sdk->notify($payNotify);

      }
      catch (\Exception $e)
      {

          mfile($e->getMessage().':'.var_export($payNotify->data, true),'微信传来的参数111','wxparam','wxparam');
          //file_put_contents(__DIR__ . '/notify_result.txt', $e->getMessage() . ':' . var_export($payNotify->data, true));
      }

  }

}




