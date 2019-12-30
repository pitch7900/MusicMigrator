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
        
        return $this->view->render($response, 'home.twig');
    }

    

}
