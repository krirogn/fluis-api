<?php
class fileDB {

    /// General File DB functions
    /// A general query function
    static function get($path, $root = false, $asc = true) {

      if ($root == false) {
          $path = "data/".$path;
      }

      $string = file_get_contents($path);
      if ($string === false) {
          http_response_code(400);
          die("Couldn't open the file: ".$path);
      }

      $json = json_decode($string, $asc);
      if ($json === null) {
          http_response_code(400);
          die('Couldn\'t convert "'.$path.'" to a JSON array');
      }

      return $json;

    }

    /// A general set function
    static function set($path, $fileName, $data, $root = false) {

      if ($root == false) {
          $path = "data/".$path;
      }

      $fp = fopen($path."/".$fileName, 'w');
      fwrite($fp, $data);
      fclose($fp);

    }

    /// A general folder making function
    static function setFolder($path, $root = false) {

        if ($root == false) {
            $path = "data/".$path;
        }

        if (is_dir($path) == false) {
            mkdir($path);
        }

    }

    /// A general file deletion function
    static function del($path, $root = false) {

      if ($root == false) {
          $path = "data/".$path;
      }

      return unlink($path);

    }

    static function delAllInFolder($path, $root = false) {

        if ($root == false) {
            $path = "data/".$path;
        }

        $files = glob($path."/*"); // get all file names
        foreach($files as $file){ // iterate files
            if(is_file($file)) {
                unlink($file); // delete file
            }
        }

    }

    /// A general file to see if file exists
    static function fileExists($path, $root = false) {
    
        if ($root == false) {
            $path = "data/".$path;
        }
  
        if (file_exists($path) == true) {
            return TRUE;
        } else {
            return FALSE;
        }

    }

    ///
    static function folderExists($path, $root = false) {

        if ($root == false) {
            $path = "data/".$path;
        }

        if (is_dir($path) == true) {
            return TRUE;
        } else {
            return FALSE;
        }
    
    }

    /// A general function to output all files in a dir
    static function getFilesInDir($path, $root = false) {

      if ($root == false) {
          $path = "data/".$path;
      }

      /// Gets all the files in the directory
      $dir = scandir($path);
      /// Removes the first two elements from the array
      // It's because the two first elements are "." and ".."
      $dir = array_slice($dir, 2);

      return array_values($dir);

    }


    /// Functions for users

    /// Gets the code from the code file
    // file_get_contents() starts at root
    static function getCode() {
        return (string)substr(file_get_contents(GV::CODE_PATH), 0, -1);
    }

    /// Sets the code in the code file
    //  to a new code
    static function setCode() {
        $fp = fopen(GV::CODE_PATH, 'w');
        fwrite($fp, Helper::generateRandomString(6));
        fclose($fp);
    }

    /// Get's an array of all the usernames
    static function users($usersPath = GV::DIR_USERS) {

        $usersIndex = fileDB::get($usersPath."index.json", true);

        $users = array();
        /// Removes the ".json" from the name
        foreach ($usersIndex as $id => $uname) {
            array_push($users, $uname);
        }

        /// Cleans and reindexes the array
        return array_values($users);

    }

    /// 
    static function idFromUsername($uname) {

        $usersIndex = fileDB::get(GV::DIR_USERS."index.json", true);

        foreach ($usersIndex as $id => $name) {
            if ($uname == $name) {
                return (String)$id;
            }
        }

        return "No matching ID";

    }

    static function usersWithId($usersPath = GV::DIR_USERS) {

        $indexFile = file_get_contents($usersPath."index.json");

        $index = json_decode($indexFile, true);

        return $index;

    }

    static function highestUserId($usersPath = GV::DIR_USERS) {

        /// Gets all the files in the users directory
        $usersDir = scandir($usersPath);
        /// Removes the first two elements from the array
        // It's because the two first elements are "." and ".."
        $usersDir = array_slice($usersDir, 2);
        /// Removes the index.json
        if (($key = array_search("index.json", $usersDir)) !== false) {
            unset($usersDir[$key]);
        }
        /// Cleans and reindexes the array
        $usersDir = array_values($usersDir);

        $highestId = 0;
        foreach($usersDir as $u) {
            $id = (int)json_decode(file_get_contents($usersPath.$u), true)["id"];

            if ($id > $highestId) {
                $highestId = $id;
            }
        }

        return $highestId;

    }

    static function getUname($id, $usersPath = GV::DIR_USERS) {

        /// Gets all the files in the users directory
        $usersDir = scandir($usersPath);
        /// Removes the first two elements from the array
        // It's because the two first elements are "." and ".."
        $usersDir = array_slice($usersDir, 2);
        /// Removes the index.json
        if (($key = array_search("index.json", $usersDir)) !== false) {
            unset($usersDir[$key]);
        }
        /// Cleans and reindexes the array
        $usersDir = array_values($usersDir);

        foreach($usersDir as $u) {
            $user = json_decode(file_get_contents($usersPath.$u), true);

            if ($user["id"] == $id) {
                return substr($u, 0, -5);
            }
        }

        return FALSE;

    }

    static function userId($login, $usersPath = GV::DIR_USERS) {

        if (in_array(sha1($login).".lt", fileDB::getFilesInDir("login_tokens"))) {
            $id = fileDB::get("login_tokens/".sha1($login).".lt");
        } else {
            http_response_code(400);
            die ("No matching login tokens");
        }

        return $id['userId'];

    }

    static function updateWatch($type, $id, $time, $season = "", $episode = "") {

        $urlType = ($type == "shows") ? "Shows" : "Movies";

        /// Get the URL
        $userId = $GLOBALS['USER']['id'];

        ///
        $lib = fileDB::get('library/'.$userId.'.json', false, false);

        if ($type == "shows") {
            if ($season != "" && $episode != "") {
                $episodeIndex = (String)$season."-".(String)$episode;
                $lib->$type->$id->watched->$episodeIndex = $time;
                fileDB::set('library/', $userId.'.json', json_encode($lib));
            
                return "Updated Show";
            } else {
                http_response_code(400);
                die("Show data is not set");
            }
        } else {
            $lib->$type->$id->watched = $time;
            fileDB::set('library/', $userId.'.json', json_encode($lib));
            
            return "Updated Movie";
        }

    }

    static function titleFromID($id) {

        /// Get the user ID
        $userId = $GLOBALS['USER']['id'];

        ///
        $lib = fileDB::get('library/'.$userId.'.json', false, false);

        return $lib->shows->$id->path;

    }

}