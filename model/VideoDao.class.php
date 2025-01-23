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
                'parts' => explode('_', basename($latest, '.mp4')),
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

    public function nextIndex($course, $block, $day, $part): string
    {
        // get the index of the last file and add one
        // probably better than counting the files and adding one
        $idx = '01';
        chdir("res/course/{$course}/{$block}/lecture/{$day}/{$part}/");
        $videos = glob('*.mp4');
        if (count($videos) > 0) {
            $last = $videos[count($videos) - 1];
            $parts = explode('_', $last);
            $idx = $parts[0];

            $idx += 1;
            if ($idx < 10) {
                $idx = '0'.$idx;
            }
        }
        chdir('../../../../../../../');

        return $idx;
    }

    public function addVideo($course, $block, $day, $part, $file, $title): bool
    {
        $text = shell_exec("ffprobe \"$file\" 2>&1");
        $matches = [];
        preg_match("/Duration: (\d\d:\d\d:\d\d\.\d\d).*bitrate: (\d+) kb/", $text, $matches);
        if ($matches) {
            $duration = $matches[1];
            $bitrate = $matches[2];
        } else {
            return false;
        }

        $status = '';
        if ($bitrate > 250) {
            $status = 'big';
        }

        // get next index number
        $idx = $this->nextIndex($course, $block, $day, $part);

        // get current timestamp
        $now = new DateTimeImmutable;
        $timeStamp = $now->format('Y-m-d H-i-s');

        // finally move the uploaded file to the right location
        $name = "{$idx}_{$title}_{$timeStamp}_{$duration}_{$status}.mp4";

        chdir("res/course/{$course}/{$block}/lecture/{$day}/{$part}/");
        move_uploaded_file($file, $name);
        chdir('../../../../../../../');

        return true;
    }

    public function reencode($course, $block, $day, $part): bool
    {
        $ch = chdir("res/course/{$course}/{$block}/lecture/{$day}/{$part}/");
        if (! $ch) {
            echo "Could not change directory\n";

            return false;
        }
        $videos = glob('*.mp4');
        $latest = array_pop($videos);
        if (! $latest) {
            echo "No video found\n";

            return false;
        }
        $parts = explode('_', basename($latest, '.mp4'));
        if (count($parts) < 5) {
            echo "Not enough parts\n";

            return false;
        }
        $status = $parts[4];
        if ($status != 'big') {
            echo "Not a high bitrate video\n";

            return false;
        }

        // rename the file to remove the status (stop addtional reencodes)
        $video = "{$parts[0]}_{$parts[1]}_{$parts[2]}_{$parts[3]}_original.mp4";
        $res = rename($latest, $video);
        if (! $res) {
            echo "Could not rename\n";

            return false;
        }

        set_time_limit(0);
        $idx = $parts[0];
        $idx += 1;
        if ($idx < 10) {
            $idx = '0'.$idx;
        }
        $name = "{$idx}_{$parts[1]}_{$parts[2]}_{$parts[3]}.mp4";
        shell_exec("nice ffmpeg -i \"$video\" -vf \"fps=10,scale=1280:720\" -c:v libx264 -preset fast -crf 34 -c:a aac -b:a 96k \"$name\"");

        chdir('../../../../../../../');

        return true;
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
        // TODO: move this into the courseDao
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
}
