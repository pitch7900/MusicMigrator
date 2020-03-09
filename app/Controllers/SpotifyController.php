<?php

namespace App\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * Description of DeezerController
 *
 * @author pierre
 */
class SpotifyController extends Controller {

    private $log;

    public function __construct($container) {

        parent::__construct($container);
        $this->log = new Logger('SpotifyController.php');
        $this->log->pushHandler(new StreamHandler(__DIR__.'/../../logs/debug.log', Logger::DEBUG));
        if (!isset($_SESSION['spotifyapi'])) {
            $_SESSION['spotifyapi'] = serialize(new \App\MusicSources\SpotifyApi());
        }
    }

    /**
     * Search on spotify
     * @param Request $request
     * @param Response $response
     * @param type $args
     */
    public function postSearch(Request $request, Response $response, $args) {
        session_write_close();
        $trackid = urlencode($request->getParsedBody()['trackid']);
        $artist = urlencode($request->getParsedBody()['artist']);
        $album = urlencode($request->getParsedBody()['album']);
        $song = urlencode($request->getParsedBody()['song']);
        $duration = urlencode($request->getParsedBody()['duration']);

        if (isset($_SESSION['spotifyapi'])) {
            $this->log->debug("(postSearch) Searching for : \n\t - " . $artist . "\n\t - " . $album . "\n\t - " . $song . "\n\t - " . $duration);
            $search = unserialize($_SESSION['spotifyapi'])->SearchIndividual($trackid, $artist, $album, $song, $duration);
            if (isset($search['info']['error'])) {
                return $this->response
                                ->withStatus(404)
                                ->withHeader('Error', 'Too many session')
                                ->withJson($search);
            }
            return $response->withJson($search);
        } else {
            return $this->response
                            ->withStatus(404)
                            ->withHeader('Error', 'Spotify Api not set');
        }
    }

    /**
     * Authenticate on Spotify
     * @param Request $request
     * @param Response $response
     * @return type
     */
    public function getAuth(Request $request, Response $response, $args) {
        $sourceordestination = $args['sourceordestination'];
        if ($sourceordestination == "destinations") {
            $_SESSION['destinations'] = "spotify";
        } else {
            $_SESSION['sources'] = "spotify";
        }
        $code = $request->getQueryParam('code');
        $_SESSION['spotifycode'] = $code;

        $this->log->debug("(getAuth) Auth Code recieved : " . $code);

        $token = unserialize($_SESSION['spotifyapi'])->apiconnect($code, getenv('SITEURL') . "/spotify/auth/" . $sourceordestination);
        $_SESSION['spotifytokne'] = $token;
        $this->log->debug("(getAuth) Auth Code recieved : " . $token);
        return $this->response
                        ->withStatus(303)
                        ->withHeader('Location', $this->router->pathFor('home'))
                        ->withHeader('Status', 'Authenticated on Spotify')
                        ->withHeader('AuthCode', $code)
                        ->withHeader('SourceorDestintation', $sourceordestination);
    }

    /**
     * Get user's information in Spotify
     * @param Request $request
     * @param Response $response
     * @return type
     */
    public function getAboutme(Request $request, Response $response) {
        $this->log->debug("(getAboutme)");
        if (!isset($_SESSION['spotifyapi'])) {
            return $this->response
                            ->withStatus(401)
                            ->withHeader('Error', 'Not logged in to Spotify');
        } else {
            $returninformation = unserialize($_SESSION['spotifyapi'])->getUserInformation();
            $this->log->debug("(getAboutme) Response is : ", var_export($returninformation, true));
            return $response->withJson($returninformation);
        }
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
        $arguments['playlist'] = unserialize($_SESSION["spotifyapi"])->getPlaylistItems($playlistid);
        $arguments['playlistname'] = unserialize($_SESSION["spotifyapi"])->getPlaylistName($playlistid);
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
        $this->log->debug("(postCreatePlaylist)recieved query to create a playlist :". $playlistname . " - " . $playlistpublic);
        if (!isset($_SESSION['spotifyapi'])) {
            return $this->response
                            ->withStatus(401)
                            ->withHeader('Error', 'Not logged in to Spotify');
        } else {
            return $response->withJson(unserialize($_SESSION['spotifyapi'])->CreatePlaylist($playlistname, $playlistpublic));
        }
    }
    
    /**
     * Return a playlist information in JSON format
     * @param Request $request
     * @param Response $response
     */
    public function getPlaylistInfo(Request $request, Response $response,$args) {
        $playlistid = $args['playlistid'];
        return $response->withJson(unserialize($_SESSION['spotifyapi'])->GetPlaylistInfo($playlistid));
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
        $this->log->debug("(postPlaylistAddSongs)recieved query to add to a playlist :" . $playlistid . "\n\t" . var_export($tracklist, true));
        return $response->withJson(unserialize($_SESSION['spotifyapi'])->AddTracksToPlaylist($playlistid, $tracklist));
    }

}
