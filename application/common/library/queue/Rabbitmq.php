<?php
/**
 * Created by PhpStorm.
 * User: xyr
 * Date: 2021/5/26
 * Time: 16:51
 * Note:痛而不言,笑而不语,不乱于心,不困于情,不畏将来,不念过往
 */

namespace app\common\library\queue;


use AMQPChannel;
use AMQPConnection;
use AMQPExchange;
use AMQPQueue;

class Rabbitmq implements MqInterface
{
    //保存类实例的静态成员变量
    private static $_instance;
    //通道
    private static $channel;
    //mq实例对象
    private static $amp;
    //route_key
    private static $route = 'key_1';
    //队列名
    private static $q;
    //交换机
    private static $ex;
    //队列实例
    private static $queue;


    /**
     * 获取实例对象
     * @return Rabbitmq
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
     * Base constructor.
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
     * @param $exchangeName
     * @param $queueName
     * @return mixed
     * @throws \AMQPChannelException
     * @throws \AMQPConnectionException
     * @throws \AMQPExchangeException
     * @throws \AMQPQueueException
     */
    public function listen($exchangeName = '', $queueName = '')
    {
        $queueName = $queueName ?: config('mq.queue');
        $exchangeName = $exchangeName ?: config('mq.exchange');
        self::$queue = $queueName;
        return $this->setExchange($exchangeName, $queueName);
    }


    /**
     * 创建交换机
     * @param $exchangeName
     * @param $queueName
     * @return mixed
     * @throws \AMQPChannelException
     * @throws \AMQPConnectionException
     * @throws \AMQPExchangeException
     * @throws \AMQPQueueException
     */
    public function setExchange($exchangeName, $queueName)
    {
        //创建交换机
        $ex = new AMQPExchange(self::$channel);
        self::$ex = $ex;
        $ex->setName($exchangeName);

        $ex->setType(AMQP_EX_TYPE_DIRECT); //direct类型
        $ex->setFlags(AMQP_DURABLE); //持久化
        $ex->declareExchange();
        return self::setQueue($queueName, $exchangeName);
    }


    /**
     * 创建队列
     * @param $queueName
     * @param $exchangeName
     * @return mixed
     * @throws \AMQPChannelException
     * @throws \AMQPConnectionException
     * @throws \AMQPQueueException
     */
    private static function setQueue($queueName, $exchangeName)
    {
        //  创建队列
        $q = new AMQPQueue(self::$channel);
        $q->setName($queueName);
        $q->setFlags(AMQP_DURABLE);
        $q->declareQueue();

        // 用于绑定队列和交换机
        $routingKey = self::$route;
        $q->bind($exchangeName, $routingKey);
        self::$q = $q;
        return self::$_instance;
    }

    /**
     * 关闭连接
     */
    private static function closeConn()
    {
        self::$amp->disconnect();
    }


    //__clone方法防止对象被复制克隆
    public function __clone()
    {
        trigger_error('Clone is not allow!', E_USER_ERROR);
    }


    /**
     * @return false
     */
    public function run()
    {
        if (!self::$q) {
            return false;
        }

        while (true) {
            echo '读取数据';
            self::$q->consume([$this, 'func']);
            echo '读取结束';
        }
    }


    public function func($evn, $queue)
    {
        $msg = $evn->getBody();
        write_log('rabbitmq数据', json_decode($msg, true), 'mq');
        $queue->ack($evn->getDeliveryTag()); //手动发送ACK应答
    }


    public function publish($msg)
    {
        while (true) {
            sleep(1);
            $data = [];
            for ($i = 0; $i < 5; $i++) {
                $data[$i] = 'qh_' . $i;
            }
            if (self::$ex->publish(json_encode($data), self::$route)) {
                //写入文件等操作
                echo $msg;
            }
        }
    }


}