<?php
/**
 * Created by PhpStorm.
 * User: xyr
 * Date: 2021/5/28
 * Time: 9:20
 * Note:痛而不言,笑而不语,不乱于心,不困于情,不畏将来,不念过往
 */

namespace app\common\library\queue;


use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class Rabbit
 * @package app\common\library\queue
 */
class Rabbit implements MqInterface
{
    //实例对象
    private static $_instance;
    //交换机名
    private static $exchange;
    //队列名
    private static $queue;
    //
    private static $consumerTag = 'consumer';
    //Routing_key
    private static $routeKey;
    //链接实例对象
    private static $connection;
    //通道对象
    private static $channel;


    /**
     * @return Rabbit
     */
    public static function getInstance()
    {
        if (!self::$_instance instanceof self) {
            $c = config('mq.host');
            self::$_instance = new self($c);
            return self::$_instance;
        }
        return self::$_instance;
    }

    /**
     * Rabbit constructor.
     */
    private function __construct($c)
    {
        self::$exchange = config('mq.exchange');
        self::$queue = config('mq.queue');
        self::$routeKey = config('mq.route');

        //创建连接
        $connection = new AMQPStreamConnection($c['host'], $c['port'], $c['login'], $c['password'], $c['vhost']);

        if (!$connection) {
            exit('Can not connect to broker!\n');
        }
        self::$connection = $connection;
        self::$channel = $connection->channel();
    }

    /** 生产端
     * @return string
     * @throws \Exception
     */
    public function product($data)
    {
        //创建队列声明
        self::$channel->queue_declare(self::$queue, false, true, false, false);
        //创建交换机声明
        self::$channel->exchange_declare(self::$exchange, 'direct', false, true, false);

        self::$channel->queue_bind(self::$queue, self::$exchange, self::$routeKey);
        $msg_body = json_encode($data);
        $message = new AMQPMessage($msg_body, ['content_type' => 'text/plain', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_NON_PERSISTENT]);
        self::$channel->basic_publish($message, self::$exchange, 'lp', false);
        self::$channel->close();
        self::$connection->close();
        echo 'ok' . PHP_EOL;
    }

    /**
     * 消费端
     * @throws \ErrorException
     * @throws \Exception
     */
    public function consumer($func)
    {
        //创建频道
        self::$channel->queue_declare(self::$queue, false, true, false, false);
        self::$channel->exchange_declare(self::$exchange, 'direct', false, true, false);
        self::$channel->queue_bind(self::$queue, self::$exchange,'lp');

        self::$channel->basic_consume(self::$queue, self::$consumerTag, false, true, false, false, $func);
        //register_shutdown_function([$this, 'closeMQ']);
        //循环消费
        while (count(self::$channel->callbacks)) {
            self::$channel->wait();
        }

        self::$channel->close();
        self::$connection->close();
    }


    /**
     * @param $message
     */
//    public function process_message($message)
//    {
//        $data = $message->body;
//    }

    /**
     * 关闭连接
     * @throws \Exception
     */
    public function closeMQ()
    {
        self::$channel->close();
        self::$connection->close();

    }


    /**
     * 日志记录
     * @param string $data
     * @param int $status
     */
    public function write_log($data = 'str:', $status = 4)
    {
        switch ($status) {
            case 1 :
                $type = 'waning';
                break;
            case 2 :
                $type = 'error';
                break;
            case 3 :
                $type = 'note';
                break;
            default:
                $type = 4;
        }
        \think\facade\Log::write('End ' . $data . '&status=' . $status . '' . '&=', $type);

    }


}