<?php
http_response_code(200);
exit(json_encode($GLOBALS['s3']->getEpisodes($_GET['title'], $_GET['season']), JSON_UNESCAPED_SLASHES));