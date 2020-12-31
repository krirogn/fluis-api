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

      unlink($path);

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

}