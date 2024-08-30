<?php

/**
 * Video Dao Class
 * @author mzijlstra 01 Oct 2021
 * 
 * This isn't a traditional DAO as it gets info from the filesystem 
 */

#[Repository]
class VideoDao
{

    public function forOffering($course_num, $block)
    {
        chdir("res/{$course_num}/{$block}/");
        $dirs = glob('*', GLOB_ONLYDIR);
        chdir("../../../");
        $result = array();
        foreach ($dirs as $dir) {
            $result[$dir] = $this->forDay($course_num, $block, $dir, true);
        }
        return $result;
    }

    public function forDay($course_num, $block, $day, $named = true)
    {
        chdir("res/{$course_num}/{$block}/{$day}/vid/");
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
            $data = array();
            $data["type"] = "vid";
            $data["file"] = $file;
            $data["duration"] = $duration;
            $data["parts"] = $parts;
            if ($named) {
                $file_info["{$parts[0]}_{$parts[1]}"] = $data;
            } else {
                $data["name"] = "{$parts[0]}_{$parts[1]}";
                $file_info[] = $data;
            }
        }
        $totalHours = floor($totalDuration / (60 * 60 * 100));
        $totalMinutes = intval($totalDuration / (60 * 100)) % 60;
        $totalSeconds = intval($totalDuration / 100) % 60;
        $totalTime = "";
        if ($totalHours > 0) {
            $totalTime .= $totalHours . ":";
        }
        $totalTime .= str_pad($totalMinutes, 2, "0", STR_PAD_LEFT) . ":";
        $totalTime .= str_pad($totalSeconds, 2, "0", STR_PAD_LEFT);
        chdir("../../../../../");
        return array(
            "file_info" => $file_info,
            "totalDuration" => $totalDuration,
            "totalTime" => $totalTime,
        );
    }

    public function duration($course_num, $block, $day, $video)
    {
        chdir("res/{$course_num}/{$block}/{$day}/vid/");
        $files = glob("*.mp4");
        foreach ($files as $file) {
            $matches = array();
            if (preg_match("/{$video}.*(\d\d):(\d\d):(\d\d)\.(\d\d)\.mp4/", $file, $matches)) {
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

    public function clone($course_number, $block, $old_block)
    {
        // change directory to where the course materials are and start clone
        chdir("res/$course_number");

        // get subdirectory names from old offering
        chdir($old_block);
        $dirs = glob("W*");
        chdir("..");

        // create new offering
        mkdir($block);
        chdir($block);

        // clone the day of week directories
        foreach ($dirs as $dir) {
            mkdir($dir);
            chdir($dir);
            // make symlinks to previous offering videos
            mkdir("vid");
            // find previoud video files
            if (chdir("../../{$old_block}/$dir/vid")) {
                $videos = glob("*.mp4");
                // make links in new vid directory
                chdir("../../../{$block}/$dir/vid");
                foreach ($videos as $video) {
                    symlink("../../../{$old_block}/$dir/vid/$video", $video);
                }
                chdir(".."); // exit vid dir
            }
            // make symlinks to previous offering pdfs
            mkdir("pdf");
            // find previoud pdf files
            if (chdir("../../{$old_block}/$dir/pdf")) {
                $pdfs = glob("*.pdf");
                // make links in new pdf directory
                chdir("../../../{$block}/$dir/pdf");
                foreach ($pdfs as $pdf) {
                    symlink("../../../{$old_block}/$dir/pdf/$pdf", $pdf);
                }
                chdir(".."); // exit pdf dir    
            }
            chdir(".."); // exit day dir
        }
    }

    public function create($number, $block, $lessonsPerRow, $lessonRows)
    {
        chdir("res");
        mkdir($number);
        chdir($number);
        mkdir($block);
        chdir($block);

        for ($week = 1; $week <= $lessonRows; $week++) {
            for ($day = 1; $day <= $lessonsPerRow; $day++) {
                mkdir("W{$week}D{$day}");
                chdir("W{$week}D{$day}");
                mkdir("vid");
                mkdir("pdf");
                chdir("..");
            }
        }
    }

    public function addVideo($course, $block, $day, $tmp, $name)
    {
        $cwd = getcwd();
        if (!str_ends_with($cwd, "res/$course/$block/$day/vid")) {
            mkdir("res/$course/$block/$day/vid/", 0775, true);
            chdir("res/$course/$block/$day/vid/");
        }
        move_uploaded_file($tmp, "$name");
    }

    public function nextIndex($course, $block, $day)
    {
        // get the index of the last file and add one
        // probably better than counting the files and adding one
        chdir("res/$course/$block/$day/vid/");
        $videos = glob("*.mp4");
        if (count($videos) > 0) {
            $last = $videos[count($videos) - 1];
            $parts = explode("_", $last);
            $idx = $parts[0];
            return $idx + 1;
        }
        return 1;
    }

    public function updateTitle($course, $block, $day, $file, $title)
    {
        $this->updatePart($course, $block, $day, $file, 1, $title);
    }

    public function updateSequence($course, $block, $day, $file, $value)
    {
        if (!is_numeric($value)) {
            return;
        }
        if ($value < 10) {
            $value = "0" . $value;
        }
        $this->updatePart($course, $block, $day, $file, 0, $value);
    }

    private function updatePart($course, $block, $day, $file, $part, $upd)
    {
        chdir("res/$course/$block/$day/vid/");
        $parts = explode("_", $file);
        $parts[$part] = $upd;
        $upd = implode("_", $parts);
        rename($file, $upd);
    }
}

