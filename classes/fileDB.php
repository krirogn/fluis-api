<?php
class fileDB {

    /// A general query function
    static function get($path, $root = false) {

      if ($root == false) {
          $path = "data/".$path;
      }

      $string = file_get_contents($path);
      if ($string === false) {
          http_response_code(400);
          die("Couldn't open the file: ".$path);
      }

      $json = json_decode($string, true);
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

    /// A general file deletion function
    static function del($path, $root = false) {

      if ($root == false) {
          $path = "data/".$path;
      }

      return unlink($path);

    }

    /// A general file to see if file exists
    static function fileExists($path, $root = false) {
    
        if ($root == false) {
            $path = "data/".$path;
        }
  
        if (file_exists($path)) {
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

    /// Get's an array of all the usernames
    static function users($usersPath = GV::DIR_USERS) {

        /// Gets all the files in the users directory
        $usersDir = scandir($usersPath);
        /// Removes the first two elements from the array
        // It's because the two first elements are "." and ".."
        $usersDir = array_slice($usersDir, 2);
        /// Removes the index.json
        if (($key = array_search("index.json", $usersDir)) !== false) {
            unset($usersDir[$key]);
        }

        $users = array();
        /// Removes the ".json" from the name
        foreach ($usersDir as $file) {
            array_push($users, substr($file, 0, -5));
        }

        /// Cleans and reindexes the array
        return array_values($users);

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

}