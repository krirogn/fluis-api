<?php
/// Gets the token sendt with the POST request.
$authBody = file_get_contents("php://input");
$authBody = json_decode($authBody);

/// Extracts the token from the POST request.
$token = $authBody->token;

Auth::authExit($token);
