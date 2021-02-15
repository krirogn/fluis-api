<?php
http_response_code(200);
exit(json_encode($s3->getShowsID($_GET['login']), JSON_UNESCAPED_SLASHES));