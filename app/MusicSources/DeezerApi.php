<?php

namespace App\MusicSources;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

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
class DeezerApi {

    private $log;

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
    public $initialized;

    public function __construct() {

        $this->_sApiKey = getenv('DEEZER_APIKEY');
        $this->_sSecretKey = getenv('DEEZER_APISECRETKEY');
        $this->log = new Logger('DeezerApi.php');
        $this->log->pushHandler(new StreamHandler(__DIR__.'/../../logs/debug.log', Logger::DEBUG));
        $this->log->debug("(__contruct) New DeeZerApi Constructor called");
        $this->log->debug("(__contruct) API key is : " . $this->_sApiKey);
        $this->initiateThrotller();
        $this->initialized = true;
    }
    
    public function __sleep() {
        return array('api', 'session', 'initialized', 'ThrottlerRules', 'ThrottlerStack', 'log','_sApiKey', '_sSecretKey','sToken');
    }
    /**
     * Return a true if this class is correctly initialized
     * @return boolean
     */
    public function isInitialized() {
        return $this->initialized;
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
                $this->log->debug("(sendRequest) Deezer request recieved : " . $sUrl);
                $response = $client->get($sUrl);
                $output = $response->getBody();
                $RequestToBeDone = false;
            } catch (\Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException $e) {
                $this->log->debug("(sendRequest) Too many requests. Waiting 1 second");
                sleep(1);
            }
        } while ($RequestToBeDone);


        if ($output === false) {
            $this->log->debug("(sendRequest) Error curl : " . curl_error($c), E_USER_WARNING);
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

    private function FormatSearchRestults($rawdata){
        $output=array();
       
        $output['accuracy']=$rawdata['accuracy'];
        $output['trackid']=$rawdata['trackid'];
        $output['total']=$rawdata['total'];
        $output['track']['id']=$rawdata['data'][0]['id'];
        $output['track']['name']=$rawdata['data'][0]['title'];
        $output['track']['link']=$rawdata['data'][0]['link'];
        $output['track']['duration']=$rawdata['data'][0]['duration'];
        $output['album']['name']=$rawdata['data'][0]['album']['title'];
        $output['album']['id']=$rawdata['data'][0]['album']['id'];
        $output['album']['link']= str_replace("api.deezer.com","www.deezer.com",$rawdata['data'][0]['album']['tracklist']);
        $output['album']['picture']=$rawdata['data'][0]['album']['cover'];
        $output['artist']['name']=$rawdata['data'][0]['artist']['name'];
        $output['artist']['id']=$rawdata['data'][0]['artist']['id'];
        $output['artist']['link']=$rawdata['data'][0]['artist']['link'];
        $output['artist']['picture']=$rawdata['data'][0]['artist']['picture'];
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

        /* Search for full informations */
        $param = 'artist:"' . $artist . '"track:"' . $track . '"album:"' . $album . '"' . 'dur_min:' . $dur_min . 'dur_max:' . $dur_max;

        $output = $this->search_params($param);
        $output['accuracy'] = 6;

        if (strcmp($output['total'], '0') == 0) {
            $matches = array();
            preg_match_all('/(.*)\(.*\)| - .*/m', urldecode($track), $matches, PREG_SET_ORDER, 0);
            if (strlen($matches[0][1]) !== 0) {

                $this->log->debug("(search) Remove unecesssary chars from title : " . $track . "\n\t" . json_encode($matches));

                $track = urlencode($matches[0][1]);

                $param = 'artist:"' . $artist . '"track:"' . $track . '"album:"' . $album . '"' . 'dur_min:' . $dur_min . 'dur_max:' . $dur_max;
                $this->log->debug("(search) Deezer Search query " . $param);
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
                $this->log->debug("(search) Remove unecesssary chars from title : " . $track . "\n\t" . json_encode($matches));
                $this->log->debug("(search) Deezer Search query " . $param);
                $output = $this->search_params($param);
                $output['accuracy'] = 1;
            }
        }

        if (strcmp($output['total'], '0') == 0) {
            $output['accuracy'] = 0;
        }
       
        $output['params'] = $param;
        if (isset($output['error']) && $output['error']['code'] == 4) {
            throw new \Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException($output['error']['message']);
        }
        return $this->FormatSearchRestults($output);
    }

    /**
     * Search for a list of songs
     * @param array $tracklist
     * @return array
     */
    public function SearchList($tracklist) {
        $_SESSION['deezersearchlist'] = ['status' => 'Searching', 'current' => 0, 'total' => count($tracklist)];
        $results = array();
        $current = 0;
        foreach ($tracklist as $track) {
            $trackarray = (array) $track;
            $this->log->debug("(SearchList) searching for  TrackID : " . $trackarray['trackid']);
            $RequestToBeDone = true;
            do {
                try {
                    $search_result = $this->search($trackarray['artist'], $trackarray['album'], $trackarray['song'], $trackarray['duration']);
                    $RequestToBeDone = false;
                } catch (\Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException $e) {
                    $this->log->debug("(SearchList) Too many requests. Waiting 1 second ");
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
     * This method return the URL to call for the authentication
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
     * @return array
     * @author Mathieu BUONOMO
     */
    private function api($sUrl) {
        $sGet = $this->_sApiUrl . $sUrl . "?access_token=" . $this->getSToken();
        return json_decode($this->sendRequest($sGet), true);
    }

    /**
     * Return an array with currently logged in user information
     * @return array
     */
    public function getUserInformation() {
        return $this->api("/user/me");
    }

    /**
     * Return an array with all playlist (name, id) for the current session
     * Do not send in this list, playlist the user is not the creator and automated playlist (loved tracks for example)
     * @return array
     */
    public function getUserPlaylists() {
        $this->log->debug("(getUserPlaylists)" . var_export($this->getUserInformation(), true));
        $userid = $this->getUserInformation()['id'];
        $playlists = $this->api("/user/" . $userid . "/playlists");
        $filteredplaylists = array();
        foreach ($playlists['data'] as $playlist) {
            $this->log->debug("(getUserPlaylists) Analysing : " . var_export($playlist, true));
            if ($playlist['creator']['id'] == $userid && $playlist['is_loved_track'] != true) {
                $playlist['folder'] = false;
                $playlist['count'] = $playlist['nb_tracks'];
                $playlist['name'] = $playlist['title'];
                array_push($filteredplaylists, $playlist);
                $this->log->debug("(getUserPlaylists) Playlist Added");
            }
        }
        return $filteredplaylists;
    }

    /**
     * Return the session Token ID
     * @return type
     */
    public function getSToken() {
        return $_SESSION['deezer_token'];
    }

    /**
     * Create a new Playlist
     * @param string $name
     * @param string $public
     * @return string playlist id
     */
    public function CreatePlaylist($name, $public) {
        $this->log->debug("(CreatePlaylist) Deezer PlaylistCreation recieved: " . $name
                . "\n\tis public : " . $public
        );
        $this->ThrottlerStack = new \GuzzleHttp\HandlerStack();
        $this->ThrottlerStack->setHandler(new \GuzzleHttp\Handler\CurlHandler());

        $throttle = new GuzzleAdvancedThrottle\Middleware\ThrottleMiddleware($this->ThrottlerRules);

        $this->ThrottlerStack->push($throttle());

        $client = new \GuzzleHttp\Client(['base_uri' => $this->_sApiUrl, 'handler' => $this->ThrottlerStack]);

        $userid = $this->getUserInformation()['id'];
        $sUrl = $this->_sApiUrl . "/user/" . $userid . "/playlists" . "?access_token=" . $this->getSToken();
        $this->log->debug("(createPlaylist) URL : " . $sUrl);
        $response = $client->post($sUrl, [
            \GuzzleHttp\RequestOptions::HEADERS => ['Content-Type' => 'application/x-www-form-urlencoded'],
            \GuzzleHttp\RequestOptions::BODY => "title=" . $name
        ]);
        $response->getBody()->rewind();
        $output = $response->getBody()->getContents();
        if ($output === false) {
            $this->log->error("(CreatePlaylist) Error curl : " . curl_error($c), E_USER_WARNING);
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

        $token = $this->getSToken();

        foreach ($tracklist as $track) {

            $RequestToBeDone = true;
            do {
                try {
                    $sUrl = $this->_sApiUrl . "/playlist/" . $playlistid . "/tracks" . "?access_token=" . $token . "&songs=" . $track;
                    $this->log->debug("(AddTracksToPlaylist) URL : " . $sUrl);
                    $response = $client->post($sUrl);
                    $RequestToBeDone = false;
                } catch (\Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException $e) {
                    $this->log->info("(AddTracksToPlaylist) Too many requests. Waiting 1 second ");
                    sleep(1);
                }
            } while ($RequestToBeDone);
        }
    }

    /**
     * Return the name of a playlist for a given PlaylistID
     * @param int $playlistID
     * @return string
     */
    public function getPlaylistName($playlistID) {
        return $this->api("/playlist/".$playlistID)['title'];        
    }
    
    private function PlaylistInfoFormat($rawdata){
        $this->log->debug("(PlaylistInfoFormat) ".var_export($rawdata,true));
        $output['name']=$rawdata['title'];
        $output['id']=$rawdata['id'];
        $output['description']=$rawdata['description'];
        $output['tracks']=$rawdata['nb_tracks'];
        $output['image']=$rawdata['picture'];
        return $output;
    }
    
    public function GetPlaylistInfo($playlistID) {
        return $this->PlaylistInfoFormat($this->api("/playlist/".$playlistID));      
    }
    

    /**
     * Return all tracks for a given PlaylistID
     * @param type $playlistID
     * @return array
     */
    public function getPlaylistItems($playlistID) {

        $playlist = $this->api("/playlist/" . $playlistID);
//        $this->log->debug("(getPlaylistItems)" . var_export($playlist, true));
        $list = array();
        foreach ($playlist['tracks']['data'] as $track) {
            $this->log->debug("(getPlaylistItems)" . var_export($track, true));
            
            array_push($list, ["ID" => $track["id"],
                "Artist" => $track["artist"]["name"],
                "Album" => $track["album"]["title"],
                "Song" => $track["title"],
                "Time" => intval($track["duration"])*1000,
                "Track" => null,
                "TotalTracks" => null
            ]);
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
