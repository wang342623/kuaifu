<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
/**
 * [decryptAes7 PHP7 AES解密]
 * @param  [type] $data           [description]
 * @param  string $privateKey_msg [description]
 * @param  string $iv_msg         [description]
 * @return [type]                 [description]
 */
function decryptAes7($data, $privateKey_msg = '5k3f4good', $iv_msg = '0102030405060708')
{
    if ($data == '') {
        return $data;
    }
    $encrypted = fromHexString($data);
    $decrypted = openssl_decrypt($encrypted, 'AES-128-CBC', $privateKey_msg, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv_msg);
    //去除填充
    $decrypted = fromPaddingString($decrypted);
    //去除补0
    $decrypted = rtrim($decrypted, "\0");
    //迁移老数据时 要解密两次 没加密的数据会变空
    if ($decrypted == "") {
        return $data;
    }
    return $decrypted;
}
/**
 * 处理PCKS7填充
 */
function fromPaddingString($cipher_text)
{
    $pad = ord($cipher_text[strlen($cipher_text) - 1]);
    if ($pad > 0 && $pad <= 16) {
        $cipher_text = substr($cipher_text, 0, -$pad);
    }
    return $cipher_text;
}
/**
 * [fromHexString 把十六进制数转换成字符串]
 * @param  [type] $sa [description]
 * @return [type]     [description]
 */
function fromHexString($sa)
{
    $buf = "";
    for ($i = 0; $i < strlen($sa); $i += 2) {
        $val = chr(hexdec(substr($sa, $i, 2)));
        $buf .= $val;
    }
    return $buf;
}

//对称解密
function decryption($str,$user_id,$id6d,$time,$money=''){
    $token = 'Ajlouadsgdvdgacs';
    $encode= md5(substr(md5(md5($money.$user_id.$id6d.$time)),4,10).$token);

    if($encode == $str){
        return true;
    }else{
        return false;
    }
}

function floatgtre($big, $small, $precision = 10)
{// is on float bigger or equal to another
    $e = pow(10, $precision);
    $ibig = intval($big * $e);
    $ismall = intval($small * $e);
    return ($ibig >= $ismall);
}

// 防止sql注入,防传入html代码
/*
* 参数$str:要过滤的字符串
* 参数$type:类型number数字，date日期，string 字符串
* 参数$filterhtml 过滤html
*/
function filterSQL($str, $type = 'string', $filterhtml = 0)
{
    $reg_date = '/^[\d]{4}-\d{1,2}-\d{1,2}$/';//匹配形如1990-04-09或1990-4-9的格式
    $patterns[0] = "/script/";
    $replacements[0] = "";
    ksort($patterns);
    ksort($replacements);
    $str = preg_replace($patterns, $replacements, $str);
    $str = addslashes($str);
    switch ($type) {
        case "string":
            break;
        case "number":
            $str += 0;
            break;
        case "date":
            if (preg_match($reg_date, $str)) {

            } else {
                return date("Y-m-d");
            }
            break;
        default:
            break;
    }

    if ($filterhtml == 0) {
        $str = sanitize_html_string($str);
    }
    return $str;
}

function https_request($url, $data=null){
    // 初始化一个 cURL 对象
    $curl = curl_init();
    // 设置你需要抓取的URL
    curl_setopt($curl, CURLOPT_URL,$url);
    //必须加这个，不加不好使（不多加解释，东西太多了）
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//对认证证书进行检验
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    if (!empty($data)){//post方式，否则是get方式
        //设置模拟post方式
        curl_setopt($curl,CURLOPT_POST,1);
        //传数据，get方式是直接在地址栏传的，这是post传参的解决方式
        curl_setopt($curl,CURLOPT_POSTFIELDS,$data);//$data可以是数组，json
    }
    // 设置cURL 参数，要求结果保存到字符串中还是输出到屏幕上。1是保存，0是输出
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    // 运行cURL，请求网页
    $output = curl_exec($curl);
    // 关闭URL请求
    curl_close($curl);
    return $output;
}

//文件输出
function mfile($mfile_value, $mfile_name = '', $path = '', $filename = '', $from = false)
{
    $path_out = dirname(__DIR__) . '/tmp';
    if (!empty($path)) {
        $path_out .=  '/' . $path;
    }
    if (!is_dir($path_out)) mkdir($path_out,0777,true);
    if (!empty($filename)) {
        $path_out .=  '/' . $filename;
    } else {
        $path_out .= '/mjs';
    }
    // $path_out .= '_'.date("YmdH").'.log';
    $path_out .= '_' . date("Ymd")  . '.log';

//    $path_out = dir_change($path_out);

    if ($from) $from = "from:" . $_SERVER['QUERY_STRING'] . "\r\n";
    if ($mfile_name) $mfile_name .= ':';
    file_put_contents($path_out, "[" . date("Y-m-d H:i:s") . "] " . $from . $mfile_name . print_r($mfile_value, true) . "\r\n\r\n", FILE_APPEND);
}
function dir_change($file_names)
{
    $filesize = filesize(trim($file_names));
    if ($filesize >= 1048576) {
        $filesize = round($filesize / 1048576 * 100) / 100;
        $file_name = substr($file_names, 0, strrpos($file_names, "."));
        $name = substr($file_name, 0, strrpos($file_name, "_"));
        $num = substr($file_name, strripos($file_name, "_") + 1);
        if ($filesize > 3) {
            $num += 1;
            $file_names = $name . '_' . $num . ".log";
            return dir_change($file_names);
        }
    }
    return $file_names;
}

