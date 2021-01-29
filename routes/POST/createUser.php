<?php
$postBody = file_get_contents("php://input");
$postBody = json_decode($postBody);

$code     = $postBody->code;
$password = $postBody->password;
$name     = $postBody->name;
$uname    = $postBody->uname;


/// Checks if the name is in the right format
// Checks if the name is shorter than 190 and bigger than 5.
// Checks if the name contains a space and that the name
// doesn't have whitespace on the start or end of the string.
if (strlen($name) <= 190 && strlen($name) >= 5 && $name == trim($name) && strpos($name, ' ') !== false) {
    /// Checks if the password is longer than 6 and shorter than 60
    if (strlen($password) >= 6 && strlen($password) <=  60) {
        /// Checks the code
        if ((string)$code === (string)fileDB::getCode()) {
            /// Check if username is taken and isn't NULL
            if (!in_array($uname, fileDB::users(), true) && strlen($uname) >= 1) {

                $query  = '{'."\n";
                $query .= "\t".'"name": "'.$name.'",'."\n";
                $query .= "\t".'"pass": "'.password_hash($password, PASSWORD_BCRYPT).'",'."\n";
                $query .= "\t".'"id": '.(fileDB::highestUserId() + 1)."\n";
                $query .= '}';

                $fp = fopen(GV::DIR_USERS.$uname.'.json', 'w');
                fwrite($fp, $query);
                fclose($fp);

                /// Inserts user id into the index
                $index = fileDB::get(GV::DIR_USERS."index.json", true);
                array_push($index, $uname);
                $fp = fopen(GV::DIR_USERS.'index.json', 'w');
                fwrite($fp, json_encode($index));
                fclose($fp);

                http_response_code(200);
                exit("Created user successfully");

            } else {
              http_response_code(400);
              die("Username is alredy taken");
            }
        } else {
          http_response_code(400);
          die("The code does not match");
        }
    } else {
      http_response_code(400);
      die("The password has to be longer than 6 and shorter than 60");
    }
} else {
  http_response_code(400);
  die("The name has to shorter than 190 characters, longer than 5, can't have spaces on the ends
       and has to have at least one space");
}
