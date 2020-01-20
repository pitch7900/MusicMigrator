<?php

namespace App\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \App\Utils\Logs as Logs;

class HomeController extends Controller {

    private $logs;

    public function __construct($container) {
        parent::__construct($container);
        $this->logs = new Logs();
    }

    /**
     * Return the "Home" view 
     * @param Request $request
     * @param Response $response
     * @return HTML
     */
    public function home(Request $request, Response $response) {





        if (!isset($_SESSION['dzapi'])) {
            $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "Creating a new Deezer API class instance");
            $_SESSION['dzapi'] = serialize(new \App\Deezer\DZApi());
        }

        $arguments['deezerauthurl'] = unserialize($_SESSION['dzapi'])->getAuthUrl(getenv("SITEURL") . "/deezer/auth");
        $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "Deezer Auth URL is : " . $arguments['deezerauthurl']);
        $arguments['deezerauthenticated'] = 0;

        // Check if we have a valid session token
        if (isset($_SESSION['deezer_token'])) {
            //Check if the token has not expired
            if ($_SESSION['deezer_token_expires'] > time()) {
                $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "Session token stored in Session : " . $_SESSION['deezer_token']);
                $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "Session token stored in class : " . unserialize($_SESSION['dzapi'])->getSToken());
                $userinfo = unserialize($_SESSION['dzapi'])->getUserInformation();
                $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", json_encode($userinfo));
                $arguments['deezertoken'] = $_SESSION['deezer_token'];
                $arguments['deezerauthenticated'] = 1;
                $arguments['deezerUserInformations'] = $userinfo;
                $arguments['deezerusername'] = $userinfo['name'];
                $arguments['deezerpict'] = $userinfo['picture'];
                $arguments['deezeruserlink'] = $userinfo['link'];
                $arguments['deezerauthenticated'] = 1;
            } else {
                //Token has expired 
                unset($_SESSION['deezer_token']);
                unset($_SESSION['deezer_token_expires']);
                unset($_SESSION['dzapi']);
                $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "Creating a new Deezer API class instance");
                $_SESSION['dzapi'] = serialize(new \App\Deezer\DZApi());
                return $this->view->render($response, 'home_logintodeezer.twig', $arguments);
            }
//            var_dump(unserialize($_SESSION['dzapi'])->getUserPlaylists()['data']);
        } else {
            return $this->view->render($response, 'home_logintodeezer.twig', $arguments);
        }


        $arguments['fileuploaded'] = false;


        if (isset($_SESSION['Library'])) {
            $arguments['fileuploaded'] = true;
            $arguments['playlists'] = unserialize($_SESSION["Library"])->getPlaylists();
        } else {
            //No file is uploaded
            if (strcmp($request->getParam('Status'), "'FileError'")) {
                $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "File error");
                $arguments['fileuploadederror'] = true;
            } else {
                $arguments['fileuploadederror'] = false;
            }
            return $this->view->render($response, 'home_loadfile.twig', $arguments);
        }

        return $this->view->render($response, 'home.twig', $arguments);
    }

}
