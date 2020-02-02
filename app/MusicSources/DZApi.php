<?php

namespace App\MusicSources;

use \App\Utils\Logs as Logs;
use \hamburgscleanest\GuzzleAdvancedThrottle as GuzzleAdvancedThrottle;

/**
 * This class will help you to interact with the Deezer API
 *
 * This is a really simple implementation and it will just help to bootstrap a project using the Deezer API.
 *
 * For more informations about the api please visit http://www.deezer.com/fr/developers/simpleapi
 *
 * @author Mathieu BUONOMO <mbuonomo@gmail.com>
 * @version 0.1
 */
class DZApi {

    private $logs;

    /**
     * This is your API key
     *
     * You have to fill this with your own key
     *
     * @var string
     */
    private $_sApiKey;

    /**
     * This is your Secret key
     *
     * You have to fill this with your own key
     *
     * @var string
     */
    private $_sSecretKey;

    /**
     * This token will be set during the oAuth process
     *
     * @var string
     */
    private $_sToken = null;

    /**
     * This is the url used to connect
     *
     * @var string
     */
    private $_sAuthUrl = "https://connect.deezer.com/oauth/";

    /**
     * This is the url to call the API
     *
     * @var string
     */
    private $_sApiUrl = "https://api.deezer.com";
    private $_sApiMaxRequest = "50";
    private $_sApiRequestInterval = "5";
    private $ThrottlerRules;
    private $ThrottlerStack;

