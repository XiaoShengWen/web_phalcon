<?php
require_once(APP_PATH . "/vendor/autoload.php");
 
$loader = new Phalcon\Loader();
$loader->registerDirs(array(
    APP_PATH . $config->application->controllersDir,
    APP_PATH . $config->application->pluginsDir,
    APP_PATH . $config->application->libraryDir,
    APP_PATH . $config->application->modelsDir,
    APP_PATH . $config->application->formsDir,
    APP_PATH . '/app/tasks/',
));
    
$loader->registerNamespaces([
    'App\Models' => APP_PATH . "/app/models/",
]);
    
$loader->register();

