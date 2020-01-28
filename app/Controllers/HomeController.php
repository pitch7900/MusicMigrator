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
     * Check if user is authenticated in Deezer with a valid Deezer Token
     * If yes, return arguments for the Home view
     * Else, return the login view
     * @param Request $request
     * @param Response $response
     * @return array arguments
     */
    private function CheckDeezerSession(Request $request, Response $response){
        
    }

    /**
     * Return the "Home" view 
     * @param Request $request
     * @param Response $response
     * @return HTML
     */
    public function home(Request $request, Response $response) {
        if (!isset($_SESSION['dzapi'])) {
            $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "HomeController.php(home) Creating a new Deezer API class instance");
            $_SESSION['dzapi'] = serialize(new \App\Deezer\DZApi());
        }
       $arguments['deezerauthurl'] = unserialize($_SESSION['dzapi'])->getAuthUrl(getenv("SITEURL") . "/deezer/auth");
        $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "HomeController.php(CheckDeezerSession) Deezer Auth URL is : " . $arguments['deezerauthurl']);
        $arguments['deezerauthenticated'] = 0;
        // Check if we have a valid session token
        if (isset($_SESSION['deezer_token'])) {
            //Check if the token has not expired
            if ($_SESSION['deezer_token_expires'] > time()) {
                $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "HomeController.php(CheckDeezerSession)Session token stored in Session : " . $_SESSION['deezer_token']);
                $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "HomeController.php(CheckDeezerSession)Session token stored in class : " . unserialize($_SESSION['dzapi'])->getSToken());
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
                $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "HomeController.php(CheckDeezerSession)Creating a new Deezer API class instance");
                $_SESSION['dzapi'] = serialize(new \App\Deezer\DZApi());
                return $this->view->render($response, 'home_logintodeezer.twig', $arguments);
            }
         } else {
            return $this->view->render($response, 'home_logintodeezer.twig', $arguments);
        }
        

        $arguments['fileuploaded'] = false;


        if (isset($_SESSION['Library'])) {
            $arguments['fileuploaded'] = true;
            $arguments['playlists'] = unserialize($_SESSION["Library"])->getPlaylists();
        } else {
            $arguments['fileuploadederror'] = false;
            $Status=$request->getParam('Status');
            //No file is uploaded
            if (strcmp($Status, "FileError")==0) {
                $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "HomeController.php(home) File error - ".$Status);
                $arguments['fileuploadederror'] = true;
                $arguments['fileuploadederrormessage']="File upload error. This file is not a clean iTunes Library file";
            }
            if (strcmp($Status, "NoFile")==0) {
                $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "HomeController.php(home) No File - ".$Status);
                $arguments['fileuploadederror'] = true;
                $arguments['fileuploadederrormessage']="Please upload a file";
            }
            return $this->view->render($response, 'home_loadfile.twig', $arguments);
        }

        return $this->view->render($response, 'home.twig', $arguments);
    }
    
    
    public function getWaitingIcons(Request $request, Response $response) {
        return $this->view->render($response, 'waiting.twig');
    }

}
