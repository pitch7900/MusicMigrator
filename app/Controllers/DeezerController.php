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

        $artist = urlencode($request->getParsedBody()['artist']);
        $album = urlencode($request->getParsedBody()['album']);
        $song = urlencode($request->getParsedBody()['song']);
        $duration = urlencode($request->getParsedBody()['duration']);
        
        $dz = new DZApi();
//        echo "Should Search : " .$artist." ".$album." ".$song;
        $this->logs->write("debug", Logs::$MODE_FILE,"debug.log", "Searching for : \n\t - ".$artist."\n\t - ".$album."\n\t - ".$song."\n\t - ".$duration);
        return $response->withJson($dz->search($artist, $album, $song, $duration));
    }

    public function getAuth(Request $request, Response $response) {
        if (!isset($_SESSION['dzapi'])) {
            $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "Creating a new Deezer API class instance");
            $_SESSION['dzapi'] = serialize(new \App\Deezer\DZApi());
        }



        $code = $request->getQueryParam('code');
        $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "Deezer code recieved");
        $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "Generating app token");
        $token = unserialize($_SESSION['dzapi'])->apiconnect($code);
        $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "Token set to : " . $token);
        $_SESSION['deezer_token'] = $token;
        return $this->response
                        ->withStatus(303)
                        ->withHeader('Location', $this->router->pathFor('home'))
                        ->withHeader('Status', 'Authenticated on deezer');
    }

}
