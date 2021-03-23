<?php
/// If 
if (!isset($_POST['path'])  || !isset($_FILES['video']) ||
    !isset($_POST['type']) || !isset($_POST['login']) ||
    !isset($_POST['id'])) {
    http_response_code(400);
    die("Not set");
}

/// Set the vars
$path  = $_POST['path'];
$ext   = $_POST['type'];
$login = $_POST['login'];
$id    = $_POST['id'];

/// Set the type
$type = "";
if ($ext == 'mp4' || $ext == 'm4v') {
    $type .= '.mp4';
} else {
    http_response_code(400);
    die("Unsupported file format");
}

http_response_code(200);
exit($GLOBALS['s3']->uploadMovie($path, $type, $_FILES['video']['tmp_name'], $id));