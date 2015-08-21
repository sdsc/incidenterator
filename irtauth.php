<?php

/***
 * irtauth.php - do auth stuff?
 *
 * When require()d, this script will check for auth credentials and
 * prompt with a http basic auth request if none are found or if they
 * are bad.
 * You probably want to use this at the top of a file.
 *
 * This script will exit() if the user is unable to supply the right credentials
 */

// right now, we'll accept any username, as long as they supply a password
// we like.

$pwsalt = "MmMmmmm salty";
$pwhash = "08e31634052b90a9c083c58c01463d5f"; // md5 password . $pwsalt


/*
if(!isset($_SERVER['PHP_AUTH_USER']) || trim($_SERVER['PHP_AUTH_USER']) == '' || !isset($_SERVER['PHP_AUTH_PW']) || md5($_SERVER['PHP_AUTH_PW'] . $pwsalt) != $pwhash){
    header('WWW-Authenticate: Basic realm="IRT"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'You must be logged in to view this page';
    phpinfo();
    exit();
}
*/
