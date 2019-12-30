<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;


$app->get('/', 'HomeController:home');
//        ->add(new AuthMiddleware($container))
//        ->setName('home');

$app->get('/test', function(Request $request, Response $response, $args) {

 
    echo " <h1>test</h1>\n";
});
