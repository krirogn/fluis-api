<?php
class Auth {

  static function user($dir) {

    if ($_SERVER['REQUEST_METHOD'] == "POST" && $dir != "routes/POST/user/") {

      $token;
      if (isset($_POST['login'])) {
        $token = $_POST['login'];
      } else {
        $postBody = file_get_contents("php://input");
        $postBody = json_decode($postBody);

        if (isset($postBody->login)) {
          $token = $postBody->login;
        } else {
          http_response_code(400);
          die("No login token provided");
        }
      }

      Auth::authReturn($token);
      $GLOBALS['USER'] = fileDB::get(GV::DIR_USERS.fileDB::get("login_tokens/".sha1($token).".lt")['userId'].".json", true);
      
    } else if ($_SERVER['REQUEST_METHOD'] == "DELETE") {

      $token;
      if (isset($_POST['login'])) {
        $token = $_POST['login'];
      } else {
        $postBody = file_get_contents("php://input");
        $postBody = json_decode($postBody);

        if (isset($postBody->login)) {
          $token = $postBody->login;
        } else {
          http_response_code(400);
          die("No login token provided");
        }
      }

      Auth::authReturn($token);
      $GLOBALS['USER'] = fileDB::get(GV::DIR_USERS.fileDB::get("login_tokens/".sha1($token).".lt")['userId'].".json", true);

    }

  }

  static function userHandler() {

    if ($_SERVER['REQUEST_METHOD'] == "POST") {

      $token;
      if (isset($_POST['login'])) {
        $token = $_POST['login'];
      } else {
        $postBody = file_get_contents("php://input");
        $postBody = json_decode($postBody);

        if (isset($postBody->login)) {
          $token = $postBody->login;
        } else {
          http_response_code(400);
          die("No login token provided");
        }
      }
      
      Auth::authReturn($token);
      $GLOBALS['USER'] = fileDB::get(GV::DIR_USERS.fileDB::get("login_tokens/".sha1($token).".lt")['userId'].".json", true);

    } else if ($_SERVER['REQUEST_METHOD'] == "GET") {
      
      Auth::authReturn($_GET['login']);
      $GLOBALS['USER'] = fileDB::get(GV::DIR_USERS.fileDB::get("login_tokens/".sha1($_GET['login']).".lt")['userId'].".json", true);

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
        die("Token has expired");

      }

    } else {

      http_response_code(400);
      die("Not logged in2");

    }

  }
    
}