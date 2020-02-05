<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\MusicSources;

use \App\Utils\Logs as Logs;
use \hamburgscleanest\GuzzleAdvancedThrottle as GuzzleAdvancedThrottle;

/**
 * Description of SpotifyApi
 *
 * @author pierre
 */
class SpotifyApi {

    private $logs;
    private $session;
    private $ThrottlerRules;
    private $ThrottlerStack;
    private $initialized;
    private $api;

    public function __construct() {
        $this->logs = new Logs();

        $this->session = new \SpotifyWebAPI\Session(
                getenv('SPOTIFY_APIKEY'),
                getenv('SPOTIFY_APISECRETKEY'),
                getenv('SITEURL')."/spotify/auth"
        );


        $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "SpotifyApi.php(__contruct) New SpotifyApi Constructor called\n\t" .
                getenv('SPOTIFY_APIKEY') . "\n\t" .
                getenv('SPOTIFY_APISECRETKEY') . "\n\t" .
                getenv('SITEURL') . "\n\t"
        );

        $this->api = new \SpotifyWebAPI\SpotifyWebAPI();

        $this->initiateThrotller();
        $this->initialized = true;
    }

    private function initiateThrotller() {
        $this->ThrottlerRules = new GuzzleAdvancedThrottle\RequestLimitRuleset([
            $this->_sApiUrl => [
                [
                    'max_requests' => $this->_sApiMaxRequest,
                    'request_interval' => $this->_sApiRequestInterval
                ]
            ]
        ]);
    }

    /**
     * Return a true if this class is correctly initialized
     * @return boolean
     */
    public function isInitialized() {
        return $this->initialized;
    }

    /**
     * This method will be called to send a request
     *
     * @param string $sUrl 
     * @return void
     */
    public function sendRequest($sUrl) {
        $this->ThrottlerStack = new \GuzzleHttp\HandlerStack();
        $this->ThrottlerStack->setHandler(new \GuzzleHttp\Handler\CurlHandler());

        $throttle = new GuzzleAdvancedThrottle\Middleware\ThrottleMiddleware($this->ThrottlerRules);

        $this->ThrottlerStack->push($throttle());

        $client = new \GuzzleHttp\Client(['base_uri' => $this->_sApiUrl, 'handler' => $this->ThrottlerStack]);
        $RequestToBeDone = true;
        do {
            try {
                $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "SpotifyApi.php(sendRequest) Deezer request recieved : " . $sUrl);
                $response = $client->get($sUrl);
                $output = $response->getBody();
                $RequestToBeDone = false;
            } catch (\Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException $e) {
                $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "SpotifyApi.php(sendRequest) Too many requests. Waiting 1 second");
                sleep(1);
            }
        } while ($RequestToBeDone);


        if ($output === false) {
            $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "SpotifyApi.php(sendRequest) Error curl : " . curl_error($c), E_USER_WARNING);
            trigger_error('Erreur curl : ' . curl_error($c), E_USER_WARNING);
        } else {
            curl_close($c);
            return $output;
        }
    }

    /**
     * Search for an individual song
     * @param type $trackid
     * @param type $artist
     * @param type $album
     * @param type $song
     * @param type $duration
     * @return array with search results
     */
    public function SearchIndividual($trackid, $artist, $album, $song, $duration) {
        
    }

    /**
     * This method return the URL to call for the authentication
     *
     * @param string $sRedirectUrl 
     * @param array $aPerms 
     * @return string
     */
    public function getAuthUrl($sRedirectUrl = null, $options=null) {
        $options = [
            'scope' => [
                'user-read-email',
            ],
        ];
        $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "SpotifyApi.php(getAuthUrl)  \n\t" .var_export($options,true));
        return $this->session->getAuthorizeUrl($options);
    }

    /**
     * This method will connect and get the token
     *
     * @param string $sCode 
     * @return string
     */
    public function apiconnect($sCode) {
        
    }

    /**
     * Return an array with currently logged in user information
     * @return array
     */
    public function getUserInformation() {
        
    }

    /**
     * Return an array with all playlist (name, id) for the current session
     * Do not send in this list, playlist the user is not the creator and automated playlist (loved tracks for example)
     * @return array
     */
    public function getUserPlaylists() {
        
    }

    /**
     * Return the session Token ID
     * @return type
     */
    public function getSToken() {
        
    }

    /**
     * Create a new Playlist
     * @param string $name
     * @param string $public
     * @return string playlist id
     */
    public function CreatePlaylist($name, $public) {
        
    }

    /**
     * Add TracksID to a playlist
     * @param int $playlistid
     * @param array $tracklist
     */
    public function AddTracksToPlaylist($playlistid, $tracklist) {
        
    }

    /**
     * count the number of track
     * @return int
     */
    public function countTracks() {
        
    }

    /**
     * Count the number of playlists
     * @return int
     */
    public function countPlaylists() {
        
    }

    /**
     * Return an array for a given trackId
     * @param type $trackid
     * @return array
     */
    public function getTrack($trackid) {
        
    }

    /**
     * Return the name of a playlist for a given PlaylistID
     * @param int $playlistID
     * @return string
     */
    public function getPlaylistName($playlistID) {
        
    }

    /**
     * Return the playlist array for a given PlaylistID
     * @param int $playlistID
     * @return array
     */
    public function getPlaylist($playlistID) {
        
    }

    /**
     * Return all tracks for a given PlaylistID
     * @param type $playlistID
     * @return array
     */
    public function getPlaylistItems($playlistID) {
        
    }

    /**
     * Return an array with all playlists information (structured with folders of first level)
     * @return array
     */
    public function getPlaylists() {
        
    }

}
