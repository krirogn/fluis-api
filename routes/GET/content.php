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

/// Get the URL
$userId = fileDB::userId($login);

///
$lib = fileDB::get('library/'.$userId.'.json', false, false);

/// Return different content based om type
//  If this is a show
if ($type == "shows") {
    if (isset($_GET['season']) && isset($_GET['episode'])) {
        $season  = $_GET['season'];
        $episode = $_GET['episode'];
        $index = $season.'-'.$episode;
        $cont = array("https://fluis-media.s3.fr-par.scw.cloud/"."Shows"."/".$lib->$type->$id->path."/".$season."/".$episode."-en.mp4", $lib->$type->$id->watched->$index);
    } else {
        http_response_code(400);
        die("Show data is not set");
    }
    
//  If this is a movie
} else {
    $cont = array("https://fluis-media.s3.fr-par.scw.cloud/"."Movies"."/".$lib->$type->$id->path."/main-en.mp4", $lib->$type->$id->watched);
}

http_response_code(200);
exit(json_encode($cont, JSON_UNESCAPED_SLASHES));