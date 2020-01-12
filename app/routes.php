<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;




$app->get('/', 'HomeController:home')
        ->setName('home');

$app->group('/file', function () {

    $this->post('/upload', 'FileController:upload')
            ->setName('file.upload');
    
});

$app->group('/playlist', function () {

    $this->get('/{playlistid}.json', 'PlaylistController:getJsonPlaylistItems')
            ->setName('playlist.getjsonitems');
    $this->get('/{playlistid}.html', 'PlaylistController:getPlaylistItems')
            ->setName('playlist.getitems');
    $this->get('/song/{songid}.html', 'PlaylistController:getItemDetails')
            ->setName('playlist.getitemdetails');
});

$app->group('/deezer', function () {

    
    $this->post('/search.json', 'DeezerController:postSearch')
            ->setName('deezer.search');
    $this->get('/auth', 'DeezerController:getAuth')
            ->setName('deezer.auth');
    
});