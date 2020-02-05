<?php

use \App\Utils\Logs as Logs;

namespace App\MusicSources;

/**
 * Description of MusicDestination
 *
 * @author pierre
 */
class MusicDestination {

    private $logs;
    private $destination;
    
    public function __construct($destinationname) {
        $this->logs = new Logs();
        switch ($destinationname){
            case "DEEZER":
                $this->destination=new DeezerApi();
                break;
            case "SPOTIFY":
                $this->destination=new SpotifyApi();
                break;
            default:
                throw new Exception("Wrong Destination Set", 0);
        }
        
    }
    
    
    /**
     * Return a true if this class is correctly initialized
     * @return boolean
     */
    public function isInitialized() {
        return $this->destination->isInitialized();
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
        return $this->destination->SearchIndividual($trackid, $artist, $album, $song, $duration);
    }

    /**
     * This method return the URL to call for the authentication
     *
     * @param string $sRedirectUrl 
     * @param array $aPerms 
     * @return string
     */
    public function getAuthUrl($sRedirectUrl, $aPerms = array("basic_access", "manage_library")) {
        return $this->destination->getAuthUrl($sRedirectUrl, $aPerms);
    }

    /**
     * This method will connect and get the token
     *
     * @param string $sCode 
     * @return string
     * @author Mathieu BUONOMO
     */
    public function apiconnect($sCode) {
        return $this->destination->apiconnect($sCode);
    }

    
    
    /**
     * Return an array with currently logged in user information
     * @return array
     */
    public function getUserInformation() {
        return $this->destination->getUserInformation();
    }
    
    /**
     * Return an array with all playlist (name, id) for the current session
     * Do not send in this list, playlist the user is not the creator and automated playlist (loved tracks for example)
     * @return array
     */
    public function getUserPlaylists() {
        return $this->destination->getUserPlaylists();
    }
    
    
    /**
     * Return the session Token ID
     * @return type
     */
    public function getSToken() {
        return $this->destination->getSToken();
    }
    /**
     * Create a new Playlist
     * @param string $name
     * @param string $public
     * @return string playlist id
     */
    public function CreatePlaylist($name, $public) {
        $this->destination->CreatePlaylist($name, $public);
    }
    /**
     * Add TracksID to a playlist
     * @param int $playlistid
     * @param array $tracklist
     */
    public function AddTracksToPlaylist($playlistid, $tracklist) {
        $this->destination->AddTracksToPlaylist($playlistid, $tracklist);
    }
    

}
