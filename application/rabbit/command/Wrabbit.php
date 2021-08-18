<?php
namespace app\rabbit\command;


use app\common\library\queue\Rabbit;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;

class Wrabbit extends Command{

    protected function configure()
    {
        $this->setName('Wrabbit')->addOption('d', null, Option::VALUE_NONE, 'damone方式启动')->setDescription('is get mq ');
    }

    protected function execute(Input $input, Output $output)
    {

        $func = function ($message) {
            $data = json_decode($message->body, true);
            echo "<pre>";
            print_r($data);
            echo "</pre>";

        };
        Rabbit::getInstance()->consumer($func);


        $output->writeln("TestCommand:");
    }


}
