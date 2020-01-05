<?php

namespace App\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class FileController extends Controller {

    public function __construct($container) {

        parent::__construct($container);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return HTML
     */
    public function upload(Request $request, Response $response) {
//        var_dump($request);

        $files = $request->getUploadedFiles();
//        var_dump($files);
        $newfile = $files['file'];
//        echo $newfile->getStream();

        if ($newfile != null) {


                file_put_contents(__DIR__."/../../libfiles/".session_id().".xml",$newfile->getStream());
            $_SESSION["Library"]->loadXML($newfile->getStream());
     
//            echo "<p>Tracks : " . $_SESSION["Library"]->countTracks() . "</p>\n";
//            echo "<p>Playlists : " . $_SESSION["Library"]->countPlaylists() . "</p>\n";
//            $arguments['playlists'] = $library->getPlaylists();
        }


        if (!$_SESSION["Library"]->isInitialized()) {
            return $this->response
                            ->withStatus(303)
                            ->withHeader('Location', $this->router->pathFor('home'))
                            ->withHeader('Status', 'NOK');
        }
       
        return $this->response
                        ->withStatus(303)
                        ->withHeader('Location', $this->router->pathFor('home'))
                        ->withHeader('Status', 'OK')
                        ->withHeader('debug', json_encode($_SESSION["Library"]))
                        ->withHeader('Tracks', $_SESSION["Library"]->countTracks())
                        ->withHeader('Playlists', $_SESSION["Library"]->countPlaylists());


//        return $this->view->render($response, $this->router->pathFor('home'), $arguments);
    }
    
    

}
