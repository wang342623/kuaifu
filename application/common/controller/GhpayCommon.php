<?php
namespace app\common\controller;


use think\Controller;
use think\Request;

/**
 * Class GhpayCommon
 * @package app\common\controller
 * 工行支付
 */

class GhpayCommon extends Controller
{

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        date_default_timezone_set('Asia/Shanghai');
        $this->autoload();
    }
    public function autoload(){
        //引入类
        import('api_client_php.ApiClient', EXTEND_PATH);

        $GLOBALS['PAY_CONFIG'] = array(
            'appid'=>'12081000000000000379',
            'wxappid'=> 'wx1676a80d208a4d8e',
            'pay_notify_url'	 => 'https://pay.kuaifu.com.cn/ghpay/Ghnotify/notify',
//            'pay_notify_url'	 => 'https://pay.kuaifu.com.cn/ghpay/Ghnotify/notify',
            'prikey'=>'MIIEowIBAAKCAQEAmk9S0vM8qVSumbhhl9QVWWlpIfb2ItP2gBe4+v9pNU1NXUjlIR/+7JVOGcRWv7ehcImMECVBGE8uW19CXUMb4DTglViuOHD4bl7dJpP+9PkDuouXDqMsclDuMwUFENFJoeXhXTAr3goURquXzVMWEZZP/hzo/wyRcrfH62mWkCWUatdtr6yPsBd+pGt8r9EJ5Y77xeJcOB+hV0zww4RLZ9raQnlT6inB/YoScMkxRt6YVwM708cHKdF8djuXjAVejZgyKr7N22usaDIdvdtSn29LBOIquPg+9Vvf2FNXUfmAhoaG3HvEF7jyZyv7Nm7Ynu9IrrSxWtDYnJMiUxRsjQIDAQABAoIBAGdO3ABm4CHtk8dObegcEP7/V2dp03eVuN4hA7Lm9CS/UCA4AU6gASea/eK6U+meovKY47CbgG0p9bsul1ug5jTAserKqDkZSPl7gUumoXaYkCp/8e3WyJbwH8kAf1e3BjjhknO1IGlTDigSEVthWNEFdSCHcmXuoCHRcILm+eoedI1k+a/lnM637aWSEyciE/ny/yxS6wO9Zpg21ELNXT6wKLF4/X5CippJIW0ZrQAxwO7X3Hx5P6+g/lYV0ba1z05LGhA5e9KIqgYGiLLFxnkuSFc+//WaWKFme2EQLMvFCPYMOuXB1+EyXUom5Q2nx9fvZNu9JhnhA03eJBQTnnECgYEAyPlLMgj3TVEMGsu8Azrf0qMcfj20i0NvsivDnmoSBTjN/x7xiF5sSlaU5oLBgXFt4Uc9BHzrhIkhgeAijnDjutEZQpBftTrnbAhV4VIH2dXEq/yRCsg1PqH3w7ZEeq/8k/JIdeVuT62p2OUyOUEWtLsMySWCd/wTXh9xj5KtzLcCgYEAxI8/sCbjHYSRiZ9qFblu/gKTZB7LHx/QelP/P5Ni6so4PvKyTPBaKDP+alLtdyguuwaTA/IqQODFXmkTsTXJaIajzwbCExKFt/H6uTpVgbWLVriMvGX0R3aFSu3sJYrlQS+9a0AHGNzrAPnXUI2reSW6JCu2ure8KiQQIMbYFNsCgYBwbscantl7b7QN6ZytUeqVtJrkJTE0F+4NB17Q6RZbxYO5Dl6ho/GBRR7YNp57BDIsreX89MXtx6nvNq0ecxl0EjbHfm2Mvf9p+N/SxkmOHIGCljGujoL7HzG1U+rLmFj9i7Xt/wTPnqA+rzqBvWAui9aDUXDEH0nWhlDJuY1x0QKBgFvMy2Hpx4ixsYck/NbIlB3t9gh8mUEgCq4XLM70du5RI5PCpaNpXbIJFnlx4ZYVHj4bA+6D21gRohxF1vycskQvHbJC5cEilXEjgoWfyO+bakhGSPR0aXI22Gn2VKP0Cr43HbBJZwclplv+U6E7iSo7hIQAJodt6GOGFEI6nBChAoGBAJ8oMmGfzm6uJB0Ycyu9vBLFQyF+/0q2H156Qii5XZUwc9u7ULoaf9pHvlazN5CuDsVu89rlwIMgIYjhxxysTNGZIDH2n7nuafxJVpn5/bX9Mmu8N4LBo8oqAJP0nmOWQcUDG4lHQq98Vp01C//YFIze2budyjzyYw0X2UU0h6sl',
            'subinstid'=>'002',
        );

    }


}