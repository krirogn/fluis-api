<?php
/// Gets the token sendt with the POST request.
$authBody = file_get_contents("php://input");
$authBody = json_decode($authBody);

/// Extracts the token from the POST request.
$token = $authBody->token;

/// Checks if the token exists in the login_tokens
if (in_array(sha1($token).".lt", fileDB::getFilesInDir("login_tokens"))) {
    http_response_code(200);
    exit(true);
} else {
    http_response_code(400);
    die("Not logged in");
}
