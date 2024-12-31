<?php

// turn off warnings
error_reporting(E_ALL ^ E_WARNING);

$courses = glob('*', GLOB_ONLYDIR);
foreach ($courses as $course) {
    if (! preg_match('/^(cs|sd)/', $course)) {
        echo "Unexpected course: {$course}\n";
        exit(1);
    }

    chdir($course);
    echo "Entered {$course}\n";
    $blocks = glob('*', GLOB_ONLYDIR);
    foreach ($blocks as $block) {
        chdir("{$block}/lecture");
        echo "Entered {$block}\n";
        processDays();
        chdir('../../'); // exit block/lecture
    }
    chdir('../'); // exit course
}

function processDays()
{
    $days = glob('*', GLOB_ONLYDIR);
    foreach ($days as $day) {
        if (! preg_match("/^W\dD\d$/", $day)) {
            continue;
        }
        chdir($day);
        echo "Entered {$day}\n";
        $parts = glob('*', GLOB_ONLYDIR);
        foreach ($parts as $part) {
            chdir($part);
            echo "Entered {$part}\n";
            $files = glob('*');
            relinkFiles($files);
            chdir('../');
            $len = strlen($part);
            $state = $part.'_on';
            echo "Rename dir to: $state";
            rename($part, $state);
        }
        chdir('../');
    }
}

function relinkFiles($files)
{
    foreach ($files as $file) {
        if (is_link($file)) {
            $link = readlink($file);
            $dirs = explode('/', $link);
            $dirs[7] = $dirs[7].'_on';
            $link = implode('/', $dirs);
            echo "Relinking to: $link\n";
            unlink($file);
            symlink($link, $file);
        }
    }

}
