<?php

session_cache_limiter('public');
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

$container['ErrorController'] = function($container) {
    return new \App\Controllers\ErrorController($container);
};

try {
    $dotenv = (Dotenv\Dotenv::createImmutable(__DIR__ . '/../config/'))->load();
} catch (\Dotenv\Exception\InvalidPathException $e) {
    require __DIR__ . '/../app/routes_error.php';
    //Stop exection at this step
    return;
}


$container['HomeController'] = function($container) {
    return new \App\Controllers\HomeController($container);
};

$container['FileController'] = function($container) {
    return new \App\Controllers\FileController($container);
};

$container['iTunesController'] = function($container) {
    return new \App\Controllers\iTunesController($container);
};

$container['DeezerController'] = function($container) {
    return new \App\Controllers\DeezerController($container);
};

$container['SpotifyController'] = function($container) {
    return new \App\Controllers\SpotifyController($container);
};

$container['DestinationsController'] = function($container) {
    return new \App\Controllers\DestinationsController($container);
};
$container['SourcesController'] = function($container) {
    return new \App\Controllers\SourcesController($container);
};

require __DIR__ . '/../app/routes.php';
