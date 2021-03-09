<?php
http_response_code(200);
exit(json_encode($s3->getShowsID(), JSON_UNESCAPED_SLASHES));