#!/usr/bin/php

<?php
$dirs = glob("*", GLOB_ONLYDIR);

foreach ($dirs as $dir) {
    chdir($dir);
    $files = glob("*");
    foreach ($files as $file) {
        $matches = [];
        preg_match('/^(\d{2}) (.+)$/', $file, $matches);
        if ($matches) {
            $name = $matches[1] . "_" . $matches[2];
            rename($file, $name);
        }
    }
    chdir("..");
}