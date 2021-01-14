<?php
http_response_code(200);
//exit($s3->list()[1]['name']);
exit(json_encode($s3->getDirs(), JSON_UNESCAPED_SLASHES));