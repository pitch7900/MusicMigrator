<?php

namespace App\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use App\MusicSources\SpotifyApi as SpotifyApi;
use \App\Utils\Logs as Logs;

/**
 * Description of DeezerController
 *
 * @author pierre
 */
class SpotifyController extends Controller {

    private $logs;

    public function __construct($container) {

        parent::__construct($container);
        $this->logs = new Logs();
    }

    /**
     * Authenticate on Spotify
     * @param Request $request
     * @param Response $response
     * @return type
     */
    public function getAuth(Request $request, Response $response) {
        $code = $request->getQueryParam('code');
         $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "SpotifyController.php(getAuth) Auth Code recieved : ".$code);
        return $this->response
                        ->withStatus(303)
                        ->withHeader('Location', $this->router->pathFor('home'))
                        ->withHeader('Status', 'Authenticated on Spotify')
                        ->withHeader('AuthCode', $code);
    }

}
