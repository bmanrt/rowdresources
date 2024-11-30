<?php
session_start();
require 'autoload.php';

use Abraham\TwitterOAuth\TwitterOAuth;

$consumerKey = 'gMIXm1Ypnkiz3KMWCwKsP2RJI';
$consumerSecret = 'gDL57MbUOEOgDh4f0UEo6dCqn4hxIzr9J3i7ywi8tMvAhUGXRj';
$oauthCallback = 'http://localhost/twitter_callback.php';

$connection = new TwitterOAuth($consumerKey, $consumerSecret);
$request_token = $connection->oauth('oauth/request_token', ['oauth_callback' => $oauthCallback]);

if ($connection->getLastHttpCode() == 200) {
    // Save the request token and secret in the session
    $_SESSION['oauth_token'] = $request_token['oauth_token'];
    $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

    // Return the request token as JSON
    echo json_encode(['oauth_token' => $request_token['oauth_token']]);
} else {
    // Handle error case
    echo json_encode(['error' => 'Could not connect to Twitter. Refresh the page or try again later.']);
}
?>
