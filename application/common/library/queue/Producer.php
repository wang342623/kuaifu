<?php
/**
 * Created by PhpStorm.
 * User: xyr
 * Date: 2021/5/27
 * Time: 14:26
 * Note:痛而不言,笑而不语,不乱于心,不困于情,不畏将来,不念过往
 */

namespace app\common\library\queue;


use AMQPChannel;
use AMQPConnection;
use AMQPExchange;
use AMQPQueue;

/**
 * Class Producer
 * @package app\common\library\queue
 */
class Producer implements MqInterface
{
    //对象实例
    private static $_instance;
    //通道
    private static $channel;
    //mq链接对象
    private static $amp;
    //route_key
    private static $route = 'key_1';
    //队列实例
    private static $q;
    //交换机
    private static $ex;
    //队列名
    private static $queue;


    /**
     * 获取producer实例对象
     * @return Producer
     * @throws \AMQPConnectionException
     */
    public static function getInstance()
    {
        if (!self::$_instance instanceof self) {
            $config = config('mq.host');
            self::$_instance = new self($config);
            return self::$_instance;
        }

        return self::$_instance;
    }


    /**
     * Producer constructor.
     * @param $conn
     * @throws \AMQPConnectionException
     */
    private function __construct($conn)
    {
        //创建连接和channel
        $conn = new AMQPConnection($conn);
        if (!$conn->connect()) {
            die("Cannot connect to the broker!\n");
        }
        self::$channel = new AMQPChannel($conn);
        self::$amp = $conn;

    }


    /**
     * 创建交换机
     * @param string $exchangeName
     * @return mixed
     * @throws \AMQPChannelException
     * @throws \AMQPConnectionException
     * @throws \AMQPExchangeException
     * @throws \AMQPQueueException
     */
    public function setExchange($exchangeName = '', $queueName)
    {
        $exchangeName = $exchangeName ?: config('mq.exchange');
        $queueName = $queueName ?: config('mq.queue');
        self::$ex = new AMQPExchange(self::$channel);
        self::$ex->setName($exchangeName);

        self::$ex->setType(AMQP_EX_TYPE_DIRECT); //direct类型
        self::$ex->setFlags(AMQP_DURABLE); //持久化
        self::$ex->declareExchange();
        return self::setQueue($exchangeName, $queueName);
    }


    /**
     * @throws \AMQPQueueException
     * @throws \AMQPChannelException
     * @throws \AMQPConnectionException
     */
    private static function setQueue($exchangeName, $queueName = '')
    {
        //  创建队列
        self::$q = new \AMQPQueue(self::$channel);
        self::$q->setName($queueName);
        self::$q->setFlags(AMQP_DURABLE);
        self::$q->declareQueue();

        // 用于绑定队列和交换机
        $routingKey = self::$route;
        self::$q->bind($exchangeName, $routingKey);
        return self::$_instance;
    }


    /**
     * 关闭连接
     */
    private static function close()
    {
        self::$amp->disconnect();
    }


    //__clone方法防止对象被复制克隆
    public function __clone()
    {
        trigger_error('Clone is not allow!', E_USER_ERROR);
    }


    /**
     *
     * @param $data
     */
    public function push($data)
    {
        while (true) {
            sleep(1);
            if (self::$ex->publish($data, self::$route)) {
                //写入文件等操作
                echo 'success';
            }
        }
    }

    public function consumer()
    {
        while (true){
            self::$q->consume([$this,'run']);
        }
    }

    public function run($evn,$queue)
    {
        $msg = $evn->getBody();
        write_log('rabbitmq数据', json_decode($msg, true), 'mq');
        $queue->ack($evn->getDeliveryTag()); //手动发送ACK应答
    }
}