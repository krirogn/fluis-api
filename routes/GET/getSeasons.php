<?php
http_response_code(200);
exit(json_encode($s3->getSeasons($_GET['title']), JSON_UNESCAPED_SLASHES));