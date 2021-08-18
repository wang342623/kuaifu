<?php
/**
 * Created by PhpStorm.
 * User: xyr
 * Date: 2021/5/25
 * Time: 16:52
 * Note:痛而不言,笑而不语,不乱于心,不困于情,不畏将来,不念过往
 */

namespace app\common\exception;


class BaseException
{
    // 状态码
    public $status;

    // 错误信息
    public $message = '很抱歉，服务器内部错误';

    // 附加数据
    public $data = [];


    /**
     * BaseException constructor.
     * @param array $params
     */
    public function __construct($params=[])
    {
        if (array_key_exists('status', $params)) {
            $this->message = $params['status'];
        }

        if (array_key_exists('msg', $params)) {
            $this->message = $params['msg'];
        }

        if (array_key_exists('data', $params)) {
            $this->message = $params['data'];
        }
    }
}