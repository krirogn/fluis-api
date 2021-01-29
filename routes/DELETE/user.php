<?php
$postBody = file_get_contents("php://input");
$postBody = json_decode($postBody);

$id       = $postBody->id;

$username = fileDB::getUname($id);

/// Check if user exists
if ($username !== FALSE) {

    ///
    if (unlink(GV::DIR_USERS.$username.".json")) {

        ///
        $index = fileDB::get(GV::DIR_USERS."index.json", true);
        unset($index[$id]);

        $fp = fopen(GV::DIR_USERS.'index.json', 'w');
        fwrite($fp, json_encode($index));
        fclose($fp);

        http_response_code(200);
        exit($username." was deleted");

    } else {
      http_response_code(400);
      die($username." couldn't be deleted");
    }



} else {
  http_response_code(400);
  die("This user id doesn't exist");
}
