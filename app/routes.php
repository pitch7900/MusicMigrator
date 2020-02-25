<?php

$app->get('/', 'HomeController:home')
        ->setName('home');

$app->get('/spinner.html', 'HomeController:getWaitingIcons')
        ->setName('getWaitingIcons');


$app->group('/file', function () {

    $this->post('/upload', 'FileController:upload')
            ->setName('file.upload');
    
});

$app->group('/itunes', function () {

    $this->get('/playlist/{playlistid}.json', 'iTunesController:getJsonPlaylistItems')
            ->setName('itunes.getplaylistjsonitems');
    $this->get('/playlist/{playlistid}.html', 'iTunesController:getPlaylistItems')
            ->setName('itunes.getplaylistitems');
    $this->get('/song/{songid}.html', 'iTunesController:getItemDetails')
            ->setName('itunes.getitemdetails');
});

$app->group('/deezer', function () {
    $this->post('/search.json', 'DeezerController:postSearch')
            ->setName('deezer.search');
    $this->post('/searchlist.json', 'DeezerController:postSearchList')
            ->setName('deezer.searchlist');
    $this->get('/searchlist.json', 'DeezerController:getSearchList')
            ->setName('deezer.searchlist');
    $this->get('/auth/{sourceordestination}', 'DeezerController:getAuth')
            ->setName('deezer.auth');
    $this->get('/me/about.json', 'DeezerController:getAboutme')
            ->setName('deezer.me.about');
    $this->get('/me/playlists.json', 'DeezerController:getMyPlaylists')
            ->setName('deezer.me.playlists');
    $this->post('/me/createplaylist','DeezerController:postCreatePlaylist')
            ->setName('deezer.me.createplaylist');
    $this->post('/playlist/{playlistid}/addsongs','DeezerController:postPlaylistAddSongs')
            ->setName('deezer.playlist.addsongs');
    $this->get('/playlist/{playlistid}.html', 'DeezerController:getPlaylistItems')
            ->setName('deezer.getplaylistitems');
    $this->get('/playlist/{playlistid}/info.json','DeezerController:getPlaylistInfo')
            ->setName('deezer.playlist.informations');
});

$app->group('/spotify', function () {
    $this->post('/search.json', 'SpotifyController:postSearch')
            ->setName('spotify.search');
    $this->get('/auth/{sourceordestination}', 'SpotifyController:getAuth')
            ->setName('spotify.sourcesordestintation.auth');
    $this->get('/me/about.json', 'SpotifyController:getAboutme')
            ->setName('spotify.me.about');
    $this->post('/me/createplaylist','SpotifyController:postCreatePlaylist')
            ->setName('spotify.me.createplaylist');
    $this->get('/playlist/{playlistid}.html', 'SpotifyController:getPlaylistItems')
            ->setName('spotify.getplaylistitems');
    $this->post('/playlist/{playlistid}/addsongs','SpotifyController:postPlaylistAddSongs')
            ->setName('spotify.playlist.addsongs');
    $this->get('/playlist/{playlistid}/info.json','SpotifyController:getPlaylistInfo')
            ->setName('spotify.playlist.informations');
});


$app->group('/sources', function () {
    $this->get('/choose', 'SourcesController:getChooseSources')
            ->setName('sources.choose');
    $this->get('/change', 'SourcesController:getChangeSources')
            ->setName('sources.change');
});


$app->group('/destinations', function () {
    $this->get('/choose', 'DestinationsController:getChooseDestinations')
            ->setName('destinations.choose');
    $this->get('/change', 'DestinationsController:getChangeDestinations')
            ->setName('destinations.change');
});
