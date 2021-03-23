<?php

//ob_start();
ob_implicit_flush(true);
echo "SHIT IS GOOD\n<br/>";
ob_flush();
//ob_end_flush();
sleep(5);
exit("END");

http_response_code(200);
exit(GV::VERSION);