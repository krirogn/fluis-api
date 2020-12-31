<?php
$dirROOT = "routes/";
$requestMethods = array("GET", "POST", "DELETE");
$dirSeperator = "-";

// -----------------------------------------------------------------------------
/// Allow cross site resource sharing
// This has to be taken away before production!!!
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');


/// The classes that are required to run this script
// The class that handles the global variables.
require_once('classes/GV.php');
// The class that handles the files in data
require_once('classes/fileDB.php');


/// Executes the file corresponding to the request route
function ExecuteRoute($dir, $folders = array(), $url = "") {
    global $dirROOT;
    $fileName = "";

    $dir = $dirROOT.$dir.'/';

    if (empty($folders) && $url == "") {
        $fileName = $_GET['url'];
    } else {
        $fileName = $url;

        foreach ($folders as $folder) {
          $dir .= $folder."/";
        }
    }

    if (is_dir($dir)) {
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                $file = substr($file, 0, -4);

                if ($file == $fileName) {

                    include($dir.$file.".php");

                    closedir($dh);
                    die();
                }
            }
            closedir($dh);

            http_response_code(405);
            die("This route does not exit");
        } else {
            http_response_code(405);
            die("Couldn't open the route ROOT folder");
        }
    } else {
        http_response_code(405);
        die("The route ROOT folder does not exist");
    }
}


/// Handles all the requests
foreach ($requestMethods as $r) {
    if ($_SERVER['REQUEST_METHOD'] == $r) {
        $folders = explode($dirSeperator, $_GET['url']);

        if (sizeof($folders) == 1) {
            ExecuteRoute($r);
        } else {
            $f = $folders;
            $url = array_pop($f);

            ExecuteRoute($r, $f, $url);
        }
    }
}
http_response_code(405);
die("This request method is not allowed!");
?>
