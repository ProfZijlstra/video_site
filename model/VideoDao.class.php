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

	public function duration($course_num, $block, $day, $video) {
		chdir("res/${course_num}/${block}/${day}/vid/");
		$files = glob("*.mp4");
		foreach ($files as $file) {
			$matches = array();
			if (preg_match("/${video}.*(\d\d):(\d\d):(\d\d)\.(\d\d)\.mp4/", $file, $matches)) {
				$hours = $matches[1];
				$minutes = $matches[2];
				$seconds = $matches[3];
				// duration in seconds 
				return $seconds + ($minutes * 60) + ($hours * 60 * 60);
			}
		}
		// if not found return negative
		return -1;
	}

	public function clone($course_number, $block, $old_block) {
		// change directory to where the course materials are and start clone
		chdir("res/$course_number");
		mkdir($block);
		chdir($block);

		// clone the day of week directories
		for ($week = 1; $week < 5; $week++) {
			for ($day = 1; $day < 7; $day++) { // we don't make sunday dirs
				mkdir("W${week}D${day}");
				chdir("W${week}D${day}");
				// make symlinks to previous offering videos
				mkdir("vid");
				// find previoud video files
				if (chdir("../../${old_block}/W${week}D${day}/vid")) {
					$videos = glob("*.mp4");
					// make links in new vid directory
					chdir("../../../${block}/W${week}D${day}/vid");
					foreach ($videos as $video) {
						symlink("../../../${old_block}/W${week}D${day}/vid/$video", $video);
					}    
					chdir(".."); // exit vid dir
				}
				// make symlinks to previous offering pdfs
				mkdir("pdf");
				// find previoud pdf files
				if (chdir("../../${old_block}/W${week}D${day}/pdf")) {
					$pdfs = glob("*.pdf");
					// make links in new pdf directory
					chdir("../../../${block}/W${week}D${day}/pdf");        
					foreach ($pdfs as $pdf) {
						symlink("../../../${old_block}/W${week}D${day}/pdf/$pdf", $pdf);
					}
					chdir(".."); // exit pdf dir    
				}
				chdir(".."); // exit day dir
			}
		}
	}

	public function create($number, $block, $lessonsPerRow, $lessonRows) {
		chdir("res");
		mkdir($number);
		chdir($number);
		mkdir($block);
		chdir($block);

		for ($week = 1; $week <= $lessonRows; $week++) {
			for ($day = 1; $day <= $lessonsPerRow; $day++) {
				mkdir("W${week}D${day}");
				chdir("W${week}D${day}");
				mkdir("vid");
				mkdir("pdf");
				chdir("..");
			}
		}
	}
}