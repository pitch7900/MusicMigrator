<?php

namespace App\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use App\Deezer\ITunesLibrary as  iTunesLibrary;

class FileController extends Controller {

    public function __construct($container) {
       
        parent::__construct($container);
    }

    /**
     * Return the "Home" view 
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

            $library = new iTunesLibrary();
//              $this->Library->loadXML($newfile->getStream());
            $library->loadXML($newfile->getStream());
//            echo "<p>Tracks : " . $library->countTracks() . "</p>\n";
//            echo "<p>Playlists : " . $library->countPlaylists() . "</p>\n";


            $arguments['playlists'] = $library->getPlaylists();
        }

        return $this->view->render($response, 'fileupload.twig', $arguments);
    }
    
}