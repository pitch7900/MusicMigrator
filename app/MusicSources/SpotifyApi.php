<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\MusicSources;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use \hamburgscleanest\GuzzleAdvancedThrottle as GuzzleAdvancedThrottle;
use GuzzleHttp\RequestOptions;

/**
 * Description of SpotifyApi
 *
 * @author pierre
 */
class SpotifyApi {

    private $log;
    private $_sAuthUrl = "https://accounts.spotify.com";
    private $_sApiUrl = "https://api.spotify.com";
    private $ThrottlerRules;
    private $ThrottlerStack;
    private $initialized;
    private $sAPIKey;
    private $sAPISecretKey;

    public function __construct() {
        $this->initializeLogger();


        $this->sAPIKey = getenv('SPOTIFY_APIKEY');
        $this->sAPISecretKey = getenv('SPOTIFY_APISECRETKEY');
        $this->log->debug("(__contruct) New SpotifyApi Constructor called\n\t" .
                getenv('SPOTIFY_APIKEY') . "\n\t" .
                getenv('SPOTIFY_APISECRETKEY')
        );
        $this->initiateThrotller();
    }

    public function __sleep() {
        return array('api', 'session', 'initialized', 'ThrottlerRules', 'ThrottlerStack', 'log', 'sAPIKey', 'sAPISecretKey', 'sToken');
    }

    private function initializeLogger() {
        if ($this->log == null) {
            $this->log = new Logger('SpotifyApi.php');
            $this->log->pushHandler(new StreamHandler(__DIR__ . '/../../logs/debug.log', Logger::DEBUG));
        }
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
        if (isset($_SESSION['spotifytoken'])) {
            $headers = [
                'Authorization' => 'Bearer ' . $this->getSToken(),
                'Accept' => 'application/json',
            ];
        }
        do {
            try {
                $this->log->debug("(sendRequest) Spotify request recieved : " . $sUrl);
                $this->log->debug("(sendRequest) Spotify request Auth Headers are : " . var_export($headers, true));

                $response = $client->get($sUrl, [
                    'headers' => $headers,
//                    'debug' => true
                ]);
                $output = $response->getBody()->getContents();
                if ($response->getStatusCode() == 429) {
                    $this->log->info("(sendRequest) Too Many request throwing exception " . $response->getStatusCode() . "\n\t" . $output);
                    throw new \Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException("Too many request to Spotify");
                }

                $this->log->debug("(sendRequest) Body : " . var_export($output, true));
                $RequestToBeDone = false;
            } catch (\Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException $e) {
                $this->log->info("(sendRequest) Too many requests. Waiting 1 second");
                sleep(1);
            }
        } while ($RequestToBeDone);


        if ($output === false) {
            $this->log->error("(sendRequest) Error curl : " . curl_error($c), E_USER_WARNING);
            trigger_error('Erreur curl : ' . curl_error($c), E_USER_WARNING);
        } else {
            curl_close($c);
            return json_decode($output, true);
        }
    }

    /**
     * This method return the URL to call for the authentication
     *
     * @param string $sRedirectUrl 
     * @param array $options 
     * @return string
     */
    public function getAuthUrl($sRedirectUrl = null, $options = ['scope' => ['user-read-email', 'user-read-private', 'user-library-read', 'playlist-modify-public']]) {
        $this->log->debug("(getAuthUrl) : 1- $sRedirectUrl \n\t" . var_export($options, true));
        $AuthURL = $this->_sAuthUrl . "/authorize?client_id=" . getenv('SPOTIFY_APIKEY') . "&redirect_uri=" . urlencode($sRedirectUrl) . "&response_type=code&scope=" . urlencode(implode(" ", $options['scope']));
        $this->log->debug("(getAuthUrl) Authentication URL : $AuthURL");
        return $AuthURL;
    }