/**
 * [getIpInfo 获取客户端IP]
 * @return [type] [description]
 */
function getIpInfo()
{
    global $REMOTE_ADDR;
    global $HTTP_X_FORWARDED_FOR, $HTTP_X_FORWARDED, $HTTP_FORWARDED_FOR, $HTTP_FORWARDED;
    global $HTTP_VIA, $HTTP_X_COMING_FROM, $HTTP_COMING_FROM;
    global $HTTP_SERVER_VARS, $HTTP_ENV_VARS;

    if (empty($REMOTE_ADDR)) {
        if (!empty($_SERVER) && isset($_SERVER['REMOTE_ADDR'])) {
            $REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
        } else if (!empty($_ENV) && isset($_ENV['REMOTE_ADDR'])) {
            $REMOTE_ADDR = $_ENV['REMOTE_ADDR'];
        } else if (!empty($HTTP_SERVER_VARS) && isset($HTTP_SERVER_VARS['REMOTE_ADDR'])) {
            $REMOTE_ADDR = $HTTP_SERVER_VARS['REMOTE_ADDR'];
        } else if (!empty($HTTP_ENV_VARS) && isset($HTTP_ENV_VARS['REMOTE_ADDR'])) {
            $REMOTE_ADDR = $HTTP_ENV_VARS['REMOTE_ADDR'];
        } else if (@getenv('REMOTE_ADDR')) {
            $REMOTE_ADDR = getenv('REMOTE_ADDR');
        }
    }

    if (empty($HTTP_X_FORWARDED_FOR)) {
        if (!empty($_SERVER) && isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $HTTP_X_FORWARDED_FOR = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else if (!empty($_ENV) && isset($_ENV['HTTP_X_FORWARDED_FOR'])) {
            $HTTP_X_FORWARDED_FOR = $_ENV['HTTP_X_FORWARDED_FOR'];
        } else if (!empty($HTTP_SERVER_VARS) && isset($HTTP_SERVER_VARS['HTTP_X_FORWARDED_FOR'])) {
            $HTTP_X_FORWARDED_FOR = $HTTP_SERVER_VARS['HTTP_X_FORWARDED_FOR'];
        } else if (!empty($HTTP_ENV_VARS) && isset($HTTP_ENV_VARS['HTTP_X_FORWARDED_FOR'])) {
            $HTTP_X_FORWARDED_FOR = $HTTP_ENV_VARS['HTTP_X_FORWARDED_FOR'];
        } else if (@getenv('HTTP_X_FORWARDED_FOR')) {
            $HTTP_X_FORWARDED_FOR = getenv('HTTP_X_FORWARDED_FOR');
        }
    }

    if (empty($HTTP_X_FORWARDED)) {
        if (!empty($_SERVER) && isset($_SERVER['HTTP_X_FORWARDED'])) {
            $HTTP_X_FORWARDED = $_SERVER['HTTP_X_FORWARDED'];
        } else if (!empty($_ENV) && isset($_ENV['HTTP_X_FORWARDED'])) {
            $HTTP_X_FORWARDED = $_ENV['HTTP_X_FORWARDED'];
        } else if (!empty($HTTP_SERVER_VARS) && isset($HTTP_SERVER_VARS['HTTP_X_FORWARDED'])) {
            $HTTP_X_FORWARDED = $HTTP_SERVER_VARS['HTTP_X_FORWARDED'];
        } else if (!empty($HTTP_ENV_VARS) && isset($HTTP_ENV_VARS['HTTP_X_FORWARDED'])) {
            $HTTP_X_FORWARDED = $HTTP_ENV_VARS['HTTP_X_FORWARDED'];
        } else if (@getenv('HTTP_X_FORWARDED')) {
            $HTTP_X_FORWARDED = getenv('HTTP_X_FORWARDED');
        }
    }

    if (empty($HTTP_FORWARDED_FOR)) {
        if (!empty($_SERVER) && isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $HTTP_FORWARDED_FOR = $_SERVER['HTTP_FORWARDED_FOR'];
        } else if (!empty($_ENV) && isset($_ENV['HTTP_FORWARDED_FOR'])) {
            $HTTP_FORWARDED_FOR = $_ENV['HTTP_FORWARDED_FOR'];
        } else if (!empty($HTTP_SERVER_VARS) && isset($HTTP_SERVER_VARS['HTTP_FORWARDED_FOR'])) {
            $HTTP_FORWARDED_FOR = $HTTP_SERVER_VARS['HTTP_FORWARDED_FOR'];
        } else if (!empty($HTTP_ENV_VARS) && isset($HTTP_ENV_VARS['HTTP_FORWARDED_FOR'])) {
            $HTTP_FORWARDED_FOR = $HTTP_ENV_VARS['HTTP_FORWARDED_FOR'];
        } else if (@getenv('HTTP_FORWARDED_FOR')) {
            $HTTP_FORWARDED_FOR = getenv('HTTP_FORWARDED_FOR');
        }
    }

    if (empty($HTTP_FORWARDED)) {
        if (!empty($_SERVER) && isset($_SERVER['HTTP_FORWARDED'])) {
            $HTTP_FORWARDED = $_SERVER['HTTP_FORWARDED'];
        } else if (!empty($_ENV) && isset($_ENV['HTTP_FORWARDED'])) {
            $HTTP_FORWARDED = $_ENV['HTTP_FORWARDED'];
        } else if (!empty($HTTP_SERVER_VARS) && isset($HTTP_SERVER_VARS['HTTP_FORWARDED'])) {
            $HTTP_FORWARDED = $HTTP_SERVER_VARS['HTTP_FORWARDED'];
        } else if (!empty($HTTP_ENV_VARS) && isset($HTTP_ENV_VARS['HTTP_FORWARDED'])) {
            $HTTP_FORWARDED = $HTTP_ENV_VARS['HTTP_FORWARDED'];
        } else if (@getenv('HTTP_FORWARDED')) {
            $HTTP_FORWARDED = getenv('HTTP_FORWARDED');
        }
    }

    if (empty($HTTP_VIA)) {
        if (!empty($_SERVER) && isset($_SERVER['HTTP_VIA'])) {
            $HTTP_VIA = $_SERVER['HTTP_VIA'];
        } else if (!empty($_ENV) && isset($_ENV['HTTP_VIA'])) {
            $HTTP_VIA = $_ENV['HTTP_VIA'];
        } else if (!empty($HTTP_SERVER_VARS) && isset($HTTP_SERVER_VARS['HTTP_VIA'])) {
            $HTTP_VIA = $HTTP_SERVER_VARS['HTTP_VIA'];
        } else if (!empty($HTTP_ENV_VARS) && isset($HTTP_ENV_VARS['HTTP_VIA'])) {
            $HTTP_VIA = $HTTP_ENV_VARS['HTTP_VIA'];
        } else if (@getenv('HTTP_VIA')) {
            $HTTP_VIA = getenv('HTTP_VIA');
        }
    }

    if (empty($HTTP_X_COMING_FROM)) {
        if (!empty($_SERVER) && isset($_SERVER['HTTP_X_COMING_FROM'])) {
            $HTTP_X_COMING_FROM = $_SERVER['HTTP_X_COMING_FROM'];
        } else if (!empty($_ENV) && isset($_ENV['HTTP_X_COMING_FROM'])) {
            $HTTP_X_COMING_FROM = $_ENV['HTTP_X_COMING_FROM'];
        } else if (!empty($HTTP_SERVER_VARS) && isset($HTTP_SERVER_VARS['HTTP_X_COMING_FROM'])) {
            $HTTP_X_COMING_FROM = $HTTP_SERVER_VARS['HTTP_X_COMING_FROM'];
        } else if (!empty($HTTP_ENV_VARS) && isset($HTTP_ENV_VARS['HTTP_X_COMING_FROM'])) {
            $HTTP_X_COMING_FROM = $HTTP_ENV_VARS['HTTP_X_COMING_FROM'];
        } else if (@getenv('HTTP_X_COMING_FROM')) {
            $HTTP_X_COMING_FROM = getenv('HTTP_X_COMING_FROM');
        }
    }

    if (empty($HTTP_COMING_FROM)) {
        if (!empty($_SERVER) && isset($_SERVER['HTTP_COMING_FROM'])) {
            $HTTP_COMING_FROM = $_SERVER['HTTP_COMING_FROM'];
        } else if (!empty($_ENV) && isset($_ENV['HTTP_COMING_FROM'])) {
            $HTTP_COMING_FROM = $_ENV['HTTP_COMING_FROM'];
        } else if (!empty($HTTP_COMING_FROM) && isset($HTTP_SERVER_VARS['HTTP_COMING_FROM'])) {
            $HTTP_COMING_FROM = $HTTP_SERVER_VARS['HTTP_COMING_FROM'];
        } else if (!empty($HTTP_ENV_VARS) && isset($HTTP_ENV_VARS['HTTP_COMING_FROM'])) {
            $HTTP_COMING_FROM = $HTTP_ENV_VARS['HTTP_COMING_FROM'];
        } else if (@getenv('HTTP_COMING_FROM')) {
            $HTTP_COMING_FROM = getenv('HTTP_COMING_FROM');
        }
    }

    if (!empty($REMOTE_ADDR)) {
        $direct_ip = $REMOTE_ADDR;
    }

    $proxy_ip = '';
    if (!empty($HTTP_X_FORWARDED_FOR)) {
        $proxy_ip = $HTTP_X_FORWARDED_FOR;
    } else if (!empty($HTTP_X_FORWARDED)) {
        $proxy_ip = $HTTP_X_FORWARDED;
    } else if (!empty($HTTP_FORWARDED_FOR)) {
        $proxy_ip = $HTTP_FORWARDED_FOR;
    } else if (!empty($HTTP_FORWARDED)) {
        $proxy_ip = $HTTP_FORWARDED;
    } else if (!empty($HTTP_VIA)) {
        $proxy_ip = $HTTP_VIA;
    } else if (!empty($HTTP_X_COMING_FROM)) {
        $proxy_ip = $HTTP_X_COMING_FROM;
    } else if (!empty($HTTP_COMING_FROM)) {
        $proxy_ip = $HTTP_COMING_FROM;
    }

    if (empty($proxy_ip)) {
        return $direct_ip;
    } else {
        $is_ip = preg_match('/^([0-9]{1,3}.){3,3}[0-9]{1,3}/', $proxy_ip, $regs);
        if ($is_ip && (count($regs) > 0)) {
            return $regs[0];
        } else {
            return false;
        }
    }
}

//公共redis获取
function CI_redis()
{
    static $redis = null;

    import('KF_redis.cloudredis', EXTEND_PATH,'.class.php');
    if (!$redis) {
        $redis = new cloudredis();
    }
    return $redis;
}


function a_ret_suc($data,$code = 200) {
    $_data = array('status'=> 1,'code' => $code);
    if(is_array($data)) {
        $_data = array_merge($_data, $data);
    } else {
        $_data['msg'] = strval($data);
    }

    return json_encode($_data);
}

//返回失败
function a_ret_err($data, $code = 10000) {
    $_data = array('status' => 0, 'code' => $code);
    if (is_array($data)) {
        $_data = array_merge($_data, $data);
    } else {
        $_data['msg'] = strval($data);

    }

    return json_encode($_data);
}

/**
 * [encryptAes PHP7以下AES加密  CBC模式 秘钥长度128 秘钥$cfg['privateKey_msg'] 秘钥偏移量$cfg['iv_msg'] 填充方式pkcs5,不满16位的话补0 输出字符集为十六进制的uft8]
 * @param  [type] $data           [description]
 * @param  string $privateKey_msg [description]
 * @param  string $iv_msg         [description]
 * @return [type]                 [description]
 */
function encryptAes($data, $privateKey_msg = '5k3f4good', $iv_msg = '0102030405060708')
{
    $data = pkcs5Pad($data);
    $encrypt_data = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $privateKey_msg, $data, MCRYPT_MODE_CBC, $iv_msg);
    $encode = toHexString($encrypt_data);
    return $encode;
}
/**
 * 针对长度不满16位的，进行填充操作
 */
