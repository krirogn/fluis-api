<?php
http_response_code(200);
exit(json_encode($s3->getEpisodesFromID($_GET['id'], $_GET['season']), JSON_UNESCAPED_SLASHES));