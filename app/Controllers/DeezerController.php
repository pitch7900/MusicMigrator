<?php

namespace App\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use App\Deezer\DZApi as DZApi;
use \App\Utils\Logs as Logs;

/**
 * Description of DeezerController
 *
 * @author pierre
 */
class DeezerController extends Controller {

    private $logs;

    public function __construct($container) {

        parent::__construct($container);
        $this->logs = new Logs();
    }

    public function postSearch(Request $request, Response $response) {
        session_write_close();
        $trackid = urlencode($request->getParsedBody()['trackid']);
        $artist = urlencode($request->getParsedBody()['artist']);
        $album = urlencode($request->getParsedBody()['album']);
        $song = urlencode($request->getParsedBody()['song']);
        $duration = urlencode($request->getParsedBody()['duration']);

        $dz = new DZApi();
//        echo "Should Search : " .$artist." ".$album." ".$song;
        $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "DeezerController.php(postSearch) Searching for : \n\t - " . $artist . "\n\t - " . $album . "\n\t - " . $song . "\n\t - " . $duration);
        $search = $dz->SearchIndividual($trackid, $artist, $album, $song, $duration);
        if (isset($search['info']['error'])) {
            return $this->response
                            ->withStatus(404)
                            ->withHeader('Error', 'Too many session')
                            ->withJson($search);
        }
        
        return $response->withJson($search);
    }

    public function getAboutme(Request $request, Response $response) {
        if (!isset($_SESSION['dzapi']) || isset(unserialize($_SESSION['dzapi'])->getUserInformation()['error'])) {
            return $this->response
                            ->withStatus(401)
                            ->withHeader('Error', 'Not logged in to Deezer');
        } else {
            $returninformation = unserialize($_SESSION['dzapi'])->getUserInformation();
            $returninformation["expiration_time"]=$_SESSION['deezer_token_expires'];
            return $response->withJson($returninformation);
        }
    }

    public function getMyPlaylists(Request $request, Response $response) {
        if (!isset($_SESSION['dzapi'])) {
            return $this->response
                            ->withStatus(401)
                            ->withHeader('Error', 'Not logged in to Deezer');
        } else {
            return $response->withJson(unserialize($_SESSION['dzapi'])->getUserPlaylists());
        }
    }

    public function getAuth(Request $request, Response $response) {
        if (!isset($_SESSION['dzapi'])) {
            $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "DeezerController.php(getAuth) Creating a new Deezer API class instance");
            $_SESSION['dzapi'] = serialize(new \App\Deezer\DZApi());
        }



        $code = $request->getQueryParam('code');
        $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "DeezerController.php(getAuth) Deezer code recieved");
        $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "DeezerController.php(getAuth) Generating app token");
        $token = unserialize($_SESSION['dzapi'])->apiconnect($code);
        $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "DeezerController.php(getAuth) Token set to : " . $token);
        $_SESSION['deezer_token'] = $token;
        return $this->response
                        ->withStatus(303)
                        ->withHeader('Location', $this->router->pathFor('home'))
                        ->withHeader('Status', 'Authenticated on deezer');
    }

    public function postSearchList(Request $request, Response $response) {

        $tracklist = json_decode($request->getParsedBody()['tracklist']);
        if (!isset($_SESSION['dzapi'])) {
            $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "DeezerController.php(postSearchList) Creating a new Deezer API class instance");
            $_SESSION['dzapi'] = serialize(new \App\Deezer\DZApi());
        }

        return $response->withJson(unserialize($_SESSION['dzapi'])->SearchList($tracklist));
    }

    public function getSearchList(Request $request, Response $response) {
        if (!isset($_SESSION['dzapi'])) {
            return $this->response
                            ->withStatus(412)
                            ->withHeader('Error', 'Session not initialized');
        } else {
            return $response->withJson(unserialize($_SESSION['deezersearchlist']));
        }
    }

    public function postCreatePlaylist(Request $request, Response $response) {
        $playlistname = urlencode($request->getParsedBody()['name']);
        $playlistpublic = urlencode($request->getParsedBody()['public']);
        $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "DeezerController.php(postCreatePlaylist)recieved query to create a playlist :", $playlistname . " - " . $playlistpublic);
        if (!isset($_SESSION['dzapi'])) {
            return $this->response
                            ->withStatus(401)
                            ->withHeader('Error', 'Not logged in to Deezer');
        } else {
            return $response->withJson(unserialize($_SESSION['dzapi'])->CreatePlaylist($playlistname, $playlistpublic));
        }
    }

    public function postPlaylistAddSongs(Request $request, Response $response, $args) {

        $playlistid = $args['playlistid'];

        $tracklist = json_decode($request->getParsedBody()['tracklist']);
        $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "DeezerController.php(postPlaylistAddSongs)recieved query to add to a playlist :" . $playlistid . "\n\t" . var_export($tracklist, true));
        return $response->withJson(unserialize($_SESSION['dzapi'])->AddTracksToPlaylist($playlistid, $tracklist));
    }

}
