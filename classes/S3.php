<?php
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

class S3 {

    /// S3 data for function
    //  The URL to the S3 site
    private $base;
    //  The name of the bucket
    private $bucket;

    /// The S3 client object
    private $client;

    public function __construct($endpoint, $bucket, $region, $accessKey, $secretKey) {

        $this->client = S3Client::factory(
            array(
                'endpoint' => $endpoint,
                'bucket_endpoint' => true,
                'version'  => 'latest',
                'region'   => $region,
                'credentials' => array(
                    'key'     => $accessKey,
                    'secret'  => $secretKey
                )
            )
        );

        $this->base = $endpoint;
        $this->bucket = $bucket;

    }

    /// 
    public function getDirs() {

        $response = $this->client->listObjects(array('Bucket' => $this->bucket));
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

        $response = $this->client->listObjects(array('Bucket' => $this->bucket));
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

    public function del($o) {

        $this->client->deleteMatchingObjects($this->bucket, $o);

    }

    public function uploadObjects(array $o) {



    }



    public function getEpisodes($title, $season) {

        if (!isset($title) || !isset($season)) {
            http_response_code(400);
            die("Title not selected");
        }

        /// Get the programs
        $response = $this->client->listObjects(array('Bucket' => $this->bucket));
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

    public function getEpisodesFromID($id, $season) {

        if (!isset($id) || !isset($season)) {
            http_response_code(400);
            die("Title not selected");
        }

        /// Get the title from ID
        $title = fileDB::titleFromID($id);

        /// Get the programs
        $response = $this->client->listObjects(array('Bucket' => $this->bucket));
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

    public function getSeasonsFromID($id) {
        if (!isset($id)) {
            http_response_code(400);
            die("ID not selected");
        }
        
        /// Get the title from ID
        $title = fileDB::titleFromID($id);
        /// Get the programs
        $response = $this->client->listObjects(array('Bucket' => $this->bucket));
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


    public function getMovies() {

        return $this->getTitleQuery("Movies");

    }

    public function getShows() {

        return $this->getTitleQuery("Shows");

    }

    public function getMoviesID() {

        return $this->getIDQuery("movies");

    }

    public function getShowsID() {

        return $this->getIDQuery("shows");

    }



    /// 
    public function uploadEpisode($title, $season, $number, $type, $fileLocation, $id = "") {

        /// Sets the max time to 10m
        set_time_limit(600);

        $loc = (String)$GLOBALS['USER']['id'] . '/Shows/' . $title . '/' . $season . '/' . $number . '-en' . $type;

        $result = $this->client->putObject([
			'Bucket' => $this->bucket,
			'Key'    => $loc,
            'SourceFile' => $fileLocation,
            'ACL'    => 'public-read'
		]);

        /// Checks if the upload was successfull
        if (isset($result['ObjectURL'])) {
            /// Inserts the metadata
            /// If the ID is new, then you
            //  have to create a new entry.
            if ($id != "") {
                $userId = $GLOBALS['USER']['id'];
                
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
                $userId = $GLOBALS['USER']['id'];
                
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
    public function uploadMovie($title, $type, $fileLocation, $id) {

        /// Sets the max time to 20m
        set_time_limit(1200);

        $loc = (String)$GLOBALS['USER']['id'] . '/Movies/' . $title . '/main-en' . $type;


        
        /// Transcode the video
        /// Get the metadata
        $meta = FFMPEG::getAllLangsWithIndex($fileLocation);
        //die(json_encode($meta, JSON_UNESCAPED_SLASHES));
        $audioTracks   = $meta['audio'];
        $captionTracks = $meta['caption'];

        // The list of finished files
        $audioFiles;
        $captionFiles;
        $videoFile;

        /// Extract the audio
        foreach ($audioTracks as $a) {
            $aLoc = "tmp/audio-".$a[0].".mp4";
            FFMPEG::extractTrack($fileLocation, $a[1], $aLoc);
            array_push($audioFiles, $aLoc);
        }
        die(json_encode($audioFiles, JSON_UNESCAPED_SLASHES));


        /// Checks if the subtitles are
        //  in bitmap format.
        if (false) {
            /// Use MEncoder to extract VobSub file(s)

            /// Use VobSub2SRT to convert the VobSub file(s) to (Web)VTT


        // If the subtitles are text based
        } else {
            /// Use FFMPEG to extract them as (Web)VTT

        }

        /// Strip down to only video


        die();
        $result = $this->client->putObject([
			'Bucket' => $this->bucket,
			'Key'    => $loc,
			'SourceFile' => $fileLocation,
			'ACL'    => 'public-read'
		]);

        /// Checks if the upload was successfull
        if (isset($result['ObjectURL'])) {
            /// Inserts the metadata
            /// If the ID is new, then you
            //  have to create a new entry.
            $userId = $GLOBALS['USER']['id'];
            
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
        $response = $this->client->listObjects(array('Bucket' => $this->bucket));
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

    private function getIDQuery($query) {

        $IDs = array();

        /// 
        $userId = $GLOBALS['USER']['id'];
        
        $lib = fileDB::get('library/'.$userId.'.json', false, false);
        foreach($lib->$query as $id => $c) {
            array_push($IDs, $id);
        }

        return $IDs;

    }

}