<?php
$dirROOT = "routes/";
$requestMethods = array("GET", "POST", "DELETE", "PUT");
$dirSeperator = "/";

// -----------------------------------------------------------------------------
/// Allow cross site resource sharing
//  The localhost has to be taken away before production!!!
header('Access-Control-Allow-Origin: http://localhost:3000');
//header('Access-Control-Allow-Origin: fluis.org');
header('Access-Control-Allow-Methods: GET, POST, DELETE, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');


/// Include objects from composer
require_once 'vendor/autoload.php';


/// The classes that are required to run this script
// The class that handles the global variables.
require_once('classes/GV.php');
// The class that handles the helper functions.
require_once('classes/Helper.php');
// The class that handles the files in data
require_once('classes/fileDB.php');
// The class that handles the S3 object storage
require_once('classes/S3.php');
// The class that handles the authentication of users
require_once('classes/Auth.php');
// The class that handles the FFMPEG binary
require_once('classes/FFMPEG.php');


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
                    /// Include local objects
                    $USER = NULL;
                    $s3 = NULL;

                    Auth::user($dir);

                    if (isset($USER)) {
                        $s3 = new S3($USER['s3_base'], $USER['s3_bucket'], $USER['s3_region'], $USER['s3_access_key'], $USER['s3_secret_key']);
                    }

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
        $folders = explode($dirSeperator, substr($_SERVER['REQUEST_URI'], 1));

        if (sizeof($folders) == 1) {
            ExecuteRoute($r);
        } else {
            $f = $folders;
            $url = array_pop($f);

            /// Sanitize URL for GET requests
            //  Remove everything but the root
            $surl = explode("?", $url)[0];

            ExecuteRoute($r, $f, $surl);
        }
    }
}
http_response_code(405);
die("This request method is not allowed!");
?>
