<?php
class Auth {

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