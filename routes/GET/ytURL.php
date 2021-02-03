<?php
use YouTube\YouTubeDownloader;

$yt = new YouTubeDownloader();
$links = $yt->getDownloadLinks("https://www.youtube.com/watch?v=".$_GET['id']);

/// Get an array of formats and keys
$formattedLinks = array();
for ($i = 0; $i < sizeof($links); $i++) {
    array_push($formattedLinks, array('key' => $i, 'format' => $links[$i]['format']));
}
if (sizeof($formattedLinks) <= 0) {
    http_response_code(400);
    die("No avilable links");
}

/// Get an array of keys who support video and audio
$valid = array();
for ($i = 0; $i < sizeof($formattedLinks); $i++) {
    $formatArray = explode(', ', $formattedLinks[$i]['format']);

    if (in_array("video", $formatArray)) {
        if (in_array("audio", $formatArray)) {
            array_push($valid, $formattedLinks[$i]);
        }
    }
}
if (sizeof($valid) <= 0) {
    http_response_code(400);
    die("No avilable video");
}

$biggest = array('key' => '0', 'quality' => 0);
/// Determine the biggest video quality
for ($i = 0; $i < sizeof($valid); $i++) {
    $formatArray = explode(', ', $formattedLinks[$i]['format']);

    for ($j = 0; $j < sizeof($formatArray); $j++) {
        if (substr($formatArray[$j], -1) == 'p') {
            $q = (int)intval(substr($formatArray[$j], 0, -1));
            
            if ($q > $biggest['quality']) {
                $biggest['quality'] = $q;
                $biggest['key'] = $formattedLinks[$i]['key'];
            }
        }
    }
    
}

http_response_code(200);
exit($links[$biggest['key']]['url']);