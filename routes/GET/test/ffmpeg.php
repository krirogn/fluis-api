<?php
//$title = "https://fluis-media.s3.fr-par.scw.cloud/Shows/Avatar/1/1-en.mp4";
$title = "https://fluis-media.s3.fr-par.scw.cloud/Movies/Planet51/main-en.mp4";

//exit(json_encode(FFMPEG::getAudioLangs($title), JSON_UNESCAPED_SLASHES));
//exit(json_encode(FFMPEG::getCaptionLangs($title), JSON_UNESCAPED_SLASHES));
//exit(json_encode(FFMPEG::getAllLangs($title), JSON_UNESCAPED_SLASHES));
exit(json_encode(FFMPEG::getAllLangsWithIndex($title), JSON_UNESCAPED_SLASHES));

//exit((string)FFMPEG::audioIndexFromLang($title, "nno"));
//exit((string)FFMPEG::captionIndexFromLang($title, "nor"));

//exit(FFMPEG::DASH($title));
//exit(file_get_contents("/var/www/html/dash/test.txt"));