<?php
$postBody = file_get_contents("php://input");
$postBody = json_decode($postBody);

$uname    = $postBody->username;
$password = $postBody->password;

/// Checks if the username exists
if (in_array($uname, fileDB::users())) {
    /// Checks if the password matches the users password
    if (password_verify($password, strval(fileDB::get("users/".$uname.".json")["pass"]))) {

        /// Generates a login token
        // Generates the token
        $cstrong = true;
        $token   = bin2hex(openssl_random_pseudo_bytes(64, $cstrong));

        /// Check if token is already taken
        if (in_array($token.".lt", fileDB::getFilesInDir("login_tokens"))) {
          $tokenTaken = true;
        } else {
          $tokenTaken = false;
        }

        /// Gets a new token until we
        // get an unused one.
        while($tokenTaken == true) {
          $token = bin2hex(openssl_random_pseudo_bytes(64, $cstrong));

          /// Check if token is already taken
          if (!in_array($token.".lt", fileDB::getFilesInDir("login_tokens"))) {
            $tokenTaken = false;
          }
        }

        /// Gets the users id
        $userId  = fileDB::get("users/".$uname.".json")['id'];

        /// The JSON data for the login_tokens file
        $data  = '{'."\n";
        $data .= "\t".'"userId": "'.$userId.'",'."\n";
        $data .= "\t".'"created": "'.(string)date("Y-m-d h:m:s",time()).'"'."\n";
        $data .= '}';

        /// Inserts the token into the DB
        fileDB::set("login_tokens", sha1($token).".lt", $data);

        /// Echo out a true to signify a successful login
        http_response_code(200);
        exit($token);

    } else {
        http_response_code(400);
        die("The password doesn't match!");
    }
} else {
    http_response_code(400);
    die("This username does not exist!");
}
