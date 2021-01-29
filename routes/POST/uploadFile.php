<?php
/// If 
if (!isset($_POST['title'])  || !isset($_POST['season']) ||
    !isset($_POST['name'])   || !isset($_POST['number']) ||
    !isset($_FILES['video']) || !isset($_POST['type'])) {
    http_response_code(400);
    die("Not set");
}

/// Set the vars
$ext    = $_POST['type'];
$title  = $_POST['title'];
$season = $_POST['season'];
$name   = $_POST['name'];
$number = $_POST['number'];

/// Set the type
$type = "";
if ($ext == 'mp4' || $ext == 'm4v') {
    $type .= '.mp4';
} else {
    http_response_code(400);
    die("Unsupported file format");
}

/// Set the location
$loc = $title . '/' . $season . '/' . $number . $type;

http_response_code(200);
exit($s3->uploadVideo($loc, $_FILES['video']['tmp_name']));