<?php
http_response_code(200);
//exit(json_encode($s3->getEntries(), JSON_UNESCAPED_SLASHES));
exit($s3->getEntries());