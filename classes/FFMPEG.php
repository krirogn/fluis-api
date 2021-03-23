<?php
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class FFMPEG {

  /// General helper functions
  /// Get the audio language tracks
  static function getAudioLangs($path) {

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

    $output = null;
    $retval = null;

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

    $output = null;
    $retval = null;

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
      $audio = Helper::array_flatten($audio);
      $caption = Helper::array_flatten($caption);
      
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


  static function extractAudioTrack($source, $index, $o) {

    $cmd = 'ffmpeg -y -i '.$source.' -map 0:'.$index.' -ac 2 -ab 192k -vn -sn '.$o.' 2>&1';

    exec($cmd, $output, $retval);

    /// A return value of 0
    //  indicates a success
    if ($retval == 0) {
      if (fileDB::fileExists($o, true)) {
        return true;
      } else {
        die("FILE IS NOT EXIST");
      }
    } else {
      http_response_code(400);
      die("\n------\nERROR: ".$retval."\nOUTPUT: ".print_r($output)."\n");
    }

  }


  static function stripVideo($source, $output) {

    $cmd = 'ffmpeg -i '.$source.' -map 0:v -c copy '.$output;

    exec($cmd, $output, $retval);

    /// A return value of 0
    //  indicates a success
    if ($retval == 0) {
      if (fileDB::fileExists($output, true)) {
        return true;
      } else {
        die("FILE IS NOT EXIST");
      }
    } else {
      http_response_code(400);
      die("\n------\nERROR: ".$retval."\nOUTPUT: ".print_r($output)."\n");
    }

  }


  /// 
  static function checkSubFormat($source, $index) {

    $cmd = 'mediainfo "--Output=Text;%ID% %Format% %Language/String%\n" '.$source.' 2>&1';

    exec($cmd, $output, $retval);

    /// A return value of 0
    //  indicates a success
    if ($retval == 0) {
      foreach ($output as $o) {
        if (explode(' ', $o)[0] == $index + 1) {
          return explode(' ', $o)[1];
        }
      }
    } else {
      http_response_code(400);
      die("\n------\nERROR: ".$retval."\nOUTPUT: ".print_r($output)."\n");
    }

  }


  static function extractVobsub($source, $lang, $index, $o) {

    $rm = 'rm '.$o.'.idx || true && rm '.$o.'.sub || true';
    exec($rm, $out, $ret);
    if ($ret != 0) {
      http_response_code(500);
      die("\n------\nERROR: ".$ret."\nOUTPUT: ".print_r($out)."\n");
    }

    $iso = new Matriphe\ISO639\ISO639;
    $langCode = $iso->code1ByLanguage($iso->languageByCode2t($lang));
    $cmd = 'mencoder '.$source.' -ifo '.$o.'.ifo -vobsubout '.$o.' -vobsuboutindex '.$index.' \
            -vobsuboutid '.$langCode.' -sid '.$index.' -nosound -ovc copy -o /dev/null 2>&1';

    exec($cmd, $output, $retval);

    /// A return value of 0
    //  indicates a success
    if ($retval == 0) {
      if (fileDB::fileExists($o.".sub", true)) {
        if (fileDB::fileExists($o.".idx", true)) {
          return true;
        } else {
          die(".idx FILE IS NOT EXIST");
        }
      } else {
        die(".sub FILE IS NOT EXIST");
      }
    } else {
      http_response_code(400);
      die("\n------\nERROR: ".$retval."\nOUTPUT: ".print_r($output)."\n");
    }

  }


  /// Use MEncoder to extract vobsub from MPEG files,
  //  then use VobSub2SRT to convert them to webvtt files. 
  static function vobsub2srt($source, $lang) {

    $iso = new Matriphe\ISO639\ISO639;
    $langCode = $iso->code1ByLanguage($iso->languageByCode2t($lang));
    $cmd = 'vobsub2srt '.$source.' --lang '.$langCode.' 2>&1';

    echo($cmd."\n");
    ob_flush();

    exec($cmd, $output, $retval);

    /// A return value of 0
    //  indicates a success
    if ($retval == 0) {
      if (fileDB::fileExists($source.".srt", true)) {
        return true;
      } else {
        die($source.".srt\nFILE IS NOT EXIST");
      }
    } else {
      http_response_code(400);
      die("\n------\nERROR: ".$retval."\nOUTPUT: ".print_r($output)."\n");
    }

  }


  static function srt2vtt($source, $output) {

    if (!fileDB::fileExists($source, true)) {
      die("INPUT DOES NOT EXIST");
    }

    $cmd = 'ffmpeg -i '.$source.' '.$output.' 2>&1';

    exec($cmd, $output, $retval);

    /// A return value of 0
    //  indicates a success
    if ($retval == 0) {
      if (fileDB::fileExists($source, true)) {
        return true;
      } else {
        die($source."\nFILE IS NOT EXIST");
      }
    } else {
      http_response_code(400);
      die("\n------\nERROR: ".$retval."\nOUTPUT: ".print_r($output)."\n");
    }

  }

}