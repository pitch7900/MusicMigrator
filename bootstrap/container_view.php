<?php
/**
 * Views that should be added to app.php for slim framework
 */

$container['view'] = function($container) {
    $view = new \Slim\Views\Twig(__DIR__ . '/../resources/views', [
//        'cache' => '/tmp',
        'debug' => true,
    ]);

    $view->addExtension(new Slim\Views\TwigExtension(
            $container->router, $container->request->getUri()
    ));
    $view->addExtension(new Twig_Extension_Debug());


 
    return $view;
};