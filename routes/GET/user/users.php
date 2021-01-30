<?php
$users = fileDB::users();

http_response_code(200);
exit(json_encode($users));