    /**
     * This method will connect and get the token after authorization code is get
     * https://developer.spotify.com/documentation/general/guides/authorization-guide/#authorization-code-flow
     * @param string $sCode 
     * @return string
     */
    public function apiconnect($sCode, $sURL) {
        $this->ThrottlerStack = new \GuzzleHttp\HandlerStack();
        $this->ThrottlerStack->setHandler(new \GuzzleHttp\Handler\CurlHandler());

        $throttle = new GuzzleAdvancedThrottle\Middleware\ThrottleMiddleware($this->ThrottlerRules);

        $this->ThrottlerStack->push($throttle());

        $client = new \GuzzleHttp\Client(['base_uri' => $this->_sAuthUrl, 'handler' => $this->ThrottlerStack]);
        $RequestToBeDone = true;
        $sUrl = $this->_sAuthUrl . "/api/token";

        $payload = base64_encode(getenv('SPOTIFY_APIKEY') . ':' . getenv('SPOTIFY_APISECRETKEY'));

        $headers = [
            'Authorization' => 'Basic ' . $payload,
        ];


        $parameters = [
            'code' => $sCode,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $sURL,
        ];

        do {
            try {
                $this->log->debug("(apiconnect) Spotify Token Request on : " . $sUrl);
                $this->log->debug("(apiconnect) Spotify Token Request Headers : " . var_export($headers, true));
                $this->log->debug("(apiconnect) Spotify Token Request Parameters : " . var_export($parameters, true));
                $response = $client->request('POST', $sUrl, [
                    RequestOptions::FORM_PARAMS => $parameters,
                    RequestOptions::HEADERS => $headers
                ]);
                $this->log->debug("(apiconnect) : " . var_export($response, true));
                $output = $response->getBody();
                $RequestToBeDone = false;
            } catch (\Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException $e) {
                $this->log->debug("(apiconnect) Too many requests. Waiting 1 second");
                sleep(1);
            }
        } while ($RequestToBeDone);


        if ($output === false) {
            $this->log->debug("(apiconnect) Error curl : " . curl_error($c), E_USER_WARNING);
            trigger_error('Erreur curl : ' . curl_error($c), E_USER_WARNING);
            return false;
        } else {
            curl_close($c);
            $_SESSION['spotifytoken'] = json_decode($output, true)['access_token'];
            $this->log->debug("(apiconnect) Token Bearer : Bearer " . $_SESSION['spotifytoken']);
            $_SESSION['spotifyexpiretoken'] = time() + json_decode($output, true)['expires_in'];
            $_SESSION['spotifyrefreshtoken'] = json_decode($output, true)['refresh_token'];
            return json_decode($output, true)['access_token'];
        }
    }

    /**
     * Return an array with currently logged in user information
     * @return array
     */
    public function getUserInformation() {
        if (isset($_SESSION['spotifyapi'])) {

            $this->log->debug("(getUserInformation) Token : " . $this->getSToken());
            return $this->sendRequest($this->_sApiUrl . '/v1/me');
        } else {
            return null;
        }
    }

    /**
     * Return an array with all playlist (name, id) for the current session
     * Do not send in this list, playlist the user is not the creator and automated playlist (loved tracks for example)
     * @return array
     */
    public function getUserPlaylists() {
        $playlists = $this->sendRequest("/v1/me/playlists");
        $filteredplaylists = array();
        foreach ($playlists['items'] as $playlist) {


            $output['folder'] = false;
            $output['count'] = $playlist['tracks']['total'];
            $output['title'] = $playlist['name'];
            $output['name'] = $playlist['name'];
            $output['id'] = $playlist['id'];
            array_push($filteredplaylists, $output);
        }
        $this->log->debug("(getUserPlaylists) Playlists : " . var_export($filteredplaylists, true));
        return $filteredplaylists;
    }

    /**
     * Return the session Token ID
     * @return type
     */
    public function getSToken() {
        return $_SESSION['spotifytoken'];
    }

    public function setSToken($token) {
        $_SESSION['spotifytoken'] = $token;
    }

    private function search_params($param) {
        $url = $this->_sApiUrl . '/v1/search?q=' . $param . '&type=track';
        $this->log->debug("(search_params) : " . $url);
        return $this->sendRequest($url);
    }

