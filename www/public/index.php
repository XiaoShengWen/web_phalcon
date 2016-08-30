<?php
use Phalcon\Mvc\Application;
use Phalcon\Di\FactoryDefault;
use Phalcon\Config\Adapter\Ini as ConfigIni;
use Phalcon\Mvc\Url as UrlProvider;

// 设置默认时区
date_default_timezone_set('Asia/Shanghai');

define('APP_PATH', realpath(dirname(dirname(__FILE__))));

$di = new FactoryDefault();

// 获取基本配置，同时判断是否有开发环境配置
$config = new ConfigIni(APP_PATH . '/app/config/config.ini.dev');
if (is_readable(APP_PATH . '/app/config/config.ini')) {
    $override = new ConfigIni(APP_PATH . '/app/config/config.ini');
    $config->merge($override);
}
$di->setShared('config', function () use ($config) {
    return $config;
});

// 设置自动加载
require APP_PATH . '/app/config/loader.php';

// 设置应用服务
require APP_PATH . '/app/config/services.php';


$application = new Application($di);
echo $application->handle()->getContent();

