<?php
http_response_code(200);
exit($s3->getEntries());