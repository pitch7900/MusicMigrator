<?php

require __DIR__ . '/../vendor/autoload.php';
use App\Deezer\ITunesLibrary as iTunesLibrary;

try {
    $dotenv = (Dotenv\Dotenv::createImmutable(__DIR__ . '/../config/'))->load();
} catch (\Dotenv\Exception\InvalidPathException $e) {
}

$file = 'Library.xml';
//$xmldata = file_get_contents($file);
//$library = new iTunesLibrary();
//$library->loadXML($xmldata);
//var_dump($library->library_array);
//var_dump($library->getPlaylistItems(86924));
$DZAPI =  new App\Deezer\DZApi();
echo $DZAPI->getAuthUrl("https://itunes2deezer.blondyfamily.com")."\n";