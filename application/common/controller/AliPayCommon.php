<?php
namespace app\common\controller;

use think\Controller;
use think\Request;

class AliPayCommon extends Controller{

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
            'appid'			=>	'2021002147688412',
            'notify_url'	=>	'http://scanpay.71baomu.com/index/index/asyncback',
            'return_url'	=>	'http://scanpay.71baomu.com/index/index/callback',
//            'notify_url'	=>	'https://pay.kuaifu.com.cn/index/index/asyncback',
//            'return_url'	=>	'https://pay.kuaifu.com.cn/index/index/callback',
//            'aesKey'		=>	'+Mh8imd5tYR4BSRi2jgtBw==',
            'aesKey'		=>	'z0XC5qBA8o15dna3A5MrEw==',
//            'publicKey'	    =>	'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAtUlJb7RmspKICxul7RNUPDv3K/HdNPWHwCUQQ1W5vTujKE2T3fkinBb6xz9u/FGt23IePOJpbTw53ckI/raMUx3NqMc8OMXRVq8AbD+Ebe1YOwNF9iOA2jUaznWir84t2uim7I4g1u/+GX1v+KGyl6Pzpv+DEl8KpxwYoFT6nVFfEZBys1waH0/j8EVpjfEpJGROPj8LsfyIzfJM84seTQlRdwGIPFIUus9n1oXGxRNmPiiecXhjcEynOyuItYn87A4Gk10LR85DgITK8s02ARFTyzn2I/VliwxMRHaqPBk7L1dcpi38Qqvhgjw/xgVcKWnoXjqQ2hOUv9ERw9i3aQIDAQAB',
            'publicKey'	    =>	'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAmm19sN/3ryNY69yO5QeToZeNMv/oiw90IGLoOOfvCtxsIBrgIw22pQKt25Orc7uoT3zEwgifnZGzV2ybbXFGfRsS1fUzi3MqBOXqIqHXA8dpTmcNRJ650qjxkm9y5+G7nBn9+U49LsWpfbMJkUeUli9QRz1pXP2GZi/wAiwQe1hUM3lzKhiR6EFcTliWXHSF7uhMrVOqXa5UuP801BM4Zdr/9DthN26nFZs26+HfXiA65Z+i3876cXdOHtG1N0+T2ngPYrYn6PZgxSPuSnfsVadS814/koXCnnS8EQBEJc7NLuQsze/o1gX4BoYNB3bdIJHUKFtN4ES37UI16xp19QIDAQAB',
            //'privateKey'	=>  'MIIEowIBAAKCAQEAguoyVVlGNkZxHv/4sogwuEoPhrSRMXpJ6TMhACuw4WfMihmvP+0A663cFL5/ZwyBlZ15YtBfHy4/Ca+IT6BIbi0axYNH/b6QSGAk2oRUVEm9TuenS1qSlOzF5YlF7Qv3XpnueFvIH98kC2wbr3FRvZlFZ0X4+u4UQA4obFF2WQuWggMILn1Jr2h6wT48wC6qzEedoC1s/6Pm/C/W2xCXY3ZPGSQQ1mcK46E+aR3dCY1sVz6FAK3F++nzhH1DI2TirapVBmgZMTyiUpM08lSSoKmCgjsabxt9F6IDCVrLFmLpgYKQ7X/fvRXwGOdqwhDZUDB4e4+lnWj84l+aMtV5UQIDAQABAoIBACA2/0HPYR60ANwvAwTzzIFdACsWve/d9tgi08kV90XQZB3Lqjth1+sm8lRMeE9kXXLyuE8flXi3yZ1Zi4KEn640/TSDVLDVNu4IcqkvDQ1cw+/pw1ogpJdvGFeeiw2E909ZM7x8YWeuYlFJPhrTGNk+IT0Dk/wzn9oPYdUdyrCodfwJ5O5MkpnVWwh3pHcSKvSmXRox44BFcwfxWdIWR0lgmGwZAVBvSU6dGS2Flten5MybY5CqKXBXBANEnGg6QHDv4qiu0XzA6+llzctW5Ej75wG3nTh1nKjHOvUql3Flg7PQtU9VZW6II5wti8qISaLHo+6YKZB7KMMmuTufN8ECgYEAxUzQhEBiRwlHMBWR1aHrQ0X4xF4JHv4ZIWruMBZQVb3YmUGajPWDrA78/ffENOhP66dfxAWpda/hB/5UmbDkvFnmX3wZEoRfPIlEf6H7GLITpys8BTn0VXmrzeUpTGxI07bQX/RljzJnKcZa1HeNuhfEyf/EY0YEVL7Zgq4UeQkCgYEAqd01rL00ofAmtPMowsNsFsrYzDN5cRnUgG4Ox24oIUT6HbXck8b45lEaa8z6iLIH+/TfFZ6un8k50nx7Tp5p2hz2lnY8g6h2FDyOpUtzDiZEtYSj40BAZHzVn1kXuLtTa6IVdx6eItNkd1wytX+DpKPqO9JquhGZf1qA7T2ZeAkCgYEAmPt2sxU5HtuS1zXiXkOup/sZaSmA6QcddUv0ZFTo9ZK+4BZ+P84eysrUYBg9isiXL6IYH2ZEf3xggFI0RvdiZKlQd7lBt5nsPoQxGMYAbKWnl3dukp7dVWmCTaPi4d8UmRSnfb+DAAWU+E9VHmmU+zZy4C3xmQgxU4SoIyvXtAECgYA9m+gN4Qon+gxv8QnFqk+X7La5SaowZWf3tPNTThbsCjKVeBKq3q4EC0KD+vKbhcbzkZsgIk+/0KJSP6gRmc9Wts6RQA0uy3kYWeZ6+WSfqy2ckKFImVNBny10AEJkVD0rerxCWi6M/xMfX6dcwQUkP/eC/wyKtDtyP4cnhQIW0QKBgAoyAeKaQcJvJfAFnJSsdc4hXYjVktW5Hz1YFIE5zUOhWhMNAuTnKV7/k/DjJu2cjqJvITd17iYyQCH7xT3NfEyL6QTPu+JWz9/8wDguJ5ySqrHM+tbvCWIc6oLgFvhLJcSkwYszdhKlcYY+niUzSoMWiQiBe5Mamo+z2E05UXUT',
            'privateKey'	=> 'MIIEowIBAAKCAQEAtnR+lTMsgobx8E0Ag8L3aEF639y1jxtlHi0OYtWmjgsjoB9BCvluaziteVc8R8PmPdHxeHr+23MiURscXeCXob8WJH4E58x/+v4uhKndw0FpOX4tDuJb87ogvstinv3wWJDKrKRVva69Sh+7+Cgb4RAnJG8p5IBGIXdTTzf+DfXlW+tZmfwboitpj3syDq18Tl4cxp4xXoWWwqqhpdMvH2fe8n95PsnTU51aayRpKpbT5hXAYZeNOLTpOdhquiKiHyQCCHxJhno2sv22+24SScyfmI01x+L7WK65rmUcAyA1MEd7jEFxNGtpZprGw/k6plZCe1ZBL24RNAwd5zriowIDAQABAoIBAQCCAG9RD61fM5efiL4dJAX0mvNaHoCe/v4FikT91w1dmEJCDQLjNuaeLzwrgSTKXaLn2XccSUbeRiLld1O28Bgaf3Tfe4szPlrSE9pBXGdOgIThGi4wtMif2LcMOw6CCp39/OzsJguRo7fr4MCmCK4UptW8+Bf5YAyYO9V4VIUInEBcSS58qvpjuUMODuSHSSJXKwgn5kD4vw/HTF3u+Cab0c1cU3dmaQA0PbyOw2W/L/XstUFpF7fzIuniWsc6De24Cvci52wlTJ6VJGp4pDtXYKNXW+NYiN7B0vJkqxHL1/r7OCPDziljDpjqmKgWVPhSGSlIJEZ/qE+eb+NY+AwBAoGBANiSddzSNkrpl00DiIzeou14UgpfLEEe8WyEfyGbxuzuEcuZpznNzOK5/p8Wk/n7SDsL8bYk5hvaJb041t1Wv6Wp7RnhYbgVHdKBPlI+5w+VS4j4H4mr5Mah7zl8eI8SYARyMaO3rYDLuYHdtvB7t04P7+26HOf8pDei4xzOHb/BAoGBANer+lGMSwSGTA8sdT2azgZZENsrrNXOsA5Qb6CFFJraJNw6RWPryeWE4u1ClSVx8Ksz/RqxZLqvPhgx7RsRQYYNT49Dk1vSgKQKyQAkWMMihcQsYkxXv5/M+Yu7WHMzn+rQqgBgpnZuypMlJd9pCSKgN0C0V9ZZFUoznQsMmHtjAoGACjD6MuYBgAz1/b3I0zg22o3q7moEHxxhqAWkJeS6u7OISth0MhgpKBIwlTPuyri0cXLicPvTiEP/uqPOGKRYaGdSrQEaJBt6R8mlMSGcpfMUluF2OnhIT8CXDVF6KuFme08UXuvp0kHD/QR981sfCSFe3QUBDzmoUKXtohFg/QECgYB7CAZyQsAAwsPc3BW9oPPqtyBghxe/mHrij4w+W+yXMd5ZK3yuNbIlFpuFkbgXdlo+sBexBIVb4eBgWQrTqBJi8HD0Xy5O8VSlP+nOBO4F00nP0Sn6Nwx/kqTAy4z3mvmdMI/qSzhq02UIb+ac/gRSFmeup7J7+rGkdLEfvn211QKBgG/m8Kdmvf3XeuaKqbYA/0AEk/UhbwaJk/8NoyD/2AdNuv3Y1V7oJ0CVEd8TTUgTB2jw7R73qmW+xy0y8rXjfuuZAXPf/IoN2nVtl9ZFccBY9suIk3VPCBHYLs2JmVf2uRAYMCO318Qq2R9/H6WRti3R3mi56YqR7JMPmoOblzse',
            );
    }

}