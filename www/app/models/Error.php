<?php
class Error
{
    const ERR_OK = 0;
    public static $errMsg = [
        self::ERR_OK => "操作成功",
    ];

    public static function getErrMsg($code)
    {  
        $ret = "错误信息不存在";
        if (isset(self::$errMsg[$code])) {
            $ret = self::$errMsg[$code];
        }
        return $ret;
    }
}
