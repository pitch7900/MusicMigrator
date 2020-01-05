<?php

require __DIR__ . '/../vendor/autoload.php';
use App\Deezer\ITunesLibrary as iTunesLibrary;
$file = 'Library.xml';
$xmldata = file_get_contents($file);
$library = new iTunesLibrary();
$library->loadXML($xmldata);
//var_dump($library->library_array);
var_dump($library->getPlaylistItems(86924));