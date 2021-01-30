<?php
$postBody = file_get_contents("php://input");
$postBody = json_decode($postBody);

$code     = $postBody->code;
$password = $postBody->password;
$uname    = $postBody->uname;
$email    = $postBody->email;


/// Checks if the email is in the right format
// Checks if the email is not taken
if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
    /// Checks if the password is longer than 6 and shorter than 60
    if (strlen($password) >= 6 && strlen($password) <=  60) {
        /// Checks the code
        if ((string)$code === (string)fileDB::getCode()) {
            /// Check if username is taken and isn't NULL
            if (!in_array($uname, fileDB::users(), true) && strlen($uname) >= 1) {

                $query  = '{'."\n";
                $query .= "\t".'"id": '.(fileDB::highestUserId() + 1).",\n";
                $query .= "\t".'"pass": "'.password_hash($password, PASSWORD_BCRYPT).'",'."\n";
                $query .= "\t".'"email": "'.$email.'"'."\n";
                $query .= '}';

                $fp = fopen(GV::DIR_USERS.$uname.'.json', 'w');
                fwrite($fp, $query);
                fclose($fp);

                /// Inserts user id into the index
                if (fileDB::fileExists(GV::DIR_USERS."index.json", true)) {
                    $index = fileDB::get(GV::DIR_USERS."index.json", true);
                    array_push($index, $uname);
                    $fp = fopen(GV::DIR_USERS.'index.json', 'w');
                    fwrite($fp, json_encode($index));
                    fclose($fp);
                } else {
                    fileDB::set(GV::DIR_USERS, "index.json", '{"1":"'.$uname.'"}', true);
                }
                

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
  die("Not a valid email");
}
