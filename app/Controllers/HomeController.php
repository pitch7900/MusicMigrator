<?php

namespace App\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class HomeController extends Controller {

    private $log;

    public function __construct($container) {
        parent::__construct($container);
        $this->log = new Logger('HomeController.php');
        $this->log->pushHandler(new StreamHandler(__DIR__.'/../../logs/debug.log', Logger::DEBUG));
    }

    private function check_source() {
        if (!isset($_SESSION['sources'])) {
            return $this->response
                            ->withStatus(303)
                            ->withHeader('Location', $this->router->pathFor('sources.choose'));
        } else {
            return null;
        }
    }

    private function check_destination() {
        if (!isset($_SESSION['destinations'])) {
            return $this->response
                            ->withStatus(303)
                            ->withHeader('Location', $this->router->pathFor('destinations.choose'));
        } else {
            return null;
        }
    }

    private function DeezerArguments() {
        if (isset($_SESSION['deezer_token'])) {
            //Check if the token has not expired
            if ($_SESSION['deezer_token_expires'] > time()) {
              
                $this->log->debug("(DeezerArguments)Session token stored in Session : " . $_SESSION['deezer_token']);
                $this->log->debug("(DeezerArguments)Session token stored in class : " . unserialize($_SESSION['deezerapi'])->getSToken());
                $userinfo = unserialize($_SESSION['deezerapi'])->getUserInformation();
                $this->log->debug("(DeezerArguments) : ". json_encode($userinfo));
                $arguments['deezertoken'] = $_SESSION['deezer_token'];
                $arguments['deezerauthenticated'] = 1;
                $arguments['deezerUserInformations'] = $userinfo;
                $arguments['deezerusername'] = $userinfo['name'];
                $arguments['deezerpict'] = $userinfo['picture'];
                $arguments['deezeruserlink'] = $userinfo['link'];
                $arguments['deezerauthenticated'] = 1;
            }

            if (strcmp($_SESSION['sources'], "deezer") == 0) {
                $deezerplaylist = unserialize($_SESSION['deezerapi'])->getUserPlaylists();
                $this->log->debug("(DeezerArguments) Deezer is set as source : " . var_export($deezerplaylist, true));
                $arguments['playlists'] = $deezerplaylist;
            }
        }

        return $arguments;
    }

    private function SpotifyArguments() {
        if (isset($_SESSION['spotifytoken'])) {
            $spotifyuserinfo = unserialize($_SESSION['spotifyapi'])->getUserInformation();
            $arguments['spotifyuserlink'] = $spotifyuserinfo['href'];
            $arguments['spotifypict'] = $spotifyuserinfo['images'][0];
            $arguments['spotifyusername'] = $spotifyuserinfo['display_name'];
        }
        if (strcmp($_SESSION['sources'], "spotify") == 0) {
                $spotifyplaylist = unserialize($_SESSION['spotifyapi'])->getUserPlaylists();
                $this->log->debug("SpotifyArguments) Spotify is set as source : " . var_export($spotifyplaylist, true));
                $arguments['playlists'] = $spotifyplaylist;
            }
        return $arguments;
    }

    private function iTunesArguments(Request $request) {
        if (isset($_SESSION["itunesapi"])) {
            $arguments['fileuploaded'] = true;
            $arguments['playlists'] = unserialize($_SESSION["itunesapi"])->getPlaylists();
        } else {
            $arguments['fileuploadederror'] = false;
            $Status = $request->getParam('Status');
            //No file is uploaded
            if (strcmp($Status, "FileError") == 0) {
                $this->log->debug("home) File error - " . $Status);
                $arguments['fileuploadederror'] = true;
                $arguments['fileuploadederrormessage'] = "File upload error. This file is not a clean iTunes Library file";
            }
            if (strcmp($Status, "NoFile") == 0) {
                $this->log->debug("home) No File - " . $Status);
                $arguments['fileuploadederror'] = true;
                $arguments['fileuploadederrormessage'] = "Please upload a file";
            }
            return false;
        }
        return $arguments;
    }

    /**
     * Return the "Home" view 
     * @param Request $request
     * @param Response $response
     * @return HTML
     */
    public function home(Request $request, Response $response) {
        if ($this->check_source() != null) {
            return $this->check_source();
        }
        if ($this->check_destination() != null) {
            return $this->check_destination();
        }


        $arguments['source'] = $_SESSION['sources'];
        $arguments['destination'] = $_SESSION['destinations'];
        if (strcmp($_SESSION['sources'], "itunes") == 0) {
            $arguments = array_merge($arguments, $this->iTunesArguments($request));
            if ($arguments['fileuploadederror']) {
                return $this->view->render($response, 'home_choosesource.twig', $arguments);
            }
            $this->log->debug("home) arguments after merging iTunesLib " . var_export($arguments, true));
        }

        if (strcmp($_SESSION['destinations'], "deezer") == 0 || strcmp($_SESSION['sources'], "deezer") == 0) {

            $arguments = array_merge($arguments, $this->DeezerArguments());
            $this->log->debug("home) arguments after mergin deezer " . var_export($arguments, true));
        }

        if (strcmp($_SESSION['destinations'], "spotify") == 0 || strcmp($_SESSION['sources'], "spotify") == 0) {
            $arguments = array_merge($arguments, $this->SpotifyArguments());
            $this->log->debug("home) arguments after mergin Spotify " . var_export($arguments, true));
        }
        $this->log->debug("home) arguments global " . var_export($arguments, true));
        return $this->view->render($response, 'home.twig', $arguments);
    }


    /**
     * Return the spinning waiting icon defined in "waiting.twig"
     * @param Request $request
     * @param Response $response
     * @return type
     */
    public function getWaitingIcons(Request $request, Response $response) {
        return $this->view->render($response, 'waiting.twig');
    }

}
