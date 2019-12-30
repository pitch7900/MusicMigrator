<?php

namespace App\Controllers\Auth;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use App\Controllers\Controller;
use Respect\Validation\Validator as v;

/**
 * AuthController
 *
 * @author    Haven Shen <havenshen@gmail.com>
 * @copyright    Copyright (c) Haven Shen
 */
class AuthController extends Controller {

    public function getSignOut(Request $request, Response $response) {
        $this->auth->logout();
        return $response->withRedirect($this->router->pathFor('home'));
    }

    public function getSignIn(Request $request, Response $response) {
        return $this->view->render($response, 'auth/signin.twig');
    }

    public function postSignIn(Request $request, Response $response) {
//        $log=new logs();
        $auth = $this->auth->attempt(
                strtolower($request->getParam('login')), $request->getParam('password')
        );
      //  echo $request->getParam('login'), $request->getParam('password');
//        $log->write("Warning", "logfile", "debug.log", "Login attempd for ".$request->getParam('login') ." - " . $request->getParam('password'));
        
        if (!$auth) {
            $this->flash->addMessage('error', 'Could not sign you in with those details');
            return $response->withRedirect($this->router->pathFor('auth.signin'));
        }

        return $response->withRedirect($this->router->pathFor('home'));
    }

    public function getSignUp(Request $request, Response $response) {
        return $this->view->render($response, 'auth/signup.twig');
    }

    public function postSignUp(Request $request, Response $response) {

        $validation = $this->validator->validate($request, [
            'email' => v::noWhitespace()->notEmpty()->email()->emailAvailable(),
            'name' => v::noWhitespace()->notEmpty()->alpha(),
            'password' => v::noWhitespace()->notEmpty(),
        ]);

        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('auth.signup'));
        }

        $user = User::create([
                    'email' => $request->getParam('email'),
                    'name' => $request->getParam('name'),
                    'password' => password_hash($request->getParam('password'), PASSWORD_DEFAULT),
        ]);

        $this->flash->addMessage('info', 'You have been signed up');

        $this->auth->attempt($user->email, $request->getParam('password'));

        return $response->withRedirect($this->router->pathFor('home'));
    }

}
