<?php

namespace App\Deezer;

session_start();

/**
 * Require CFPropertyList
 */
//require_once(__DIR__.'../../vendor/rodneyrehm/plist/classes/CFPropertyList/CFPropertyList.php');

use \CFPropertyList\CFPropertyList as CFPropertyList;
use CFPropertyList\IOException as IOException;

/**
 * Description of ITunesLibrary
 *
 * @author pierre
 */
class ITunesLibrary {

    public $initialized;
    private $plist;
    public $library_array;

    public function __construct() {
        $this->initialized = false;
    }

    public function loadXML($xmldata) {

        $tmp = tmpfile();
        fwrite($tmp, $xmldata);
        rewind($tmp);
        $path = stream_get_meta_data($tmp)['uri'];
        if (!is_readable($path)) {
            throw IOException::notReadable($path);
        }
        $this->plist = new CFPropertyList($path, CFPropertyList::FORMAT_AUTO);
        $this->library_array = $this->plist->toArray();
        $this->initialized = true;

        fclose($tmp);
    }

    public function loadXMLFile($filename) {
        if (!is_readable($filename)) {
            throw IOException::notReadable($filename);
        }
        $this->plist = new CFPropertyList($filename, CFPropertyList::FORMAT_AUTO);
        $this->library_array = $this->plist->toArray();
        $this->initialized = true;
    }

    public function isInitialized() {
        return $this->initialized;
    }

    public function getLibrary() {
        return $this->library_array;
    }

    public function countTracks() {
        return count($this->library_array["Tracks"]);
    }

    public function countPlaylists() {
        return count($this->library_array["Playlists"]);
    }

    public function getTrack($trackid) {
        $key = $this->library_array["Tracks"][$trackid];
        //var_dump($key);
        return $key;
    }

    public function getPlaylist($playlistID) {
        foreach ($this->library_array["Playlists"] as $Playlist) {
            if ($Playlist["Playlist ID"] == $playlistID) {
                return $Playlist;
            }
        }
        return null;
    }

    public function getPlaylistItems($playlistID) {
        $list = array();
        $Playlist = $this->getPlaylist($playlistID);
        //var_dump($Playlist);
        if ($Playlist == null) {
            return null;
        } else {
            foreach ($Playlist["Playlist Items"] as $Item) {
                $trackid = $Item["Track ID"];
                $key = $this->getTrack($trackid);

                array_push($list, ["ID" => $Item["Track ID"], "Artist" => $key["Artist"], "Album" => $key["Album"], "Song" => $key["Name"]]);
            }
            return $list;
        }
    }

    public function getPlaylists() {
        $results = array();

        foreach ($this->library_array["Playlists"] as $Playlist) {
//            $list = array();
//            foreach ($Playlist["Playlist Items"] as $Item) {
//
//                $trackid = $Item["Track ID"];
//                $key = $this->getTrack($trackid);
//
//                array_push($list, ["ID" => $Item["Track ID"], "Artist" => $key["Artist"], "Album" => $key["Album"], "Song" => $key["Name"]]);
//            }
            array_push($results, ["name" => $Playlist["Name"], "id" => $Playlist["Playlist ID"]]);
        }

        return $results;
    }

}