    private function FormatSearchRestults($rawdata) {
        $output = array();
        $output['accuracy'] = $rawdata['accuracy'];

        $output['total'] = $rawdata['tracks']['total'];
        $output['track']['id'] = $rawdata['tracks']['items'][0]['id'];
        $output['track']['name'] = $rawdata['tracks']['items'][0]['name'];
        $output['track']['link'] = $rawdata['tracks']['items'][0]['external_urls']['spotify'];
        $output['track']['duration'] = $rawdata['tracks']['items'][0]['duration_ms'] / 1000;
        $output['album']['name'] = $rawdata['tracks']['items'][0]['album']['name'];
        $output['album']['id'] = $rawdata['tracks']['items'][0]['album']['id'];
        $output['album']['link'] = $rawdata['tracks']['items'][0]['album']['external_urls']['spotify'];
        $output['album']['picture'] = $rawdata['tracks']['items'][0]['album']['images'][0]['url'];
        $output['artist']['name'] = $rawdata['tracks']['items'][0]['album']['artists'][0]['name'];
        $output['artist']['id'] = $rawdata['tracks']['items'][0]['album']['artists'][0]['id'];
        $output['artist']['link'] = $rawdata['tracks']['items'][0]['album']['artists'][0]['external_urls']['spotify'];
        return $output;
    }

    /**
     * Search for an individual song
     * Search is getting less accurate if the number of search results is zero
     * @param type $artist
     * @param type $album
     * @param type $track
     * @param type $duration
     * @return array
     * @throws \Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException
     */
    private function search($artist, $album, $track, $duration) {

        /* Set duration of the track to be more or less 10% of the duration passed */
        $dur_min = (int) ($duration / 1000 * 0.9);
        $dur_max = (int) ($duration / 1000 * 1.1);

        $artist = urlencode(urldecode($artist));
        $album = urlencode(urldecode($album));
        $track = urlencode(urldecode($track));

        /* Search for full informations */
        $param = $track . '%20artist:' . $artist . '%20album:' . $album . '';

        $output = $this->search_params($param);

        $output['accuracy'] = 6;
        $this->log->debug("(search) Output : " . var_export($output, true));
        $this->log->debug("(search) Total matches : " . $output['tracks']['total']);

        if (strcmp($output['tracks']['total'], '0') == 0) {
            $matches = array();
            preg_match_all('/(.*)\(.*\)| - .*/m', urldecode($track), $matches, PREG_SET_ORDER, 0);
            if (strlen($matches[0][1]) !== 0) {

                $this->log->debug("(search) Remove unecesssary chars from title : " . $track . "\n\t" . json_encode($matches));

                $track = urlencode($matches[0][1]);

                $param = 'artists:"' . $artist . '"name:"' . $track . '"album:"' . $album . '"';
                $this->log->debug("(search) Spotify Search query " . $param);
                $output = $this->search_params($param);
                $output['accuracy'] = 5;
            }
        }

        /* Search for artist, track and duration informations */
        if (strcmp($output['tracks']['total'], '0') == 0) {
            $param = $track . '%20artist:' . $artist;
            $output = $this->search_params($param);
            $output['accuracy'] = 4;
        }


        /* Search globally for the track name */
        if (strcmp($output['tracks']['total'], '0') == 0) {
            $param = '"' . $track . '"';
            $output = $this->search_params($param);
            $output['accuracy'] = 2;
        }

        /* Still nothing found, remove (...) data and "- .." data from title */
        if (strcmp($output['tracks']['total'], '0') == 0) {
            $matches = array();
            preg_match_all('/(.*)\(.*\)| - .*/m', urldecode($track), $matches, PREG_SET_ORDER, 0);
            if (strlen($matches[0][1]) !== 0) {
                $param = '"' . urlencode($matches[0][1]) . '"';
                $this->log->debug("(search) Remove unecesssary chars from title : " . $track . "\n\t" . json_encode($matches));
                $this->log->debug("(search) Spotify Search query " . $param);
                $output = $this->search_params($param);
                $output['accuracy'] = 1;
            }
        }

        if (strcmp($output['tracks']['total'], '0') == 0) {
            $output['accuracy'] = 0;
        }

        $output['params'] = $param;
        if (isset($output['error']) && $output['error']['code'] == 4) {
            throw new \Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException($output['error']['message']);
        }
        return $this->FormatSearchRestults($output);
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

        $this->log->debug("(SearchIndividual) searching for  TrackID : " . $trackid);
        $RequestToBeDone = true;
        do {
            try {
                $search_result = $this->search($artist, $album, $song, $duration);
                $RequestToBeDone = false;
            } catch (\Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException $e) {
                $this->log->debug("(SearchIndividual) Too many requests. Waiting 1 second");
                sleep(1);
            }
        } while ($RequestToBeDone);
        $returns = ['trackid' => $trackid, 'accuracy' => $search_result['accuracy'], 'app_id' => $search_result['app_id'], 'info' => $search_result];
        return $returns;
    }

