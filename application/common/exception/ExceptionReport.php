<?php
/**
 * Created by PhpStorm.
 * User: xyr
 * Date: 2020/12/25
 * Time: 14:46
 */

namespace app\common\exception;


use Exception;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\HttpException;
use think\exception\ValidateException;
use think\Request;

class ExceptionReport
{


    protected $exception;
    protected $request;
    protected $report;

    public function __construct(Request $request, Exception $exception)
    {
        $this->request = $request;
        $this->exception = $exception;
    }

    //定义常见异常
    public static $doReport = [
        HttpException::class => ['请求方法错误', 405],
        ModelNotFoundException::class => ['模型不存在', 404],
        DataNotFoundException::class => ['没有数据', 404],
    ];

    /**
     * @param $className
     * @param $cb
     */
    public static function register($className, $cb)
    {
        self::$doReport[$className] = $cb;
    }

    /**
     * @return bool
     */
    public function shouldReturn()
    {
        foreach (array_keys(self::$doReport) as $report) {
            if ($this->exception instanceof $report) {
                $this->report = $report;
                return true;
            }
        }
        return false;
    }

    /**
     * @return \think\response\Json
     */
    public function reports()
    {
        if ($this->exception instanceof ValidateException) {
            return renderError($this->exception->getMessage());
        }
        $message = self::$doReport[$this->report];
        return renderError($message[0]);
    }

    /**
     * @param Exception $exception
     * @return static
     */
    public static function make(Exception $exception)
    {
        return new static(request(), $exception);
    }


    /**
     * @return \think\response\Json
     */
    public function prodReport()
    {
        return renderError(lang('server_internal_error'));
    }
}