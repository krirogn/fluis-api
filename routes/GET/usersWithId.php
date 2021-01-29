<?php
$users = fileDB::usersWithId();

http_response_code(200);
exit(json_encode($users));