    /**
     * Create a new Playlist
     * @param string $name
     * @param string $public
     * @return string playlist id
     */
    public function CreatePlaylist($name, $public) {

        $name = str_replace('+', ' ', $name);
        if (strcmp($public, "public") == 0) {
            $public = "true";
        } else {
            $public = "false";
        }
        $this->log->debug("(CreatePlaylist) Spotify PlaylistCreation recieved: " . $name
                . "\n\tis public : " . $public
        );
        $this->ThrottlerStack = new \GuzzleHttp\HandlerStack();
        $this->ThrottlerStack->setHandler(new \GuzzleHttp\Handler\CurlHandler());

        $throttle = new GuzzleAdvancedThrottle\Middleware\ThrottleMiddleware($this->ThrottlerRules);

        $this->ThrottlerStack->push($throttle());

        $client = new \GuzzleHttp\Client(['base_uri' => $this->_sApiUrl, 'handler' => $this->ThrottlerStack]);

        $userid = $this->getUserInformation()['id'];
        $this->log->debug("(CreatePlaylist) UserID Is : " . $userid);
        $sUrl = $this->_sApiUrl . "/v1/users/" . $userid . "/playlists";
        $this->log->debug("(CreatePlaylist) URL : " . $sUrl);

        if (isset($_SESSION['spotifytoken'])) {
            $headers = [
                'Authorization' => 'Bearer ' . $this->getSToken(),
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ];
        }
        $json = json_encode(['name' => $name, 'public' => $public]);
        $this->log->debug("(CreatePlaylist) Body : " . $json);
        $response = $client->post($sUrl, [
            \GuzzleHttp\RequestOptions::HEADERS => $headers,
            \GuzzleHttp\RequestOptions::BODY => $json
        ]);
        $response->getBody()->rewind();
        $output = $response->getBody()->getContents();
        $this->log->debug("(CreatePlaylist) Response is  : " . $output);
        if ($output === false) {
            $this->log->debug("(CreatePlaylist) Error curl : " . curl_error($c), E_USER_WARNING);
            trigger_error('Erreur curl : ' . curl_error($c), E_USER_WARNING);
        } else {
            curl_close($c);
            return json_decode($output);
        }
    }

