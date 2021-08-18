<?php
/**
 * Created by PhpStorm.
 * User: xyr
 * Date: 2021/5/27
 * Time: 11:05
 * Note:痛而不言,笑而不语,不乱于心,不困于情,不畏将来,不念过往
 */

namespace app\common\library\queue;


class Customer
{

    public function __construct($evn,$queue)
    {
        
    }
    public function exec($evn, $queue)
    {
        $msg = $evn->getBody();
        echo $msg . "\n"; //处理消息
        $queue->ack($evn->getDeliveryTag()); //手动发送ACK应答
    }
}