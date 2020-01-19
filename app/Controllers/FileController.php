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
     * @param Request $request
     * @param Response $response
     * @return HTML
     */
    public function upload(Request $request, Response $response) {
        $lib = new iTunesLibrary();
        $files = $request->getUploadedFiles();
        $newfile = $files['file'];
        if ($newfile != null) {
            try {
                $lib->loadXML($newfile->getStream());
                $_SESSION['Library'] = serialize($lib);
            } catch (\Exception $e) {
                return $this->response
                                ->withStatus(303)
                                ->withHeader('Location', $this->router->pathFor('home'))
                                ->withHeader('Status', 'File not readable');
            }
        } else {
            return $this->response
                            ->withStatus(303)
                            ->withHeader('Location', $this->router->pathFor('home'))
                            ->withHeader('Status', 'File not readable');
        }

        if (!$lib->isInitialized()) {
            return $this->response
                            ->withStatus(303)
                            ->withHeader('Location', $this->router->pathFor('home'))
                            ->withHeader('Status', 'NOK');
        }
        return $this->response
                        ->withStatus(303)
                        ->withHeader('Location', $this->router->pathFor('home'))
                        ->withHeader('Status', 'OK')
                        ->withHeader('Tracks', $lib->countTracks())
                        ->withHeader('Playlists', $lib->countPlaylists());
    }

}
