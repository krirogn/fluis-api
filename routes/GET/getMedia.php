<?php
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

$s3Client = S3Client::factory(
    array(
        'endpoint' => 'https://fluis-media.s3.fr-par.scw.cloud',
        'bucket_endpoint' => true,
        'version'  => 'latest',
        'region'   => 'fr-par',
        'credentials' => array(
            'key'     => getenv("S3_ACCESS_KEY_ID"),
            'secret'  => getenv("S3_SECRET_ACCESS_KEY"),
        )
    )
);

$response = $s3Client->listObjects(array('Bucket' => 'fluis'));
$files = $response->getPath('Contents');
foreach ($files as $file) {
    $isFolder = false;
    if (substr($file['Key'], -1) == '/') {$isFolder = true; }
    //echo $isFolder."  ".substr($file['Key'], -1)."\n";

    $filename = $file['Key'];
    echo ($isFolder) ? "Folder:". $file['Key'] : "Filename:". $file['Key'];
    echo "\n\n";
}

http_response_code(200);
exit("H");