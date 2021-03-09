<?php
class Auth {

  static function user($dir) {

    if ($_SERVER['REQUEST_METHOD'] == "POST" && $dir != "routes/POST/user/") {
      $postBody = file_get_contents("php://input");
      $postBody = json_decode($postBody);
      
      Auth::authReturn($postBody->login);
      echo fileDB::get("login_tokens/".sha1($postBody->login).".lt");
      return fileDB::get(GV::DIR_USERS.fileDB::get("login_tokens/".sha1($postBody->login).".lt")['userId'].".json", true);
    }

  }

  static function userHandler() {

    if ($_SERVER['REQUEST_METHOD'] == "POST") {

      $postBody = file_get_contents("php://input");
      $postBody = json_decode($postBody);
      
      Auth::authReturn($postBody->login);
      return fileDB::get(GV::DIR_USERS.fileDB::get("login_tokens/".sha1($postBody->login).".lt")['userId'].".json", true);

    } else if ($_SERVER['REQUEST_METHOD'] == "GET") {
      
      Auth::authReturn($_GET['login']);
      return fileDB::get(GV::DIR_USERS.fileDB::get("login_tokens/".sha1($_GET['login']).".lt")['userId'].".json", true);

    }

  }

  static function authExit($token) {

    /// Checks if login token foler exists
    if (!fileDB::folderExists("login_tokens")) {
      fileDB::setFolder("login_tokens");
    }
    
    /// Checks if the token exists in the login_tokens
    if (in_array(sha1($token).".lt", fileDB::getFilesInDir("login_tokens"))) {

      /// Checks if the time has expired
      //  Creates a time object for the created time in the token
      $tokenDate = new DateTime(fileDB::get("login_tokens/".sha1($token).".lt")['created']);
      // Checks if the difference in the time and now is bugger than the max limit set in GV
      if ($tokenDate->diff(new DateTime())->days < GV::AUTH_MAX_TIME) {

        http_response_code(200);
        exit("Logged in");

      } else {

        http_response_code(400);
        die("Not logged in");

      }

    } else {

      http_response_code(400);
      die("Not logged in");

    }

  }


  static function authReturn($token) {

    /// Checks if login token foler exists
    if (!fileDB::folderExists("login_tokens")) {
      fileDB::setFolder("login_tokens");
    }
    
    /// Checks if the token exists in the login_tokens
    if (in_array(sha1($token).".lt", fileDB::getFilesInDir("login_tokens"))) {

      /// Checks if the time has expired
      //  Creates a time object for the created time in the token
      $tokenDate = new DateTime(fileDB::get("login_tokens/".sha1($token).".lt")['created']);
      // Checks if the difference in the time and now is bugger than the max limit set in GV
      if ($tokenDate->diff(new DateTime())->days > GV::AUTH_MAX_TIME) {

        http_response_code(400);
        die("Not logged in");

      }

    } else {

      http_response_code(400);
      die("Not logged in");

    }

  }
    
}