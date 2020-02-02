<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use \App\Utils\Logs as Logs;
use \hamburgscleanest\GuzzleAdvancedThrottle as GuzzleAdvancedThrottle;

namespace App\MusicSources;

/**
 * Description of SpotifyApi
 *
 * @author pierre
 */
class SpotifyApi {
    
     
    private $logs;
    private $_sApiUrl = "https://api.spotify.com";
     private $_sApiMaxRequest = "50";
    private $_sApiRequestInterval = "5";
    private $ThrottlerRules;
    private $ThrottlerStack;
    
    public function __construct() {
        $this->logs = new Logs();
        $this->initiateThrotller();
    }
    
    private function initiateThrotller(){
        $this->ThrottlerRules = new GuzzleAdvancedThrottle\RequestLimitRuleset([
            $this->_sApiUrl => [
                [
                    'max_requests' => $this->_sApiMaxRequest,
                    'request_interval' => $this->_sApiRequestInterval
                ]
            ]
        ]);
    }
}
