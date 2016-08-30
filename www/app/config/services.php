<?php
use Phalcon\Events;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Router;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\View\Engine\Volt;
use Phalcon\Filter;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\ErrorHandler;
use Phalcon\Mvc\Application;

// Web服务相关配置，CLI模式下不要执行
if (!($di instanceof Phalcon\Di\FactoryDefault\Cli)) {
    $di->set('router', function () {
        $router = new Router();
        // 注意URI的来源, 默认使用的是$_GET['_url']
        $router->setUriSource(Router::URI_SOURCE_SERVER_REQUEST_URI);

        $router->add(
            '/api/v1/([a-zA-Z0-9]+)/:action',
            [
                'controller' => 'apiv1',
                'action' => 2,
                'pid' => 1,
            ]
        );

        $router->add(
            '/admin/:controller/:action',
            array(
                'module' => 'backend',
                'namespace'  => 'backend\Controllers',
                'controller' => 1,
                'action'     => 2,
            )
        );

        $router->add(
            '/admin/:controller/:action/:params',
            array(
                'module' => 'backend',
                'namespace'  => 'backend\Controllers',
                'controller' => 1,
                'action'     => 2,
                "params"     => 3,
            )
        );

        return $router;
    });

    // 设置事件管理
    $di->set('dispatcher', function () use ($di) {
        $eventsManager = new Events\Manager();
        
        $eventsManager->attach("dispatch:beforeException", function ($event, $dispatcher, $exception) {
            // Handle 404 exceptions
            if ($exception instanceof Dispatcher\Exception) {
                $dispatcher->forward([
                    'controller' => 'index',
                    'action' => 'index',
                ]);
                return false;
            }

            if ($exception instanceof App\Models\Exception) {
                $dispatcher->forward([
                    'controller' => 'index',
                    'action' => 'error',
                    'params' => [$exception],
                ]);
                return false;
            }
        });
        $dispatcher = new Dispatcher();
        $dispatcher->setEventsManager($eventsManager);
        return $dispatcher;
    });

    // 设置视图
    $di->set('view', function () use ($config) {
        $view = new View();
        $view->setViewsDir(APP_PATH . $config->application->viewsDir);
        $view->registerEngines(array(
            ".volt" => 'volt'
        ));

        return $view;
    });

    // 设置volt模板
    $di->set('volt', function ($view, $di) use ($config) {
        $volt = new Volt($view, $di);

        $volt->setOptions(array(
            "compiledPath" => $config->volt->compiledPath,
            "compileAlways" => filter_var($config->volt->compileAlways, FILTER_VALIDATE_BOOLEAN),
        ));

        $compiler = $volt->getCompiler();
        $compiler->addFunction('in_array', 'in_array');
        $compiler->addFunction('implode', 'implode');
        $compiler->addFunction('is_a', 'is_a');

        return $volt;
    }, true);
}

$di->set('log', function () use ($config) {
    date_default_timezone_set('Asia/Shanghai');

    $logger = new Logger('main');
    $logger->pushHandler(new StreamHandler('php://stderr', Logger::DEBUG));
    /* $sentryClient = new \Raven_Client($config['sentry']['dsn']); */
    /* $handler = new Monolog\Handler\RavenHandler($sentryClient); */
    /* $handler->setLevel(Psr\Log\LogLevel::WARNING); */
    /* $handler->setFormatter(new Monolog\Formatter\LineFormatter("%message% %context% %extra%\n")); */
    /* $logger->pushHandler($handler); */
    return $logger;
});
ErrorHandler::register($di->get('log'));

// 设置MongoDB
use Phalcon\Config\Adapter\Ini as ConfigIni;
$di->setShared("mongo", function () use ($config) {
    $servers = "mongodb://" . $config->mongodb->host;
    $options = [
        'replicaSet' => $config->mongodb->replicaSet,
        'username' => $config->mongodb->username,
        'password' => $config->mongodb->password,
        'db' => 'admin',
        'connectTimeoutMS' => 5000,
        'readPreference' => MongoClient::RP_PRIMARY_PREFERRED,
    ];
    $mongo = new MongoClient($servers, $options);
    return $mongo->selectDB($config->mongodb->database);
});

// Phalcon框架MongoDB组件必备
$di->setShared('collectionManager', function () {
    return new \Phalcon\Mvc\Collection\Manager();
});

// 设置Session
$di->setShared('session', function () use ($di) {
    ini_set('session.gc_maxlifetime', 86400);
    ini_set('session.cookie_lifetime', 86400);
    ini_set('session.cookie_httponly', true);
    $mongo = $di->get('mongo');
    $session = new Session\Adapter\Mongo([
        'collection' => $mongo->session,
    ]);

    $session->start();
    return $session;
});
//Set up the flash service
$di->set('flash', function() {
    $flash = new \Phalcon\Flash\Session(array(
        'error'   => 'alert alert-danger',
        'success' => 'alert alert-success',
        'notice'  => 'alert alert-info',
        'warning'  => 'alert alert-warning',
    ));
    return $flash;
});

// 添加自定义过滤器，现在包括数组格式
$di->setShared('filter', function () use ($di) {
    $filter = new Filter();

    $filter->add('array', function ($value) {
        return is_array($value) ? $value : null;
    });

    $filter->add('mongoid', function ($value) {
        return \MongoId::isValid($value) ? $value : null;
    });

    $filter->add('alphanum2', function ($value) {
        return preg_match('/[^a-zA-Z0-9_\-]/', $value) === 1 ? null : $value;
    });

    $filter->add('int', function ($value) {
        return filter_var($value, FILTER_VALIDATE_INT) ?: null;
    });

    $filter->add('float', function ($value) {
        return filter_var($value, FILTER_VALIDATE_FLOAT) ?: null;
    });

    return $filter;
});   

