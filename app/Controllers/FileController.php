<?php

namespace App\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use App\MusicSources\ITunesLibrary as iTunesLibrary;

class FileController extends Controller {

    public function __construct($container) {

        parent::__construct($container);
    }

    /**
     * Get a file for uploading and initialize the $_SESSION["itunesapi"] class
     * Return in any case a 303 redirect with a Status argument if needed.
     * @param Request $request
     * @param Response $response
     * @return HTML
     */
    public function upload(Request $request, Response $response) {
        $lib = new iTunesLibrary();
        $files = $request->getUploadedFiles();
        $newfile = $files['file'];
        unset($_SESSION['itunesapi']);
        if ($newfile != null) {
            try {
                $lib->loadXML($newfile->getStream());
                $_SESSION["itunesapi"] = serialize($lib);
            } catch (\Exception $e) {
                return $this->response
                                ->withStatus(303)
                                ->withHeader('Location', $this->router->pathFor('home') . "?Status=NoFile")
                                ->withHeader('Status', 'File not readable');
            }
        } else {
            return $this->response
                            ->withStatus(303)
                            ->withHeader('Location', $this->router->pathFor('home') . "?Status=FileError")
                            ->withHeader('Status', 'File not readable');
        }

        if (!$lib->isInitialized()) {
            unset($_SESSION["itunesapi"]);
            return $this->response
                            ->withStatus(303)
                            ->withHeader('Location', $this->router->pathFor('home') . "?Status=FileError")
                            ->withHeader('Status', 'NOK');
        }
        if ($lib->countTracks() == 0 || $lib->countPlaylists() == 0) {
            unset($_SESSION["itunesapi"]);
            return $this->response
                            ->withStatus(303)
                            ->withHeader('Location', $this->router->pathFor('home') . "?Status=FileError")
                            ->withHeader('Status', 'NOK');
        }
        $_SESSION['sources']="itunes";
        return $this->response
                        ->withStatus(303)
                        ->withHeader('Location', $this->router->pathFor('home'))
                        ->withHeader('Status', 'OK')
                        ->withHeader('Tracks', $lib->countTracks())
                        ->withHeader('Playlists', $lib->countPlaylists());
    }

}
