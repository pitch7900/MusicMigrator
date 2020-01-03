<?php
use App\Deezer\ITunesLibrary as iTunesLibrary;

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


$container['HomeController'] = function($container) {
    return new \App\Controllers\HomeController($container);
};

$container['FileController'] = function($container) {
    return new \App\Controllers\FileController($container);
};
require __DIR__ . '/../app/routes.php';
