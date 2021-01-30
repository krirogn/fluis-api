<?php
http_response_code(200);
exit(json_encode($s3->getDirs(), JSON_UNESCAPED_SLASHES));