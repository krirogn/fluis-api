<?php
http_response_code(200);
exit(json_encode($s3->getSeasonsFromID($_GET['id']), JSON_UNESCAPED_SLASHES));