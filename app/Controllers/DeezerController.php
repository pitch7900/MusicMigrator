<?php
namespace App\Controllers;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use App\Deezer\DZApi as DZApi;

/**
 * Description of DeezerController
 *
 * @author pierre
 */
class DeezerController extends Controller {

    public function __construct($container) {

        parent::__construct($container);
    }
    
    public function postSearch(Request $request, Response $response) {

        $artist = urlencode($request->getParsedBody()['artist']);
        $album = urlencode($request->getParsedBody()['album']);
        $song = urlencode($request->getParsedBody()['song']);
        
        $dz=new DZApi();
//        echo "Should Search : " .$artist." ".$album." ".$song;
        return $response->withJson($dz->search($artist, $album, $song));
    }

}
