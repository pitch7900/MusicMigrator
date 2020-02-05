<?php

namespace App\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * Description of ErrorController
 *
 * @author pierre
 */
class ErrorController extends Controller {

    public function __construct($container) {
        parent::__construct($container);
    }

    /**
     * Return the "Config error file missing" view 
     * @param Request $request
     * @param Response $response
     * @return HTML
     */
    public function getConfigError(Request $request, Response $response) {
        return $this->view->render($response, 'configerror.twig');
    }
    /**
     * Write a config file .env with informations passed in the body
     * @param Request $request
     * @param Response $response
     */
    public function postWriteConfig(Request $request, Response $response) {
        $deezerapi = utf8_encode($request->getParam('dzapi'));
        $dzsecret = utf8_encode($request->getParam('dzsecret'));
        $sitename = utf8_encode($request->getParam('sitename'));
        $spotifyapi = utf8_encode($request->getParam('spotifyapi'));
        $spotifysecret = utf8_encode($request->getParam('spotifysecret'));
        
        echo "Config should be : $deezerapi $dzsecret $sitename $spotifyapi $spotifysecret";
        if (!is_dir(__DIR__ . '/../../config')) {
            if (!mkdir(__DIR__ . '/../../config', 0777, true)) {
                die("Can't write folder");
            }
        }
        $current = 'SITEURL="'.$sitename.'"' . "\n" .
                'DEEZER_APIKEY="'.$deezerapi.'"' . "\n" .
                'DEEZER_APISECRETKEY="'.$dzsecret.'"'. "\n" .
                'SPOTIFY_APIKEY="'.$spotifyapi.'"'. "\n" .
                'SPOTIFY_APISECRETKEY="'.$spotifysecret.'"';

        if (!file_put_contents(__DIR__ . '/../../config/.env', $current)) {
            die("Can't write configuration file");
        }
    }

}
