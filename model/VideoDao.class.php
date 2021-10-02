<?php

/**
 * Video Dao Class
 * @author mzijlstra 01 Oct 2021
 * @Repository
 * 
 * This isn't a traditional DAO as it gets info from the filesystem 
 */
class VideoDao {

	public function forOffering($course_num, $block) {
		chdir("res/${course_num}/${block}/");
		$dirs = glob('*', GLOB_ONLYDIR);
		chdir("../../../");
		$result = array();
		foreach ($dirs as $dir) {
			$result[$dir] = $this->forDay($course_num, $block, $dir);
		}
		return $result;
	}

    public function forDay($course_num, $block, $day) {
		chdir("res/${course_num}/${block}/${day}/vid/");
		$files = glob("*.mp4");
		$file_info = array();
		$totalDuration = 0;
		foreach ($files as $file) {
			$matches = array();
			preg_match("/.*(\d\d):(\d\d):(\d\d)\.(\d\d)\.mp4/", $file, $matches);
			$hours = $matches[1];
			$minutes = $matches[2];
			$seconds = $matches[3];
			$hundreth = $matches[4];
			// duration in hundreth of a second
			$duration = $hundreth + ($seconds * 100) + ($minutes * 60 * 100) + ($hours * 60 * 60 * 100);
			$totalDuration += $duration;
			$parts = explode("_", $file);
			$file_info["${parts[0]}_${parts[1]}"] = array();
			$file_info["${parts[0]}_${parts[1]}"]["file"] = $file;
			$file_info["${parts[0]}_${parts[1]}"]["duration"] = $duration;
			$file_info["${parts[0]}_${parts[1]}"]["parts"] = $parts;
		}
		$totalHours = floor($totalDuration / (60 * 60 * 100));
		$totalMinutes = floor($totalDuration / (60*100) % 60);
		$totalSeconds = floor($totalDuration / 100 % 60);
		$totalTime = "";
		if ($totalHours > 0) {
			$totalTime .= $totalHours . ":";
		}
		$totalTime .= str_pad($totalMinutes, 2, "0", STR_PAD_LEFT) . ":";
		$totalTime .= str_pad($totalSeconds, 2, "0", STR_PAD_LEFT);
        chdir("../../../../../");
        return array("file_info" => $file_info, 
                    "totalDuration" => $totalDuration, 
                    "totalTime" => $totalTime, 
                );
    }
}