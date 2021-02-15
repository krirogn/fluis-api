<?php
http_response_code(200);
exit(json_encode($s3->getMoviesID($_GET['login']), JSON_UNESCAPED_SLASHES));