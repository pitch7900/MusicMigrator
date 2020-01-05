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

    public function getPlaylistItems(Request $request, Response $response, $args) {
        $playlistid = $args['playlistid'];
        $filename = __DIR__ . "/../../libfiles/" . session_id() . ".xml";
        if (is_readable($filename)) {

            $Library = new \App\Deezer\ITunesLibrary();
            $Library->loadXMLFile($filename);
        }
        return $response->withJson($Library->getPlaylistItems($playlistid));
    }

    public function getItemDetails(Request $request, Response $response, $args) {
        $trackid = $args['songid'];
      

        $arguments['songid'] = $trackid;
        $filename = __DIR__ . "/../../libfiles/" . session_id() . ".xml";
        if (is_readable($filename)) {
            $Library = new \App\Deezer\ITunesLibrary();
            $Library->loadXMLFile($filename);
            $track = $Library->getTrack($trackid);
            $arguments['song'] = $track['Song'];
            $arguments['artist'] = $track['Artist'];
            $arguments['album'] = $track['Album'];
        }
        return $this->view->render($response, 'elements/song.twig', $arguments);
    }

}
