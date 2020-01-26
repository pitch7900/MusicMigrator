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

    /**
     * Try to read an XML Plist file
     * Trow IOException if not possible
     * @param type $xmldata
     * @throws type
     */
    public function loadXML($xmldata) {

        $tmp = tmpfile();
        fwrite($tmp, $xmldata);
        rewind($tmp);
        $path = stream_get_meta_data($tmp)['uri'];
        if (!is_readable($path)) {
            throw IOException::notReadable($path);
        }
        try {
            $this->plist = new CFPropertyList($path, CFPropertyList::FORMAT_AUTO);
            $this->library_array = $this->plist->toArray();
            $this->initialized = true;
        } catch (IOException $e) {
            throw IOException::notReadable($e);
        }

        fclose($tmp);
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

    public function countPlaylistTracks($playlistID) {
        return count($this->getPlaylist($playlistID)['Playlist Items']);
    }

    public function countPlaylists() {
        return count($this->library_array["Playlists"]);
    }

    public function getTrack($trackid) {
        $key = $this->library_array["Tracks"][$trackid];
        //var_dump($key);
        return $key;
    }

    public function getPlaylistName($playlistID) {
        foreach ($this->library_array["Playlists"] as $Playlist) {
            if ($Playlist["Playlist ID"] == $playlistID) {
                return $Playlist["Name"];
            }
        }
        return null;
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

                array_push($list, ["ID" => $Item["Track ID"],
                    "Artist" => $key["Artist"],
                    "Album" => $key["Album"],
                    "Song" => $key["Name"],
                    "Time" => $key["Total Time"],
                    "Track" => $key["Track Number"],
                    "TotalTracks" => $key["Track Count"]
                ]);
            }
            return $list;
        }
    }

    private function AddToParent($ParentPersistentID, $lists, $arraytoadd) {
        $counter=0;
        foreach ($lists as $list) {
            if ($list["PersistentID"] == $ParentPersistentID) {
                array_push($lists[$counter]["subfolder"], $arraytoadd);
                return $lists;
            }
            $counter++;
        }
        return null;
    }

    public function getPlaylists() {
        $results = array();

        foreach ($this->library_array["Playlists"] as $Playlist) {

            if (array_key_exists("Folder", $Playlist)) {
                $folder = true;
            } else {
                $folder = false;
            }
            if (array_key_exists("Parent Persistent ID", $Playlist)) {
                $parentid = $Playlist["Parent Persistent ID"];
                $arraytoadd = ["name" => $Playlist["Name"],
                    "id" => $Playlist["Playlist ID"],
                    "count" => $this->countPlaylistTracks($Playlist["Playlist ID"]),
                    "PersistentID" => $Playlist["Playlist Persistent ID"],
                    "ParentPersistentID" => $parentid,
                    "folder" => $folder,
                    "subfolder" => array()];
                $results = $this->AddToParent($parentid, $results, $arraytoadd);
            } else {
                $parentid = null;
                array_push($results, ["name" => $Playlist["Name"],
                    "id" => $Playlist["Playlist ID"],
                    "count" => $this->countPlaylistTracks($Playlist["Playlist ID"]),
                    "PersistentID" => $Playlist["Playlist Persistent ID"],
                    "ParentPersistentID" => $parentid,
                    "folder" => $folder,
                    "subfolder" => array()]);
            }

        }

        return $results;
    }

}
