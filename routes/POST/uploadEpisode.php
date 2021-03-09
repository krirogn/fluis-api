<?php
/// If 
if (!isset($_POST['title'])  || !isset($_POST['season']) ||
    !isset($_POST['number']) || !isset($_FILES['video']) ||
    !isset($_POST['type'])) {
    http_response_code(400);
    die("Not set");
}

/// Set the vars
$ext    = $_POST['type'];
$title  = $_POST['title'];
$season = $_POST['season'];
$number   = $_POST['number'];

/// Check if this is a new title
$id = "";
$shows = $GLOBALS['s3']->getShows();
if (!in_array($title, $shows)) {
    if (isset($_POST['id'])) {
        /// Insert metadata
        $id = $_POST['id'];
    } else {
        http_response_code(400);
        die("ID is not set for new title!");
    }
}

/// Set the type
$type = "";
if ($ext == 'mp4' || $ext == 'm4v') {
    $type .= '.mp4';
} else {
    http_response_code(400);
    die("Unsupported file format");
}

http_response_code(200);
exit($GLOBALS['s3']->uploadEpisode($title, $season, $number, $type, $_FILES['video']['tmp_name'], $id));