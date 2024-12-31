<?php

/**
 * Video Dao Class
 *
 * @author mzijlstra 01 Oct 2021
 *
 * This isn't a traditional DAO as it gets info from the filesystem
 */
#[Repository]
class VideoDao
{
    public function forOffering($course_num, $block): array
    {
        chdir("res/course/{$course_num}/{$block}/");
        $dirs = glob('*', GLOB_ONLYDIR);
        chdir('../../../../');
        $result = [];
        foreach ($dirs as $dir) {
            $result[$dir] = $this->forDay($course_num, $block, $dir, true);
        }

        return $result;
    }

    public function forDay($course_num, $block, $day): array
    {
        $videos = [];
        $totalDuration = 0;
        $ch = chdir("res/course/{$course_num}/{$block}/lecture/{$day}/");
        $parts = glob('*', GLOB_ONLYDIR);
        foreach ($parts as $part) {
            if (strpos($part, '_') === false) {
                continue;
            }
            $deep = chdir($part);
            $files = glob('*.mp4');
            $latest = array_pop($files);
            if (! $latest) {
                if ($deep) {
                    chdir('..');
                }

                continue;
            }

            $matches = [];
            preg_match("/.*(\d\d):(\d\d):(\d\d)\.(\d\d)\.mp4/", $latest, $matches);
            $hours = $matches[1];
            $minutes = $matches[2];
            $seconds = $matches[3];
            $hundreth = $matches[4];
            // duration in hundreth of a second
            $duration = $hundreth
                + ($seconds * 100)
                + ($minutes * 60 * 100)
                + ($hours * 60 * 60 * 100);
            $totalDuration += $duration;

            $chunks = explode('_', $part);
            $videos[$chunks[0]] = [
                'type' => 'vid',
                'file' => $latest,
                'duration' => $duration,
                'parts' => explode('_', $latest),
            ];

            if ($deep) {
                chdir('../');
            }
        }
        $totalHours = floor($totalDuration / (60 * 60 * 100));
        $totalMinutes = intval($totalDuration / (60 * 100)) % 60;
        $totalSeconds = intval($totalDuration / 100) % 60;
        $totalTime = '';
        if ($totalHours > 0) {
            $totalTime .= $totalHours.':';
        }
        $totalTime .= str_pad($totalMinutes, 2, '0', STR_PAD_LEFT).':';
        $totalTime .= str_pad($totalSeconds, 2, '0', STR_PAD_LEFT);
        if ($ch) {
            chdir('../../../../../../');
        }

        return [
            'videos' => $videos,
            'totalDuration' => $totalDuration,
            'totalTime' => $totalTime,
        ];
    }

    /* Everything below this needs to be updated / fixed to werk with new dirs */

    public function clone($course_number, $block, $old_block): void
    {
        // TODO: this needs to be fixed after the change in vid/pdf directories

        // change directory to where the course materials are and start clone
        chdir("res/course/$course_number");

        // get subdirectory names from old offering
        chdir($old_block.'/lecture');
        $dirs = glob('W*');
        chdir('../..');

        // create new offering
        mkdir($block);
        chdir($block);
        mkdir('lecture');
        chdir('lecture');

        // clone the day of week directories
        foreach ($dirs as $dir) {
            mkdir($dir);
            chdir($dir);
            // make symlinks to previous offering videos
            mkdir('vid');
            // find previoud video files
            if (chdir("../../../{$old_block}/lecture/$dir/vid")) {
                $videos = glob('*.mp4');
                // make links in new vid directory
                chdir("../../../../{$block}/lecture/$dir/vid");
                foreach ($videos as $video) {
                    symlink("../../../../{$old_block}/lecture/$dir/vid/$video", $video);
                }
                chdir('..'); // exit vid dir
            }
            // make symlinks to previous offering pdfs
            mkdir('pdf');
            // find previoud pdf files
            if (chdir("../../../{$old_block}/lecture/$dir/pdf")) {
                $pdfs = glob('*.pdf');
                // make links in new pdf directory
                chdir("../../../../{$block}/lecture/$dir/pdf");
                foreach ($pdfs as $pdf) {
                    symlink("../../../../{$old_block}/lecture/$dir/pdf/$pdf", $pdf);
                }
                chdir('..'); // exit pdf dir
            }
            chdir('..'); // exit day dir
        }
        chdir('../../../../..'); // exit lecture, block, course, res dirs
    }

    public function create($number, $block, $lessonsPerRow, $lessonRows): void
    {
        chdir('res/course');
        mkdir($number);
        chdir($number);
        mkdir($block);
        chdir($block);

        for ($week = 1; $week <= $lessonRows; $week++) {
            for ($day = 1; $day <= $lessonsPerRow; $day++) {
                mkdir("W{$week}D{$day}");
                chdir("W{$week}D{$day}");
                mkdir('vid');
                mkdir('pdf');
                chdir('..');
            }
        }
        chdir('../../../../');
    }

    public function addVideo($course, $block, $day, $tmp, $name): void
    {
        $cwd = getcwd();
        if (! str_ends_with($cwd, "res/course/{$course}/{$block}/{$day}/vid")) {
            mkdir("res/course/{$course}/{$block}/lecture/{$day}/vid/", 0775, true);
            chdir("res/course/{$course}/{$block}/lecture/{$day}/vid/");
        }
        move_uploaded_file($tmp, "$name");
        chdir('../../../../../../../');
    }

    public function nextIndex($course, $block, $day): int
    {
        // get the index of the last file and add one
        // probably better than counting the files and adding one
        chdir("res/course/{$course}/{$block}/lecture/{$day}/vid/");
        $videos = glob('*.mp4');
        if (count($videos) > 0) {
            $last = $videos[count($videos) - 1];
            $parts = explode('_', $last);
            $idx = $parts[0];
            chdir('../../../../../../../');

            return $idx + 1;
        }
        chdir('../../../../../../../');

        return 1;
    }

    public function updateSequence($course, $block, $day, $file, $value): void
    {
        if (! is_numeric($value)) {
            return;
        }
        if ($value < 10) {
            $value = '0'.$value;
        }
        $this->updatePart($course, $block, $day, $file, 0, $value);
    }

    private function updatePart($course, $block, $day, $file, $part, $upd): void
    {
        chdir("res/course/{$course}/{$block}/lecture/{$day}/vid/");
        $parts = explode('_', $file);
        $parts[$part] = $upd;
        $upd = implode('_', $parts);
        rename($file, $upd);
        chdir('../../../../../../../');
    }
}
