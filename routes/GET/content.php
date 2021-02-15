<?php
/// If 
if (!isset($_GET['type']) || !isset($_GET['login']) ||
    !isset($_GET['id'])) {
    http_response_code(400);
    die("Not set");
}

/// Set the vars
$type  = $_GET['type'];  // Show or Movie
$login = $_GET['login']; // Login session ID
$id    = $_GET['id'];    // The ID of the content

$urlType = ($type == "shows") ? "Shows" : "Movies";

/// Get the URL
$userId = fileDB::userId($login);

///
$lib = fileDB::get('library/'.$userId.'.json', false, false);

$cont = array("https://fluis-media.s3.fr-par.scw.cloud/".$urlType."/".$lib->$type->$id->path."/main-en.mp4", $lib->$type->$id->watched);

http_response_code(200);
exit(json_encode($cont, JSON_UNESCAPED_SLASHES));