<?php
namespace app\common\controller;

use think\Controller;
use think\Request;

class AliAppCommon extends Controller{

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        date_default_timezone_set('Asia/Shanghai');
        $this->autoload();
    }

    public function autoload(){
        //引入类
        import('vendor.autoload', EXTEND_PATH);
        $GLOBALS['PAY_CONFIG'] = array(
            'appid'			=>	'2021002149689986',
            'notify_url'	=>	'http://scanpay.71baomu.com/index/Aliapp_pay/asyncback',
            'return_url'	=>	'http://scanpay.71baomu.com/index/Aliapp_pay/callback',
//            'notify_url'	=>	'https://pay.kuaifu.com.cn/index/AliappPay/asyncback',
//            'return_url'	=>	'https://pay.kuaifu.com.cn/index/AliappPay/callback',
            'aesKey'		=>	'jTK1oJWBOZSvZOKD/7z4dg==',
            'publicKey'	    =>	'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAtUlJb7RmspKICxul7RNUPDv3K/HdNPWHwCUQQ1W5vTujKE2T3fkinBb6xz9u/FGt23IePOJpbTw53ckI/raMUx3NqMc8OMXRVq8AbD+Ebe1YOwNF9iOA2jUaznWir84t2uim7I4g1u/+GX1v+KGyl6Pzpv+DEl8KpxwYoFT6nVFfEZBys1waH0/j8EVpjfEpJGROPj8LsfyIzfJM84seTQlRdwGIPFIUus9n1oXGxRNmPiiecXhjcEynOyuItYn87A4Gk10LR85DgITK8s02ARFTyzn2I/VliwxMRHaqPBk7L1dcpi38Qqvhgjw/xgVcKWnoXjqQ2hOUv9ERw9i3aQIDAQAB',
            'privateKey'	=> 'MIIEpAIBAAKCAQEAqr/qoQzqf086/YSLGeiE3n2ffQ98Og7DxDeqbBTrjamCPe3xO+TpJWu5S7TMbJnkydx9/tMgF34dyucK8Zg5Vhd5sS3A6ylq1dW2lbmr0ZbdPJR89pYibVKHXtNXeJKRUeZoyNF66IwrNF0QPDldwEH79O5YBJCTyojS4Z24+gI8iyft+zR2kaaIe1leM81zqckCFqG7LKy2/VEvEsSGm8B2P0ykHuPiSZwi0wgl+IdrwTlUx9vj5nyJTZNGA0/C6G0b6PLWSIPExZgJ9dYOTjOT0Ygcu3P0cKU0XWHMrLorSpEj81ZmesQPGWwWyr6KVYYfyumf3V+RPYBid8s18wIDAQABAoIBACVNKcenF69aMqvhgbXWOviT/vyGHoBca135Pyy/YTogVsiKq5GHD3vSTbeBNstez0Fd/tRlcPRQ49dIo/ZlZ9kr5bTUJvn4oVM3jdylpCh0Sb6LPcVsxPaW+eW4S7qWYlK/ABMm2C/nNyUIU5ykd5uhjpZSXz4YC4FKZK9PEnLjIIkOvyTcOzXj9LzCRdAH+sCGwZn6a7RossWEpnydQsIBUk80HkNdCrqzFOP+nZKsPws33l3si2yGQSups3B1vAPbkmdPzxhwm0TmQt+X9RSEIfaD03afNSjM/vBl0/6r3ERJsDjoukYFHGFPuB7nLEajWQJTlls762Jq4D4b1IkCgYEA4uw0Lrcbk2S7Kk5DnkY7WcGJHThXlCSQ1dBHGtzFmwZNkiKTiFUslJoKo74aIAvuv0pqFCRvj5ahlWvxFCyqhjeVJkSCp6/gTFiu3Y2jHqhN1D786a6pdBmYogH4JQcX3k8yUlb4gZBta5LlxUnfcXmOPJwfxAJmOYKLcUM0Nd8CgYEAwKEOmb273riQ2AVxZY2Ev4Kx+LPyisGUZKry3pl0GsjRq/mPy6MtZ54eugQBZVTKkPkStNmDqFnEW1LSU/zs2/Z2ChUMRkQab9VloNBbnvF+bJyUI+WaIzdqRZ+UIfqSEOZOvBt7iZESBdg9BF9JN3mBWAEOeSgEgdVqcXucem0CgYA4IJhfnabSdC3fLvCGq3RupPIXKiJvYAP6/sM3n7e4unxhUvAO3pJiRx+ulIy9tHXfFrbaOJDngSiCXuqIROwAuqrHhlaSx2vBNoY6ApiAMrzdbJ795dfAAbzBBZ+s+O4sbIZT23MoCOMnonP2smj0Fk7aKvuobbd/LgzzfN3jHQKBgQC/NmDG7hdyo6VsOiLxWsR+Ul8V8JJ/eF80epkxrAQbnmK7orRBwU4OAGCh5932129o70XJbe2KDOmkeZc9NeFtC77qjTAtA+d2Qc9rDckVAsWrTNakt4MFGDoOuLST8iZSTjuz8Ff2G6JXWOpgz7FfNubVE99pFvc6Zj4OPlT7FQKBgQCnQIyJR+tQIXsMIEVWfAFbeHvD8oLmrrX3j5anf8SKeqmohpS5ArD6MshicweGaVShCUrD0qFYkMGBBCSqpznyfYLGu5l3WOF3OwcmRmCYgrHUfUMbFKmJUToe5QCQbLkpshPMVubs9yiGOHA7xjrAlhJEx9C/PopooMGpg/O7Ew==',
        );
    }



}