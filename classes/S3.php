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
            $d = explode('/', $file['Key']);
            if ($d[0] == "Shows" && sizeof($d) >= 3 && $d[1] == $title && !in_array($d[2], $content)) {
                array_push($content, $d[2]);
            }
        }

        return $content;

    }

    public function getSeasonsFromID($login, $id) {

        if (!isset($id)) {
            http_response_code(400);
            die("ID not selected");
        }
        if (!isset($login)) {
            http_response_code(400);
            die("Login not selected");
        }

        /// Get the title from ID
        $title = fileDB::titleFromID($login, $id);

        /// Get the programs
        $response = $this->client->listObjects(array('Bucket' => GV::S3_BUCKET));
        $files = $response->getPath('Contents');

        $content = array();
        foreach ($files as $file) {
            $d = explode('/', $file['Key']);
            if ($d[0] == "Shows" && sizeof($d) >= 3 && $d[1] == $title && !in_array($d[2], $content)) {
                array_push($content, $d[2]);
            }
        }

        return $content;

    }

    public function getEpisodes($title, $season) {

        if (!isset($title) || !isset($season)) {
            http_response_code(400);
            die("Title not selected");
        }

        /// Get the programs
        $response = $this->client->listObjects(array('Bucket' => GV::S3_BUCKET));
        $files = $response->getPath('Contents');

        $content = array();
        foreach ($files as $file) {
            $d = explode('/', $file['Key']);
            if ($d[0] == "Shows" && $d[1] == $title && $d[2] == $season) {
                $e = explode('-', $d[3]);
                array_push($content, $e[0]);
            }
        }

        return $content;

    }

    public function getEpisodesFromID($login, $id, $season) {

        if (!isset($id) || !isset($season)) {
            http_response_code(400);
            die("Title not selected");
        }

        /// Get the title from ID
        $title = fileDB::titleFromID($login, $id);

        /// Get the programs
        $response = $this->client->listObjects(array('Bucket' => GV::S3_BUCKET));
        $files = $response->getPath('Contents');

        $content = array();
        foreach ($files as $file) {
            $d = explode('/', $file['Key']);
            if ($d[0] == "Shows" && $d[1] == $title && $d[2] == $season) {
                $e = explode('-', $d[3]);
                array_push($content, $e[0]);
            }
        }

        return $content;

    }

    public function getMovies() {

        return $this->getTitleQuery("Movies");

    }

    public function getShows() {

        return $this->getTitleQuery("Shows");

    }

    public function getMoviesID($login) {

        return $this->getIDQuery("movies", $login);

    }

    public function getShowsID($login) {

        return $this->getIDQuery("shows", $login);

    }

    public function uploadVideo($fileName, $fileLocation, $id = "") {

        /// Sets the max time to 10m
        set_time_limit(600);

        $result = $this->client->putObject([
			'Bucket' => GV::S3_BUCKET,
			'Key'    => $fileName,
            'SourceFile' => $fileLocation,
            'ACL'    => 'public-read'		
		]);
        
        /// Checks if the upload was successfull
        if (isset($result)) {
            /// Inserts the metadata
            if ($id != "") {
                ///
            }
        }

        
        /// Sets the max time back to 30s
        set_time_limit(30);

    }

    /// 
    public function uploadEpisode($title, $season, $number, $type, $fileLocation, $login, $id = "") {

        /// Sets the max time to 10m
        set_time_limit(600);

        $loc = 'Shows/' . $title . '/' . $season . '/' . $number . '-en' . $type;

        $result = $this->client->putObject([
			'Bucket' => GV::S3_BUCKET,
			'Key'    => $loc,
            'SourceFile' => $fileLocation,
            //'ACL'    => 'public-read'
		]);

        /// Checks if the upload was successfull
        if (isset($result['ObjectURL'])) {
            /// Inserts the metadata
            /// If the ID is new, then you
            //  have to create a new entry.
            if ($id != "") {
                $userId = fileDB::userId($login);
                
                ///
                $lib = fileDB::get('library/'.$userId.'.json', false, false);
                $lib->shows->$id = array(
                    "id" => (String)$id,
                    "path" => $title,
                    "languages" => array(
                        (String)$season."-".(String)$number => array("en")
                    ),
                    "cc" => array(
                        (String)$season."-".(String)$number => array("en")
                    )
                );
                fileDB::set('library/', $userId.'.json', json_encode($lib));
            /// Insert an episode into an entry
            } else {
                $userId = fileDB::userId($login);
                
                ///
                $showID = "";
                $libID = fileDB::get('library/'.$userId.'.json');
                foreach ($libID['shows'] as $sID => $sShow) {
                    if ($sShow['path'] == $title) {
                        $showID = $sShow['id'];
                    }
                }

                ///
                $lib = fileDB::get('library/'.$userId.'.json', false, false);
                $episodeString = (String)$season."-".(String)$number;
                $lib->shows->$showID->languages->$episodeString = array("en");
                $lib->shows->$showID->cc->$episodeString = array("en");
                fileDB::set('library/', $userId.'.json', json_encode($lib));
            }
        }
        
        /// Sets the max time back to 30s
        set_time_limit(30);

    }

    /// 
    public function uploadMovie($title, $type, $fileLocation, $login, $id) {

        /// Sets the max time to 10m
        set_time_limit(600);

        $loc = 'Movies/' . $title . '/main-en' . $type;

        $result = $this->client->putObject([
			'Bucket' => GV::S3_BUCKET,
			'Key'    => $loc,
            'SourceFile' => $fileLocation,
            'ACL'    => 'public-read'
		]);

        /// Checks if the upload was successfull
        if (isset($result['ObjectURL'])) {
            /// Inserts the metadata
            /// If the ID is new, then you
            //  have to create a new entry.
            $userId = fileDB::userId($login);
            
            ///
            $lib = fileDB::get('library/'.$userId.'.json', false, false);
            $lib->movies->$id = array(
                "id" => (String)$id,
                "path" => $title,
                "watched" => 0,
                "languages" => array("en"),
                "cc" => array("en")
            );
            fileDB::set('library/', $userId.'.json', json_encode($lib));
        }
        
        /// Sets the max time back to 30s
        set_time_limit(30);

    }


    /// Private functions

    /// Get the titles from S3
    //  with a specified query
    //  wich corresponds to the name
    //  of the root folder of the title.
    private function getTitleQuery($query) {

        /// Get the programs
        $response = $this->client->listObjects(array('Bucket' => GV::S3_BUCKET));
        $files = $response->getPath('Contents');

        $content = array();
        foreach ($files as $file) {
            $d = explode('/', $file['Key']);
            if ($d[0] == $query && sizeof($d) >= 3 && !in_array($d[1], $content)) {
                array_push($content, $d[1]);
            }
        }

        return $content;

    }

    private function getIDQuery($query, $login) {

        $IDs = array();

        /// 
        $userId = fileDB::userId($login);
        
        $lib = fileDB::get('library/'.$userId.'.json', false, false);
        foreach($lib->$query as $id => $c) {
            array_push($IDs, $id);
        }

        return $IDs;

    }

}