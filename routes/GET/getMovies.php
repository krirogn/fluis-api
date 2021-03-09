<?php
http_response_code(200);
exit(json_encode($GLOBALS['s3']->getMovies(), JSON_UNESCAPED_SLASHES));