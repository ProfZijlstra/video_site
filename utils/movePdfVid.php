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
        $res = chdir('vid');
        if ($res) {
            $vids = glob('*.mp4');
            copyFiles($vids);
            chdir('../');
        }
        $res = chdir('pdf');
        if ($res) {
            $pdfs = glob('*.pdf');
            copyFiles($pdfs);
            chdir('../');
        }
        chdir('../');
    }
}

function copyFiles($files)
{
    foreach ($files as $file) {
        // get parts
        $parts = explode('_', $file);
        $dir = "{$parts[0]}_".basename($parts[1], '.pdf');
        mkdir("../{$dir}");
        if (is_link($file)) {
            $link = readlink($file);
            $dirs = explode('/', $link);
            $dirs[7] = $dir;
            $link = implode('/', $dirs);
            symlink($link, "../{$dir}/$file");
            echo "Linked {$link}\n";
        } else {
            copy($file, "../{$dir}/$file");
            echo "Copied {$file}\n";
        }
    }
}
