<?php

use \App\Utils\Logs as Logs;

namespace App\MusicSources;

/**
 * Description of MusicSource
 *
 * @author pierre
 */
class MusicSource {

    private $logs;
    private $source;
    
    public function __construct($sourcename) {
        $this->logs = new Logs();
        switch ($sourcename){
            case "DEEZER":
                $this->source=new DeezerApi();
                break;
            case "SPOTIFY":
                $this->source=new SpotifyApi();
                break;
            case "ITUNESFILE":
                $this->source=new ITunesLibrary();
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
        return $this->source->isInitialized();
    }
    
    /**
     * count the number of track
     * @return int
     */
    public function countTracks() {
        return $this->source->countTracks();
    }

    /**
     * Count the number of playlists
     * @return int
     */
    public function countPlaylists() {
        return $this->source->countPlaylists();
    }

    /**
     * Return an array for a given trackId
     * @param type $trackid
     * @return array
     */
    public function getTrack($trackid) {
        return $this->source->getTrack($trackid);
    }

    /**
     * Return the name of a playlist for a given PlaylistID
     * @param int $playlistID
     * @return string
     */
    public function getPlaylistName($playlistID) {
        return $this->source->getPlaylistName($playlistID);
    }

    /**
     * Return the playlist array for a given PlaylistID
     * @param int $playlistID
     * @return array
     */
    public function getPlaylist($playlistID) {
        return $this->source->getPlaylist($playlistID);
    }

    /**
     * Return all tracks for a given PlaylistID
     * @param type $playlistID
     * @return array
     */
    public function getPlaylistItems($playlistID) {
        return $this->source->getPlaylistItems($playlistID);
    }



    /**
     * Return an array with all playlists information (structured with folders of first level)
     * @return array
     */
    public function getPlaylists() {
        return $this->source->getPlaylists();
    }
    
    

}