    /**
     * Add TracksID to a playlist
     * @param int $playlistid
     * @param array $tracklist
     */
    public function AddTracksToPlaylist($playlistid, $tracklist) {

        $this->log->debug("(AddTracksToPlaylist) Add to Playlist " . $playlistid . " Tracks : " . var_export($tracklist, true));

        $this->ThrottlerStack = new \GuzzleHttp\HandlerStack();
        $this->ThrottlerStack->setHandler(new \GuzzleHttp\Handler\CurlHandler());

        $throttle = new GuzzleAdvancedThrottle\Middleware\ThrottleMiddleware($this->ThrottlerRules);

        $this->ThrottlerStack->push($throttle());

        $client = new \GuzzleHttp\Client(['base_uri' => $this->_sApiUrl, 'handler' => $this->ThrottlerStack]);

        if (isset($_SESSION['spotifytoken'])) {
            $headers = [
                'Authorization' => 'Bearer ' . $this->getSToken(),
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ];
        }
        $counter = 0;
        $jsonuri = array();
        foreach ($tracklist as $track) {
            //Don't add if a spotify track ID is not found
            if (strlen($track) != 0) {
                array_push($jsonuri, "spotify:track:" . $track);
            }
            $RequestToBeDone = true;
            if ($counter == count($tracklist) - 1 || $counter % 99 == 0) {
                do {
                    try {
                        $sUrl = $this->_sApiUrl . "/v1/playlists/" . $playlistid . "/tracks";
                        $json = json_encode(['uris' => $jsonuri]);
                        $this->log->debug("(AddTracksToPlaylist) URL : " . $sUrl);
                        $this->log->debug("(AddTracksToPlaylist) URL : " . var_export($json, true));
                        $response = $client->post($sUrl, [
                            \GuzzleHttp\RequestOptions::HEADERS => $headers,
                            \GuzzleHttp\RequestOptions::BODY => $json
                        ]);
                        $RequestToBeDone = false;
                        $output = $response->getBody()->getContents();
//                        $this->log->debug("(AddTracksToPlaylist) Response is  : " . $output);
                    } catch (\Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException $e) {
                        $this->log->debug("(AddTracksToPlaylist) Response is  : " . $output);
                        $this->log->debug("(AddTracksToPlaylist) Too many requests. Waiting 1 second ");
                        sleep(1);
                    }
                } while ($RequestToBeDone);
                $jsonuri = array();
            }
            $counter++;
        }
    }

    /**
     * Return the name of a playlist for a given PlaylistID
     * @param int $playlistID
     * @return string
     */
    public function getPlaylistName($playlistID) {
        return $this->sendRequest("/v1/playlists/" . $playlistID . "?fields=fields%3Ddescription")['name'];
    }

    private function PlaylistInfoFormat($rawdata) {

        $this->log->debug("(PlaylistInfoFormat) " . var_export($rawdata, true));
        $output['name'] = $rawdata['name'];
        $output['id'] = $rawdata['id'];
        $output['description'] = $rawdata['description'];
        $output['tracks'] = $rawdata['tracks']['total'];
        $output['image'] = $rawdata['images'][0]['url'];
        return $output;
    }

    public function GetPlaylistInfo($playlistID) {
        return $this->PlaylistInfoFormat($this->sendRequest("/v1/playlists/" . $playlistID));
    }

    /**
     * Return the playlist array for a given PlaylistID
     * @param int $playlistID
     * @return array
     */
    public function getPlaylist($playlistID) {
        return $this->getPlaylistItems($playlistID);
    }

    /**
     * Return all tracks for a given PlaylistID
     * 
     * @param type $playlistID
     * @return array of array ["ID","Artist","Album","Song","Time" in ms,"Track","TotalTracks"]
     */
    public function getPlaylistItems($playlistID) {

        $numberoftracks = $this->sendRequest("/v1/playlists/" . $playlistID . "/tracks?fields=total%2Climit")['total'];
        $list = array();
        //Loop because of Spotify Api limitation https://developer.spotify.com/documentation/web-api/reference/playlists/get-playlists-tracks/
        for ($i = 0; $i < ($numberoftracks / 100); $i++) {
            $playlist = $this->sendRequest("/v1/playlists/" . $playlistID . "/tracks?limit=100&offset=" . $i * 100);

            $this->log->debug("(getPlaylistItems) query sent: /v1/playlists/" . $playlistID . "/tracks?limit=100&offset=" . $i * 100);
            foreach ($playlist['items'] as $item) {
                array_push($list, ["ID" => $item["track"]["id"],
                    "Artist" => $item["track"]["artists"][0]["name"],
                    "Album" => $item["track"]["album"]["name"],
                    "Song" => $item["track"]["name"],
                    "Time" => intval($item["track"]["duration_ms"]),
                    "Track" => $item["track"]["track_number"],
                    "TotalTracks" => $item["track"]["album"]["total_tracks"]
                ]);
            }
        }
        return $list;
    }

    /**
     * Return an array with all playlists information (structured with folders of first level)
     * @return array
     */
    public function getPlaylists() {
        return $this->getUserPlaylists();
    }

}
