<?php
/**
 * Created by PhpStorm.
 * User: xyr
 * Date: 2021/5/25
 * Time: 16:55
 * Note:痛而不言,笑而不语,不乱于心,不困于情,不畏将来,不念过往
 */

namespace app\common\exception;


use think\Exception;
use think\exception\Handle;


/**
 * 自定义异常处理类
 * Class ExceptionHandler
 * @package app\common\exception
 */
class ExceptionHandler extends Handle
{


    // 状态码
    private $status;

    // 错误信息
    private $message;

    // 附加数据
    public $data = [];


    /**
     * 异常处理
     * @param Exception $e
     */
    public function render(\Exception $e)
    {
        if ($e instanceof BaseException) {
            $this->status = $e->status;
            $this->message = $e->message;
            $this->data = $e->data;
            return $this->renderJson();
        }
        $report = ExceptionReport::make($e);
        if ($report->shouldReturn()) {
            $this->recordErrorLog($e);
            return $report->reports();
        }
        $this->status = 500;
        $this->message = $e->getMessage() ?: lang('server_internal_error');

        // 如果是debug模式, 使用框架自带异常页面
        if (config('app_debug')) {
            return parent::render($e);
        }

        // 将异常写入日志
        $this->recordErrorLog($e);
        // return $report->prodReport();
        return $this->renderJson();

    }


    /**
     * 返回json格式数据
     * @param array $extend
     * @return \think\response\Json
     */
    private function renderJson($extend = [])
    {
        $jsonData = ['message' => $this->message, 'status' => $this->status, 'data' => $this->data];
        return json(array_merge($jsonData, $extend));
    }


    /**
     * 返回json格式数据(debug模式)
     * @param \Throwable $e
     * @return \think\response\Json
     */
    private function renderDebug(\Exception $e)
    {
        $data = [
            'name' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'code' => $e->getCode(),
            'message' => $e->getMessage(),
            'trace' => $e->getTrace(),
            'source' => $this->getSourceCode($e)
        ];

        return $this->renderJson(['debug' => $data]);
    }


    /**
     * 异常写入日志
     * @param \Exception $e
     */
    private function recordErrorLog(\Exception $e)
    {
        // 生成日志内容
        $data = [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'message' => $this->getMessage($e),
            'status' => $this->getCode($e),
        ];
        $log = "[{$data['status']}]{$data['message']} [{$data['file']}:{$data['line']}]";
        $log .= "\r\n" . $e->getTraceAsString();

        write_log(lang('request_error'), $log, 'error');
    }

}