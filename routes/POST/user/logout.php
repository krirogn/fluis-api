<?php
/// Gets all the information sendt with POST request.
$postBody = file_get_contents("php://input");
/// Decodes the POST information into a JSON format.
$postBody = json_decode($postBody);

/// Extranct the info from the POST query.
$userToken   = $postBody->token;
/// Da stands for "Delete All"
$da          = $postBody->da;

/// If both of the vars have a value
if (isset($userToken) && isset($da)) {
    /// If only this session will be deleted
    if ($da == false) {
        /// If the token exists
        if (in_array(sha1($userToken).".lt", fileDB::getFilesInDir("login_tokens"))) {
            fileDB::del("login_tokens/".sha1($userToken).".lt");

            http_response_code(200);
            exit("Din økt er fjernet");
        } else {
            http_response_code(400);
            die("The token doesn't exist");
        }
    } else {
      $userId = (int)fileDB::get("login_tokens/".sha1($userToken).".lt")["userId"];

      /// If the user with the id exists
      if (array_key_exists($userId, fileDB::usersWithId())) {
          /// Delete all the tokens from a user
          $tokens = fileDB::getFilesInDir("login_tokens");
          foreach ($tokens as $token) {
              if (fileDB::get("login_tokens/".$token)["userId"] == $userId) {
                  fileDB::del("login_tokens/".$token);
              }
          }

          http_response_code(200);
          exit("All your sessions are removed");
      } else {
          http_response_code(400);
          die("This user does not exist");
      }
    }

} else {
    http_response_code(400);
    die("The params haven't been set");
}
