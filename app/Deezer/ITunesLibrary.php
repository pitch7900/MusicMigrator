<?php

namespace App\Deezer;

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

    private $plist;
    public $library_array;

    public function __construct(){
        
    }

    public function loadXML($xmldata) {
        $tmp = tmpfile();
        fwrite($tmp, $xmldata);
        rewind($tmp);
        $path = stream_get_meta_data($tmp)['uri'];
        if (!is_readable($path)){
            throw IOException::notReadable($path);
        }   
        $this->plist = new CFPropertyList($path, CFPropertyList::FORMAT_AUTO);
        $this->library_array = $this->plist->toArray();

        fclose($tmp);
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

    public function getPlaylists() {
        $results = array();
//        var_dump($this->library_array["Playlists"]);
        foreach ($this->library_array["Playlists"] as $Playlist) {

//            echo $Playlist["Name"] . "\n";
            $list = array();
            foreach ($Playlist["Playlist Items"] as $Item) {

                $trackid = $Item["Track ID"];
                $key = $this->getTrack($trackid);
//                echo "\t" . $trackid . "\t" . $key["Artist"] . "(" . $key["Album"] . ") - " . $key["Name"] . "\n";
                array_push($list,["ID" => $Item["Track ID"], "Artist" => $key["Artist"], "Album" => $key["Album"], "Song" => $key["Name"]]);
            }
            array_push($results , ["Name" => $Playlist["Name"], "Playlist" => $list]);
//             array_push($results,'name'=>$Playlist["Name"]);
        }

        return $results;
    }

}
