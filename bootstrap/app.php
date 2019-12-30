<?php
//use Slim\Factory\AppFactory;
//use Respect\Validation\Validator as validator;
session_cache_limiter('public');
//session_cache_limiter('private_no_expire:');
session_start();

require __DIR__ . '/../vendor/autoload.php';


$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true,
        'debug' => true
    ]
        ]);
$app->add(new \Slim\HttpCache\Cache('public', 0));
$container = $app->getContainer();



require_once __DIR__ . '/container_view.php';


//$container['flash'] = function($container) {
//    return new \Slim\Flash\Messages;
//};


$container['HomeController'] = function($container) {
    return new \App\Controllers\HomeController($container);
};


//$container['validator'] = function ($container) {
//    return new App\Validation\Validator;
//};
//
//$container['csrf'] = function($container) {
//    return new \Slim\Csrf\Guard;
//};
//
//$container['AuthController'] = function($container) {
//    return new \App\Controllers\Auth\AuthController($container);
//};

require __DIR__ . '/../app/routes.php';
