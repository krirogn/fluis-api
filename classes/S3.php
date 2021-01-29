<?php
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

class S3 {

    private $client;

    public function __construct($endpoint = GV::S3_URL) {

        $this->client = S3Client::factory(
            array(
                'endpoint' => $endpoint,
                'bucket_endpoint' => true,
                'version'  => 'latest',
                'region'   => GV::S3_REGION,
                'credentials' => array(
                    'key'     => S3_ACCESS_KEY_ID,
                    'secret'  => S3_SECRET_ACCESS_KEY
                )
            )
        );
    }

    /// 
    public function getDirs() {
        $response = $this->client->listObjects(array('Bucket' => GV::S3_BUCKET));
        $files = $response->getPath('Contents');

        $content = array();
        foreach ($files as $file) {
            if (substr($file['Key'], -1) == '/') {
                array_push($content, $file['Key']);
            }
        }

        return $content;
    }

    /// General function to list contents of bucket
    public function list() {
        $response = $this->client->listObjects(array('Bucket' => GV::S3_BUCKET));
        $files = $response->getPath('Contents');

        $content = array();
        foreach ($files as $file) {
            $isFolder = false;
            if (substr($file['Key'], -1) == '/') {$isFolder = true; }

            array_push($content, array(
                'name' => $file['Key'],
                'type' => ($isFolder) ? 'folder' : 'file'
            ));
        }

        return $content;
    }

    public function move() {
        
    }

    public function del() {

    }

    public function delFolder($dir) {
        $this->client->deleteMatchingObjects(GV::S3_BUCKET, $dir);
    }

    public function getTitles() {

        /// Get the programs
        $response = $this->client->listObjects(array('Bucket' => GV::S3_BUCKET));
        $files = $response->getPath('Contents');

        $currentDir = "";

        $content = array();
        foreach ($files as $file) {
            if (substr($file['Key'], -1) == '/') {
                $d = explode('/', $file['Key']);
                $dir = "";
                for ($i = 0; $i < sizeof($d) - 2; $i++) {
                    $dir .= $d[$i] . '/';
                }

                /// If this is a sub dir
                if ($dir != $currentDir || $currentDir == "") {
                    array_push($content, substr($file['Key'], 0, -1));
                    $currentDir = $file['Key'];
                }
            }
        }

        return $content;

    }

    public function getEntries() {

        /// Get the programs
        $response = $this->client->listObjects(array('Bucket' => GV::S3_BUCKET));
        $files = $response->getPath('Contents');

        $currentDir = "";
        $series = false;

        $entries = "{\n";
        foreach ($files as $file) {
            if (substr($file['Key'], -1) == '/') {
                $d = explode('/', $file['Key']);
                $dir = "";
                for ($i = 0; $i < sizeof($d) - 2; $i++) {
                    $dir .= $d[$i] . '/';
                }

                /// If this is a sub dir
                if ($currentDir != "" && $dir == $currentDir) {

                    if (!$series) {
                        $entries .= "\t\t".'"seasons": '."[\n";
                        $series = true;
                    }

                    $entries .= "\t\t\t".'"'.explode('/', $file['Key'])[count(explode('/', $file['Key']))-2].'",'."\n";
                    
                /// If this is a root dir
                } else {

                    /// If this isn't the first root dir
                    //  that we go through
                    if ($currentDir != "") {
                        if (substr($entries, -2, 1) == ",") {
                            $entries = substr($entries, 0, -2)."\n";
                        }

                        if ($series) {
                            $entries = substr($entries, 0, -1);
                            $entries .= "\n";

                            $entries .= "\t\t]\n";
                        }
                        $entries .= "\t},\n";

                        $series = false;
                    }

                    $entries .= "\t".'"'.substr($file['Key'], 0, -1).'": {'."\n";
                    $entries .= "\t\t".'"conf": "'.GV::S3_URL.'/'.$file['Key'].GV::CONF_NAME.'",'."\n";

                    $currentDir = $file['Key'];

                }
            }
        }

        if (substr($entries, -2, 1) == ",") {
            $entries = substr($entries, 0, -2)."\n";
        }
        if (substr($entries, -2, 1) == "[") {
            $entries = substr($entries, 0, -2);
            $entries = substr($entries, 0, -14);
            $entries .= "\n";
            $entries .= "\t}\n";
        } else {
            $entries .= "\t\t]\n";
            $entries .= "\t}\n";
        }

        $entries .= "}";

        return $entries;

    }


    public function getSeasons($title) {

        if (!isset($title)) {
            http_response_code(400);
            die("Title not selected");
        }

        /// Get the programs
        $response = $this->client->listObjects(array('Bucket' => GV::S3_BUCKET));
        $files = $response->getPath('Contents');

        $content = array();
        foreach ($files as $file) {
            if (substr($file['Key'], -1) == '/') {
                /// Make sure it's a directory
                $d = explode('/', $file['Key']);
                $dir = "";
                for ($i = 0; $i < sizeof($d) - 2; $i++) {
                    $dir .= $d[$i];
                }

                if ($dir == $title) {
                    array_push($content, substr(substr($file['Key'], 0, -1), strlen($dir) + 1));
                }
            }
        }

        return $content;

    }

    public function uploadVideo($fileName, $fileLocation) {

        /// Sets the max time to 10m
        set_time_limit(600);

        $result = $this->client->putObject([
			'Bucket' => GV::S3_BUCKET,
			'Key'    => $fileName,
            'SourceFile' => $fileLocation,
            'ACL'    => 'public-read'		
		]);

        //var_dump($result);
        if (isset($result)) {
            return $fileName;
        } else {
            return FALSE;
        }
        
        /// Sets the max time back to 30s
        set_time_limit(30);

    }

}