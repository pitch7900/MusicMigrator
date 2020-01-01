<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;




$app->get('/', 'HomeController:home')
        ->setName('home');

$app->group('/file', function () {

    $this->post('/upload', 'FileController:upload')
            ->setName('file.upload');
    
});

