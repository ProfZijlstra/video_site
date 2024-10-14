<?php

// chdir("res");
// $courses = glob('*', GLOB_ONLYDIR);
// foreach ($courses as $course) {
//     if (!preg_match("/\w{2}\d{3}/", $course)) {
//         continue;
//     }
    // chdir($course);
    // print("Processing $course\n");

    $blocks = glob('*', GLOB_ONLYDIR);
    foreach ($blocks as $block) {
        if (!preg_match("/^\d{4}-\d{2}$/", $block)) {
            continue;
        }
        chdir($block);
        print("Processing $block\n");

        // To deal with partially migrated courses
        $lectureExists = file_exists("lecture");
        if (!$lectureExists) {
            mkdir("lecture", 0755);
        } else {
            chdir("lecture");
        }
        $days = glob('W*', GLOB_ONLYDIR);
        foreach ($days as $day) {
            print("Processing $day\n");
            chdir("{$day}/vid");
            $videos = glob("*.mp4");
            renameLinks($videos);
            $pdfDir = chdir("../pdf");
            if ($pdfDir) {
                $pdfs = glob("*.pdf");
                renameLinks($pdfs);
            }
            chdir("../..");
            if (!$lectureExists) {
                rename($day, "lecture/{$day}");
            }
        }
        if ($lectureExists) {
            chdir("..");
        }
        chdir("..");
    }

    // chdir("..");
// }
// chdir("..");

function renameLinks($links) {
    foreach ($links as $video) {
        if (!is_link($video)) {
            continue;
        }
        $link = readlink($video);
        $parts = explode("/", $link);
        if (in_array("lecture", $parts)) {
            continue;
        }
        unlink($video);
        array_splice($parts, 4, 0, "lecture");
        array_unshift($parts, "..");
        $link = implode("/", $parts);
        symlink($link, $video);
        print("Renamed $video to $link\n");
    }
}

?>