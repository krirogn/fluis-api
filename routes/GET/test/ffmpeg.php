<?php
Auth::userHandler();
//$GLOBALS['s3'] = new S3($GLOBALS['USER']['s3_base'], $GLOBALS['USER']['s3_bucket'], $GLOBALS['USER']['s3_region'], $GLOBALS['USER']['s3_access_key'], $GLOBALS['USER']['s3_secret_key']);

//$title = "https://fluis-media.s3.fr-par.scw.cloud/Shows/Avatar/1/1-en.mp4";
$title = "https://fluis-media.s3.fr-par.scw.cloud/Movies/Planet51/main-en.mp4";

//exit(json_encode(FFMPEG::getAudioLangs($title)));
//exit(json_encode(FFMPEG::getCaptionLangs($title), JSON_UNESCAPED_SLASHES));
//exit(json_encode(FFMPEG::getAllLangs($title), JSON_UNESCAPED_SLASHES));
exit(json_encode(FFMPEG::getAllLangsWithIndex($title), JSON_UNESCAPED_SLASHES));
//exit(FFMPEG::vobsub2srt($title));

//exit((string)FFMPEG::audioIndexFromLang($title, "nno"));
//exit((string)FFMPEG::captionIndexFromLang($title, "nor"));

//exit(FFMPEG::DASH($title));
//exit(file_get_contents("/var/www/html/dash/test.txt"));