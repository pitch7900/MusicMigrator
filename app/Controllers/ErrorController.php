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

    public function postWriteConfig(Request $request, Response $response) {
        $dzapi = utf8_encode($request->getParam('dzapi'));
        $dzsecret = utf8_encode($request->getParam('dzsecret'));
        $sitename = utf8_encode($request->getParam('sitename'));

        echo "Config should be : $dzapi $dzsecret $sitename";
        if (!is_dir(__DIR__ . '/../../config')) {
            if (!mkdir(__DIR__ . '/../../config', 0777, true)) {
                die("Can't write folder");
            }
        }
        $current = 'SITEURL="'.$sitename.'"' . "\n" .
                'DZAPIKEY="'.$dzapi.'"' . "\n" .
                'DZAPI_SECRETKEY="'.$dzsecret.'"';

        if (!file_put_contents(__DIR__ . '/../../config/.env', $current)) {
            die("Can't write configuration file");
        }
        //return $this->response->withStatus(303)->withHeader('Location', '/');
    }

}
