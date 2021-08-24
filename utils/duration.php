#!/usr/bin/php

<?php 
$minutes = 0;
$seconds = 0;
$count = 0;

$dirs = glob("*", GLOB_ONLYDIR);
foreach ($dirs as $dir) {
    $videos = glob("$dir/*.mp4");
    foreach ($videos as $video) {
      $text = shell_exec("ffmpeg -i \"$video\" 2>&1");
      $matches = array();
      preg_match("/Duration: (\d\d:\d\d:\d\d\.\d\d)/", $text, $matches);
      if ($matches) {
        $times = explode(":", $matches[1]);
        $minutes += $times[1];
        $seconds += floatval($times[2]);
        $count++;
      } else {
        print($text);
      }
    }
}

$minutes += $seconds / 60;
$average = $minutes / $count;
$seconds = $seconds % 60;
$hours = floor($minutes / 60);
$minutes = $minutes % 60;
print("Hours: $hours\n");
print("Minutes: $minutes\n");
print("Seconds: $seconds\n");
print("Video count: $count\n");
print("Average time: $average\n");

?>
