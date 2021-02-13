<?php
http_response_code(200);
exit(fileDB::userId($_GET['id']));