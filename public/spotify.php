<?php
require __DIR__ . '/../vendor/autoload.php';
use \App\Utils\Logs as Logs;

try {
    $dotenv = (Dotenv\Dotenv::createImmutable(__DIR__ . '/../config/'))->load();
} catch (\Dotenv\Exception\InvalidPathException $e) {
    echo "Can't read file";
}

//echo getenv('SPOTIFY_APIKEY')."\n";
$logs=new   Logs();

$logs->write("debug", Logs::$MODE_FILE, "debug.log", "Spotify.php " . getenv('SPOTIFY_APIKEY')."\n\t".
                getenv('SPOTIFY_APISECRETKEY')."\n\t".
                getenv('SITEURL')."/spotify.php");
//
//$session = new SpotifyWebAPI\Session(
//                getenv('SPOTIFY_APIKEY'),
//                getenv('SPOTIFY_APISECRETKEY'),
//                getenv('SITEURL')."/spotify.php"
//        );
//
//$api = new SpotifyWebAPI\SpotifyWebAPI();
//
//if (isset($_GET['code'])) {
//    $session->requestAccessToken($_GET['code']);
//    $api->setAccessToken($session->getAccessToken());
//
//    print_r($api->me());
//} else {
//    $options = [
//        'scope' => [
//            'user-read-email',
//        ],
//    ];
//
//    header('Location: ' . $session->getAuthorizeUrl($options));
//    echo 'Location: ' . $session->getAuthorizeUrl($options)."\n";
//    die();
//}

$spotifyapi = new \App\MusicSources\SpotifyApi();
        $arguments['spotifyauthurl'] = $spotifyapi->getAuthUrl(getenv("SITEURL") . "/spotify/auth/sources");