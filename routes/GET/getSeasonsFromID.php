<?php
Auth::userHandler($_GET['login']);
$GLOBALS['s3'] = new S3($GLOBALS['USER']['s3_base'], $GLOBALS['USER']['s3_bucket'], $GLOBALS['USER']['s3_region'], $GLOBALS['USER']['s3_access_key'], $GLOBALS['USER']['s3_secret_key']);

http_response_code(200);
exit(json_encode($GLOBALS['s3']->getSeasonsFromID($_GET['id']), JSON_UNESCAPED_SLASHES));