function pkcs5Pad($text, $blocksize = 16)
{
    $pad = $blocksize - (strlen($text) % $blocksize);
    return $text . str_repeat(chr($pad), $pad);
}
/**
 * [toHexString 把字符串中的每个字符转换成十六进制数]
 * @param  [type] $sa [description]
 * @return [type]     [description]
 */
function toHexString($sa)
{
    $buf = "";
    for ($i = 0; $i < strlen($sa); $i++) {
        $val = dechex(ord($sa {
        $i}));
        if (strlen($val) < 2)
            $val = "0" . $val;
        $buf .= $val;
    }
    return $buf;
}

/**
 * [encryptAes7 PHP7 AES加密  CBC模式  秘钥长度128 秘钥$cfg['privateKey_msg'] 秘钥偏移量$cfg['iv_msg'] 补码方式pkcs5 解密串编码方式十六进制]
 * @param  [type] $data           [description]
 * @param  string $privateKey_msg [description]
 * @param  string $iv_msg         [description]
 * @return [type]                 [description]
 */
function encryptAes7($data, $privateKey_msg = '5k3f4good', $iv_msg = '0102030405060708')
{
    $encrypt_data = openssl_encrypt($data, 'AES-128-CBC', $privateKey_msg, OPENSSL_RAW_DATA, $iv_msg);
    $encode = toHexString($encrypt_data);
    return $encode;
}