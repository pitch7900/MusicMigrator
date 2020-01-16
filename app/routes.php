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
    $this->post('/searchlist.json', 'DeezerController:postSearchList')
            ->setName('deezer.searchlist');
    $this->get('/searchlist.json', 'DeezerController:getSearchList')
            ->setName('deezer.searchlist');
    $this->get('/auth', 'DeezerController:getAuth')
            ->setName('deezer.auth');
    $this->get('/me/about.json', 'DeezerController:getAboutme')
            ->setName('deezer.me.about');
    $this->get('/me/playlists.json', 'DeezerController:getMyPlaylists')
            ->setName('deezer.me.playlists');
    $this->post('/me/createplaylist','DeezerController:postCreatePlaylist')
            ->setName('deezer.me.createplaylist');
    $this->post('/playlist/{playlistid}/addsongs','DeezerController:postPlaylistAddSongs')
            ->setName('deezer.playlist.addsongs');
    
});