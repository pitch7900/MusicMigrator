<?php

namespace App\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * Description of PlaylistController
 *
 * @author pierre
 */
class PlaylistController extends Controller {

    public function __construct($container) {

        parent::__construct($container);
    }

    public function getJsonPlaylistItems(Request $request, Response $response, $args) {
        $playlistid = $args['playlistid'];
        return $response->withJson(unserialize($_SESSION["Library"])->getPlaylistItems($playlistid));
    }

    public function getPlaylistItems(Request $request, Response $response, $args) {
        $playlistid = $args['playlistid'];
        $arguments['playlist'] = unserialize($_SESSION["Library"])->getPlaylistItems($playlistid);
        $arguments['playlistname'] = unserialize($_SESSION["Library"])->getPlaylistName($playlistid);
        if (isset($_SESSION['deezer_token'])) {
            $arguments['deezerauthenticated'] = true;
            $arguments['deezerplaylists'] = unserialize($_SESSION['dzapi'])->getUserPlaylists();
        } else {
            $arguments['deezerauthenticated'] = false;
        }
        return $this->view->render($response, 'songs.twig', $arguments);
    }

    public function getItemDetails(Request $request, Response $response, $args) {
        $trackid = $args['songid'];
        $arguments['songid'] = $trackid;
        $track = unserialize($_SESSION["Library"])->getTrack($trackid);

        $arguments['song'] = $track['Song'];
        $arguments['artist'] = $track['Artist'];
        $arguments['album'] = $track['Album'];
        $arguments['duration'] = $track['Time'];
        $arguments['track'] = $track['Track'];
        $arguments['totaltracks'] = $track['TotalTracks'];
        return $this->view->render($response, 'elements/song.twig', $arguments);
    }

}
