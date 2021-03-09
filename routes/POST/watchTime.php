<?php
/// Gets the token sendt with the POST request.
$postBody = file_get_contents("php://input");
$postBody = json_decode($postBody);

/// If 
if (!isset($postBody->type)) {
    http_response_code(400);
    die("'type' not set");
} else {
    if (!isset($postBody->id)) {
        http_response_code(400);
        die("'id' not set");
    } else {
        if (!isset($postBody->time)) {
            http_response_code(400);
            die("'time' not set");
        }
    }
}

/// Set the vars
$type  = $postBody->type;
$id    = $postBody->id;
$time  = $postBody->time;

if ($type == "shows") {
    if (isset($postBody->season) && isset($postBody->episode)) {
        $season  = $postBody->season;
        $episode = $postBody->episode;

        http_response_code(200);
        exit(fileDB::updateWatch($type, $id, $time, $season, $episode));
    } else {
        http_response_code(400);
        die("Show data is not set");
    }
} else {
    http_response_code(200);
    exit(fileDB::updateWatch($type, $id, $time));
}
