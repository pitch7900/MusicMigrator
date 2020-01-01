<?php

namespace App\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use App\Deezer\ITunesLibrary as iTunesLibrary;

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
        $files = $request->getUploadedFiles();
        //var_dump($files);
        $newfile = $files['file'];
        if ($newfile != null) {

            $library = new iTunesLibrary($newfile->getStream());
            echo "<p>Tracks : " . $library->countTracks() . "</p>\n";
            echo "<p>Playlists : " . $library->countPlaylists() . "</p>\n";
//            echo count($library->getPlaylists());
            $library->getPlaylists();
            
//            foreach ($library->library_array["Playlists"] as $playlist) {
//                echo $playlist["Name"]." \n ";
//                print_r($playlist["Name"]);
//            }
        }
    }

}
