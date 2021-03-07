<?php
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class FFMPEG {

  /// General helper functions
  /// Get the audio language tracks
  static function getAudioLangs($path) {
    $output=null;
    $retval=null;

    $cmd = 'ffprobe -show_entries stream=index,codec_type:stream_tags=language -of compact '.$path.' -v 0 \
            | grep codec_type=audio \
            | grep -o "language=.*" \
            | cut -f2- -d=';

    exec($cmd, $output, $retval);
    
    /// A return value of 0
    //  indicates a success
    if ($retval == 0) {
      return $output;
    } else {
      http_response_code(400);
      die($retval."\n\n".print_r($output));
    }
  }

  /// Get the subtitle language tracks
  static function getCaptionLangs($path) {
    $output=null;
    $retval=null;

    $cmd = 'ffprobe -show_entries stream=index,codec_type:stream_tags=language -of compact '.$path.' -v 0 \
            | grep codec_type=subtitle \
            | grep -o "language=.*" \
            | cut -f2- -d=';

    exec($cmd, $output, $retval);
    
    /// A return value of 0
    //  indicates a success
    if ($retval == 0) {
      return $output;
    } else {
      http_response_code(400);
      die($retval."\n\n".print_r($output));
    }
  }

  /// Get both the audio and subtitle language tracks
  static function getAllLangs($path) {
    $output=null;
    $retval=null;

    $cmd = 'ffprobe -show_entries stream=index,codec_type:stream_tags=language -of compact '.$path.' -v 0 \
            | grep -E "(.*codec_type=audio.*)|(.*codec_type=subtitle.*)"';

    exec($cmd, $output, $retval);

    $audio   = array();
    $caption = array();

    foreach ($output as $u) {
      if (strpos($u, "codec_type=audio") !== false) {
        array_push($audio, end(array_values(explode("=", $u))));
      } elseif (strpos($u, "codec_type=subtitle") !== false) {
        array_push($caption, end(array_values(explode("=", $u))));
      }
    }
    
    /// A return value of 0
    //  indicates a success
    if ($retval == 0) {
      return array('audio' => $audio, 'caption' => $caption);
    } else {
      http_response_code(400);
      die($retval."\n\n".print_r($output));
    }
  }

  /// Get both the audio and subtitle language tracks
  static function getAllLangsWithIndex($path) {
    $output=null;
    $retval=null;

    $cmd = 'ffprobe -show_entries stream=index,codec_type:stream_tags=language -of compact '.$path.' -v 0 \
            | grep -E "(.*codec_type=audio.*)|(.*codec_type=subtitle.*)"';

    exec($cmd, $output, $retval);

    $audio   = array();
    $caption = array();

    foreach ($output as $u) {
      if (strpos($u, "codec_type=audio") !== false) {
        array_push($audio, array(end(array_values(explode("=", $u))) => explode('=', explode('|', $u)[1])[1]));
      } elseif (strpos($u, "codec_type=subtitle") !== false) {
        array_push($caption, array(end(array_values(explode("=", $u))) => explode('=', explode('|', $u)[1])[1]));
      }
    }
    
    /// A return value of 0
    //  indicates a success
    if ($retval == 0) {
      return array('audio' => $audio, 'caption' => $caption);
    } else {
      http_response_code(400);
      die($retval."\n\n".print_r($output));
    }
  }

  /// Gets the audio index from the lang name
  static function audioIndexFromLang($path, $lang) {
    $output=null;
    $retval=null;

    $cmd = 'ffprobe -show_entries stream=index,codec_type:stream_tags=language -of compact '.$path.' -v 0 \
            | grep codec_type=audio';

    exec($cmd, $output, $retval);

    /// A return value of 0
    //  indicates a success
    if ($retval == 0) {
      foreach ($output as $u) {
        if (strpos($u, "codec_type=audio") !== false && strpos($u, "language=".$lang) !== false) {
          return explode('=', explode('|', $u)[1])[1];
        }
      }

      http_response_code(400);
      die("Can't find language");
    } else {
      http_response_code(400);
      die($retval."\n\n".print_r($output));
    }
  }

  /// Gets the caption index from the lang name
  static function captionIndexFromLang($path, $lang) {
    $output=null;
    $retval=null;

    $cmd = 'ffprobe -show_entries stream=index,codec_type:stream_tags=language -of compact '.$path.' -v 0 \
            | grep codec_type=subtitle';

    exec($cmd, $output, $retval);

    /// A return value of 0
    //  indicates a success
    if ($retval == 0) {
      foreach ($output as $u) {
        if (strpos($u, "codec_type=subtitle") !== false && strpos($u, "language=".$lang) !== false) {
          return explode('=', explode('|', $u)[1])[1];
        }
      }

      http_response_code(400);
      die("Can't find language");
    } else {
      http_response_code(400);
      die($retval."\n\n".print_r($output));
    }
  }


  /// Streaming functions
  /// DASH
  static function DASH($path) {
    $cmd = 'ffmpeg -y -re -i '.$path.' \
            -c:v libx264 -x264opts "keyint=24:min-keyint=24:no-scenecut" -r 24 \
            -c:a aac -b:a 128k \
            -bf 1 -b_strategy 0 -sc_threshold 0 -pix_fmt yuv420p \
            -map 0:v:0 -map 0:a:0 -map 0:v:0 -map 0:a:0 -map 0:v:0 -map 0:a:0 \
            -b:v:0 250k  -filter:v:0 "scale=-2:240" -profile:v:0 baseline \
            -b:v:1 750k  -filter:v:1 "scale=-2:480" -profile:v:1 main \
            -b:v:2 1500k -filter:v:2 "scale=-2:720" -profile:v:2 high \
            -use_timeline 1 -use_template 1 -window_size 5 -adaptation_sets "id=0,streams=v id=1,streams=a" \
            -f dash dash/dash.mpd &';
    
    $cmd2 = 'ffmpeg -i '.$path.' -an -c:v copy -b:v 2000k -f dash -window_size 4 -extra_window_size 0 -min_seg_duration 2000000 -remove_at_exit 1 dash/manifest.mpd 2>&1 &';
    
    system($cmd2);

    echo file_get_contents("/var/www/html/dash/manifest.mpd");
  }

}