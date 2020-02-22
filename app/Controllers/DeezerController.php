<?php

namespace App\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use App\MusicSources\DeezerApi as DeezerApi;
use \App\Utils\Logs as Logs;

/**
 * Description of DeezerController
 *
 * @author pierre
 */
class DeezerController extends Controller {

    private $logs;

    public function __construct($container) {

        parent::__construct($container);
        $this->logs = new Logs();
    }
    /**
     * get the post query for searching for a track on deezer
     * @param Request $request
     * @param Response $response
     * @return type
     */
    public function postSearch(Request $request, Response $response) {
        session_write_close();
        $trackid = urlencode($request->getParsedBody()['trackid']);
        $artist = urlencode($request->getParsedBody()['artist']);
        $album = urlencode($request->getParsedBody()['album']);
        $song = urlencode($request->getParsedBody()['song']);
        $duration = urlencode($request->getParsedBody()['duration']);

        $dz = new DeezerApi();
        $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "DeezerController.php(postSearch) Searching for : \n\t - " . $artist . "\n\t - " . $album . "\n\t - " . $song . "\n\t - " . $duration);
        $search = $dz->SearchIndividual($trackid, $artist, $album, $song, $duration);
        if (isset($search['info']['error'])) {
            return $this->response
                            ->withStatus(404)
                            ->withHeader('Error', 'Too many session')
                            ->withJson($search);
        }
        
        return $response->withJson($search);
    }
    /**
     * Get user's information in Deezer
     * @param Request $request
     * @param Response $response
     * @return type
     */
    public function getAboutme(Request $request, Response $response) {
        if (!isset($_SESSION['deezerapi']) || isset(unserialize($_SESSION['deezerapi'])->getUserInformation()['error'])) {
            return $this->response
                            ->withStatus(401)
                            ->withHeader('Error', 'Not logged in to Deezer');
        } else {
            $returninformation = unserialize($_SESSION['deezerapi'])->getUserInformation();
            $returninformation["expiration_time"]=$_SESSION['deezer_token_expires'];
            return $response->withJson($returninformation);
        }
    }
    /**
     * Return a json array with all user's playlist in Deezer
     * @param Request $request
     * @param Response $response
     * @return type
     */
    public function getMyPlaylists(Request $request, Response $response) {
        if (!isset($_SESSION['deezerapi'])) {
            return $this->response
                            ->withStatus(401)
                            ->withHeader('Error', 'Not logged in to Deezer');
        } else {
            return $response->withJson(unserialize($_SESSION['deezerapi'])->getUserPlaylists());
        }
    }
    /**
     * Authenticate on Deezer
     * @param Request $request
     * @param Response $response
     * @return type
     */
    public function getAuth(Request $request, Response $response,$args) {
        $sourceordestination = $args['sourceordestination'];
        if ($sourceordestination=="destinations") {
            $_SESSION['destinations']="deezer"; 
        } else {
            $_SESSION['sources']="deezer"; 
        }
        if (!isset($_SESSION['deezerapi'])) {
            $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "DeezerController.php(getAuth) Creating a new Deezer API class instance");
            $_SESSION['deezerapi'] = serialize(new \App\MusicSources\DeezerApi());
        }



        $code = $request->getQueryParam('code');
        $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "DeezerController.php(getAuth) Deezer code recieved");
        $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "DeezerController.php(getAuth) Generating app token");
        $token = unserialize($_SESSION['deezerapi'])->apiconnect($code);
        $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "DeezerController.php(getAuth) Token set to : " . $token);
        $_SESSION['deezer_token'] = $token;
        return $this->response
                        ->withStatus(303)
                        ->withHeader('Location', $this->router->pathFor('home'))
                        ->withHeader('Status', 'Authenticated on deezer');
    }
    /**
     * Search for a full list of track in Deezer.
     * Return a Json with track informations found
     * @param Request $request
     * @param Response $response
     * @return type
     */
    public function postSearchList(Request $request, Response $response) {

        $tracklist = json_decode($request->getParsedBody()['tracklist']);
        if (!isset($_SESSION['deezerapi'])) {
            $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "DeezerController.php(postSearchList) Creating a new Deezer API class instance");
            $_SESSION['deezerapi'] = serialize(new \App\MusicSources\DeezerApi());
        }

        return $response->withJson(unserialize($_SESSION['deezerapi'])->SearchList($tracklist));
    }
    /**
     * Return the List of track to find on Deezer
     * This list is created by the function postSearchList
     * @param Request $request
     * @param Response $response
     * @return type
     */
    public function getSearchList(Request $request, Response $response) {
        if (!isset($_SESSION['deezerapi'])) {
            return $this->response
                            ->withStatus(412)
                            ->withHeader('Error', 'Session not initialized');
        } else {
            return $response->withJson(unserialize($_SESSION['deezersearchlist']));
        }
    }
    /**
     * Create a playlist in Deezer
     * Return a Json with the Playlist information once created
     * @param Request $request
     * @param Response $response
     * @return type
     */
    public function postCreatePlaylist(Request $request, Response $response) {
        $playlistname = urlencode($request->getParsedBody()['name']);
        $playlistpublic = urlencode($request->getParsedBody()['public']);
        $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "DeezerController.php(postCreatePlaylist)recieved query to create a playlist :", $playlistname . " - " . $playlistpublic);
        if (!isset($_SESSION['deezerapi'])) {
            return $this->response
                            ->withStatus(401)
                            ->withHeader('Error', 'Not logged in to Deezer');
        } else {
            return $response->withJson(unserialize($_SESSION['deezerapi'])->CreatePlaylist($playlistname, $playlistpublic));
        }
    }
    /**
     * Add tracks to a given Deezer PlaylistID
     * @param Request $request
     * @param Response $response
     * @param type $args
     * @return type
     */
    public function postPlaylistAddSongs(Request $request, Response $response, $args) {

        $playlistid = $args['playlistid'];

        $tracklist = json_decode($request->getParsedBody()['tracklist']);
        $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "DeezerController.php(postPlaylistAddSongs)recieved query to add to a playlist :" . $playlistid . "\n\t" . var_export($tracklist, true));
        return $response->withJson(unserialize($_SESSION['deezerapi'])->AddTracksToPlaylist($playlistid, $tracklist));
    }
    /**
     * Redirect to the songs.twig page. Display all songs for a given PlaylistID
     * @param Request $request
     * @param Response $response
     * @param type $args
     * @return type
     */
    public function getPlaylistItems(Request $request, Response $response, $args) {
        $playlistid = $args['playlistid'];
        $arguments['playlist'] = unserialize($_SESSION["deezerapi"])->getPlaylistItems($playlistid);
        $arguments['playlistname'] = unserialize($_SESSION["deezerapi"])->getPlaylistName($playlistid);
        $arguments['destination'] = $_SESSION['destinations'];

        switch ($_SESSION['destinations']) {
            case "deezer":
                $arguments['destinationauthenticated'] = true;
                $arguments['destinationplaylists'] = unserialize($_SESSION['deezerapi'])->getUserPlaylists();
                break;
            case "spotify":
                $arguments['destinationauthenticated'] = true;
                $arguments['destinationplaylists'] = unserialize($_SESSION['spotifyapi'])->getUserPlaylists();
                break;
            default:
                $arguments['destinationauthenticated'] = false;
                break;
        }

        return $this->view->render($response, 'songs.twig', $arguments);
        
    }
}
