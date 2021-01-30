<?php
$postBody = file_get_contents("php://input");
$postBody = json_decode($postBody);

$id       = $postBody->id;
$username = fileDB::getUname($id);

/// Check if user exists
if ($username !== FALSE) {

    /// 
    if (fileDB::del(GV::DIR_USERS.$username.".json", true)) {

        /// 
        $index = fileDB::get(GV::DIR_USERS."index.json", true);
        unset($index[$id]);

        /// 
        if (sizeof($index) == 0) {
            fileDB::del(GV::DIR_USERS.'index.json', true);
        } else {
            fileDB::set(GV::DIR_USERS, 'index.json', json_encode($index), true);
        }

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