    public function __construct() {

        $this->_sApiKey = getenv('DEEZER_APIKEY');
        $this->_sSecretKey = getenv('DEEZER_APISECRETKEY');
        $this->logs = new Logs();
        $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "DZApi.php(__contruct) New DZAPI Constructor called");
        $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "DZApi.php(__contruct) API key is : " . $this->_sApiKey);
        $this->initiateThrotller();
    }

    /**
     * Initialize Throttler with values set in the class
     */
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
     * This method will be called to send a request
     *
     * Really simple cUrl :)
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
                $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "DZApi.php(sendRequest) Deezer request recieved : " . $sUrl);
                $response = $client->get($sUrl);
                $output = $response->getBody();
                $RequestToBeDone = false;
            } catch (\Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException $e) {
                $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "DZApi.php(sendRequest) Too many requests. Waiting 1 second");
                sleep(1);
            }
        } while ($RequestToBeDone);


        if ($output === false) {
            $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "DZApi.php(sendRequest) Error curl : " . curl_error($c), E_USER_WARNING);
            trigger_error('Erreur curl : ' . curl_error($c), E_USER_WARNING);
        } else {
            curl_close($c);
            return $output;
        }
    }

    private function search_params($param) {
        $url = $this->_sApiUrl . '/search?q=' . $param;
        return json_decode($this->sendRequest($url), true);
    }

    public function search($artist, $album, $track, $duration) {
        /* Set duration of the track to be more or less 10% of the duration passed by itunes */
        $dur_min = (int) ($duration / 1000 * 0.9);
        $dur_max = (int) ($duration / 1000 * 1.1);

        /* Search for full informations */
        $param = 'artist:"' . $artist . '"track:"' . $track . '"album:"' . $album . '"' . 'dur_min:' . $dur_min . 'dur_max:' . $dur_max;

        $output = $this->search_params($param);
        $output['accuracy'] = 6;

        if (strcmp($output['total'], '0') == 0) {
            $matches = array();
            preg_match_all('/(.*)\(.*\)| - .*/m', urldecode($track), $matches, PREG_SET_ORDER, 0);
            if (strlen($matches[0][1]) !== 0) {

                $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "DZApi.php(search) Remove unecesssary chars from title : " . $track . "\n\t" . json_encode($matches));

                $track = urlencode($matches[0][1]);

                $param = 'artist:"' . $artist . '"track:"' . $track . '"album:"' . $album . '"' . 'dur_min:' . $dur_min . 'dur_max:' . $dur_max;
                $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "DZApi.php(search) Deezer Search query " . $param);
                $output = $this->search_params($param);
                $output['accuracy'] = 5;
            }
        }

        /* Search for artist, track and duration informations */
        if (strcmp($output['total'], '0') == 0) {
            $param = 'artist:"' . $artist . '"track:"' . $track . '"' . 'dur_min:' . $dur_min . 'dur_max:' . $dur_max;
            $output = $this->search_params($param);
            $output['accuracy'] = 4;
        }
        /* Search for artist, track  informations */
        if (strcmp($output['total'], '0') == 0) {
            $param = 'artist:"' . $artist . '"track:"' . $track . '"';
            $output = $this->search_params($param);
            $output['accuracy'] = 3;
        }

        /* Search globally for the track name */
        if (strcmp($output['total'], '0') == 0) {
            $param = '"' . $track . '"';
            $output = $this->search_params($param);
            $output['accuracy'] = 2;
        }

        /* Still nothing found, remove (...) data and "- .." data from title */
        if (strcmp($output['total'], '0') == 0) {
            $matches = array();
            preg_match_all('/(.*)\(.*\)| - .*/m', urldecode($track), $matches, PREG_SET_ORDER, 0);
            if (strlen($matches[0][1]) !== 0) {
                $param = '"' . urlencode($matches[0][1]) . '"';
                $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "DZApi.php(search) Remove unecesssary chars from title : " . $track . "\n\t" . json_encode($matches));
                $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "DZApi.php(search) Deezer Search query " . $param);
                $output = $this->search_params($param);
                $output['accuracy'] = 1;
            }
        }

        if (strcmp($output['total'], '0') == 0) {
            $output['accuracy'] = 0;
        }
        $output['app_id'] = getenv('DEEZER_APIKEY');
        $output['params'] = $param;
        if (isset($output['error']) && $output['error']['code'] == 4) {
            throw new \Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException($output['error']['message']);
        }
        return $output;
    }

    public function SearchList($tracklist) {
        $_SESSION['deezersearchlist'] = ['status' => 'Searching', 'current' => 0, 'total' => count($tracklist)];
        $results = array();
        $current = 0;
        foreach ($tracklist as $track) {
            $trackarray = (array) $track;
            $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "DZApi.php(SearchList) searching for  TrackID : " . $trackarray['trackid']);
            $RequestToBeDone = true;
            do {
                try {
                    $search_result = $this->search($trackarray['artist'], $trackarray['album'], $trackarray['song'], $trackarray['duration']);
                    $RequestToBeDone = false;
                } catch (\Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException $e) {
                    $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "DZApi.php(SearchList) Too many requests. Waiting 1 second ");
                    sleep(1);
                }
            } while ($RequestToBeDone);
            $current++;
            $_SESSION['deezersearchlist'] = ['status' => 'Finished', 'current' => $current, 'total' => count($tracklist)];
            array_push($results, ['trackid' => $trackarray['trackid'], 'accuracy' => $search_result['accuracy'], 'info' => $search_result]);
        }
        $_SESSION['deezersearchlist'] = ['status' => 'Finished', 'current' => count($tracklist), 'total' => count($tracklist)];
        return $results;
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
        $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "DZApi.php(SearchIndividual) searching for  TrackID : " . $trackid);
        $RequestToBeDone = true;
        do {
            try {
                $search_result = $this->search($artist, $album, $song, $duration);
                $RequestToBeDone = false;
            } catch (\Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException $e) {
                $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "DZApi.php(SearchIndividual) Too many requests. Waiting 1 second");
                sleep(1);
            }
        } while ($RequestToBeDone);
        $returns = ['trackid' => $trackid, 'accuracy' => $search_result['accuracy'], 'app_id' => $search_result['app_id'], 'info' => $search_result];
        return $returns;
    }

    /**
     * This method return the url to call for the authentification
     *
     * @param string $sRedirectUrl 
     * @param array $aPerms 
     * @return string
     */
    public function getAuthUrl($sRedirectUrl, $aPerms = array("basic_access", "manage_library")) {
        return $this->_sAuthUrl . "auth.php?app_id=" . $this->_sApiKey . "&redirect_uri=" . $sRedirectUrl . "&perms=" . implode(',', $aPerms);
    }

    /**
     * This method will get the token
     *
     * @param string $sCode 
     * @return string
     * @author Mathieu BUONOMO
     */
    public function apiconnect($sCode) {
        $sUrl = $this->_sAuthUrl . "access_token.php?app_id=" . $this->_sApiKey . "&secret=" . $this->_sSecretKey . "&code=" . $sCode;
        $response = $this->sendRequest($sUrl);
        $params = null;
        parse_str($response, $params);
        $_SESSION['deezer_token'] = $params['access_token'];
        $_SESSION['deezer_token_expires'] = time() + $params['expires'];
        return $params['access_token'];
    }

    /**
     * Call the api
     *
     * @param string $sUrl 
     * @param array $aParams 
     * @return void
     * @author Mathieu BUONOMO
     */
    public function api($sUrl) {
        $sGet = $this->_sApiUrl . $sUrl . "?access_token=" . $this->getSToken();
        return json_decode($this->sendRequest($sGet), true);
    }

    public function getUserInformation() {
        return $this->api("/user/me");
    }

    public function getUserPlaylists() {
        $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "DZApi.php(getUserPlaylists)" . var_export($this->getUserInformation(), true));
        $userid = $this->getUserInformation()['id'];
        $playlists = $this->api("/user/" . $userid . "/playlists");
        $filteredplaylists = array();
        foreach ($playlists['data'] as $playlist) {
            $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "DZApi.php(getUserPlaylists) Analysing : " . var_export($playlist, true));
            if ($playlist['creator']['id'] == $userid && $playlist['is_loved_track'] != true) {
                array_push($filteredplaylists, $playlist);
                $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "DZApi.php(getUserPlaylists) Playlist Added");
            }
        }
        return $filteredplaylists;
    }

    public function getSToken() {
        return $_SESSION['deezer_token'];
    }

    public function CreatePlaylist($name, $public) {

        $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "DZApi.php(CreatePlaylist) Deezer PlaylistCreation recieved: " . $name
                . "\n\tis public : " . $public
        );
        $this->ThrottlerStack = new \GuzzleHttp\HandlerStack();
        $this->ThrottlerStack->setHandler(new \GuzzleHttp\Handler\CurlHandler());

        $throttle = new GuzzleAdvancedThrottle\Middleware\ThrottleMiddleware($this->ThrottlerRules);

        $this->ThrottlerStack->push($throttle());

        $client = new \GuzzleHttp\Client(['base_uri' => $this->_sApiUrl, 'handler' => $this->ThrottlerStack]);

        $userid = $this->getUserInformation()['id'];
        $sUrl = $this->_sApiUrl . "/user/" . $userid . "/playlists" . "?access_token=" . $this->getSToken();
        $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "DZApi.php(createPlaylist) URL : " . $sUrl);
        $response = $client->post($sUrl, [
            \GuzzleHttp\RequestOptions::HEADERS => ['Content-Type' => 'application/x-www-form-urlencoded'],
            \GuzzleHttp\RequestOptions::BODY => "title=" . $name
        ]);
        $response->getBody()->rewind();
        $output = $response->getBody()->getContents();
        if ($output === false) {
            $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "DZApi.php(CreatePlaylist) Error curl : " . curl_error($c), E_USER_WARNING);
            trigger_error('Erreur curl : ' . curl_error($c), E_USER_WARNING);
        } else {
            curl_close($c);
            return json_decode($output);
        }
    }

    public function AddTracksToPlaylist($playlistid, $tracklist) {

        $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "DZApi.php(AddTracksToPlaylist) Add to Playlist " . $playlistid . " Tracks : " . var_export($tracklist, true));

        $this->ThrottlerStack = new \GuzzleHttp\HandlerStack();
        $this->ThrottlerStack->setHandler(new \GuzzleHttp\Handler\CurlHandler());

        $throttle = new GuzzleAdvancedThrottle\Middleware\ThrottleMiddleware($this->ThrottlerRules);

        $this->ThrottlerStack->push($throttle());

        $client = new \GuzzleHttp\Client(['base_uri' => $this->_sApiUrl, 'handler' => $this->ThrottlerStack]);

        $token = $this->getSToken();

        foreach ($tracklist as $track) {

            $RequestToBeDone = true;
            do {
                try {
                    $sUrl = $this->_sApiUrl . "/playlist/" . $playlistid . "/tracks" . "?access_token=" . $token . "&songs=" . $track;
                    $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "DZApi.php(AddTracksToPlaylist) URL : " . $sUrl);
                    $response = $client->post($sUrl);
                    $RequestToBeDone = false;
                } catch (\Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException $e) {
                    $this->logs->write("debug", Logs::$MODE_FILE, "debug.log", "DZApi.php(AddTracksToPlaylist) Too many requests. Waiting 1 second ");
                    sleep(1);
                }
            } while ($RequestToBeDone);
        }
    }

}
