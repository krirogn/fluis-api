<?php
http_response_code(200);
exit(json_encode($s3->getTitles(), JSON_UNESCAPED_SLASHES));