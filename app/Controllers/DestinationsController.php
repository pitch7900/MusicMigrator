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
class DestinationsController extends Controller {

    private $logs;

    public function __construct($container) {

        parent::__construct($container);
        $this->logs = new Logs();
    }

    public function getChooseDestinations(Request $request, Response $response) {
        $deezerapi = new \App\MusicSources\DeezerApi();
        $arguments['deezerauthurl'] = $deezerapi->getAuthUrl(getenv("SITEURL") . "/deezer/auth/destinations");
        if (!isset($_SESSION['spotifyapi'])) {
            $_SESSION['spotifyapi'] = serialize(new \App\MusicSources\SpotifyApi());
        }
        $arguments['spotifyauthurl'] = unserialize($_SESSION['spotifyapi'])->getAuthUrl(getenv("SITEURL") . "/spotify/auth/destinations");
        $arguments['sourcechoosed'] = $_SESSION['sources'];
        return $this->view->render($response, 'Destinations/Choose.twig', $arguments);
    }

    public function getChangeDestinations(Request $request, Response $response) {
        unset($_SESSION['sources']);
        unset($_SESSION['destinations']);
        return $this->response
                        ->withStatus(303)
                        ->withHeader('Location', $this->router->pathFor('home'));
    }

}
