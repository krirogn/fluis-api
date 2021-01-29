<?php
/// If 
/*if (!isset(Helper::PUT('title')) || !isset(Helper::PUT('season')) ||
    !isset(Helper::PUT('name')) || !isset(Helper::PUT('number')) ||
    !isset($_FILES['video'])) {
    http_response_code(400);
    die("Not set");
}*/
die($_FILES['video']);

/// Set the vars
$title  = Helper::PUT('title');
$season = Helper::PUT('season');
$name   = Helper::PUT('name');
$number = Helper::PUT('number');

/// Set the type
$type = "";
if ($_FILES['video']['type'] == 'video/mp4') {
    $type .= '.mp4';
}

/// Set the location
$loc = $title . '/' . $season . '/' . $number . $type;
http_response_code(400);
die($loc);

http_response_code(200);
exit($s3->uploadVideo($loc, $_FILES['video']['tmp_name']));