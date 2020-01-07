<?php

namespace App\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class HomeController extends Controller {

    public function __construct($container) {
        parent::__construct($container);
    }

    /**
     * Return the "Home" view 
     * @param Request $request
     * @param Response $response
     * @return HTML
     */
    public function home(Request $request, Response $response) {
//        $arguments['debugdata']=json_encode($_SESSION['Library']);
//        $filename=__DIR__."/../../libfiles/".session_id().".xml";
        $arguments['fileuploaded'] = false;
//        if (is_readable($filename)) {
        if (isset($_SESSION['Library'])) {
            $arguments['fileuploaded'] = true;

//            $Library= new \App\Deezer\ITunesLibrary();
//        unserialize($_SESSION["Library"])->loadXMLFile($filename);

            $arguments['playlists'] = unserialize($_SESSION["Library"])->getPlaylists();
        }

        return $this->view->render($response, 'home.twig', $arguments);
    }

}
