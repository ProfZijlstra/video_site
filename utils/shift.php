<?php

$inc = 1;
if ($argc == 2) {
    $inc = intval($argv[1]);
}
print("Using inc: $inc\n");

$files = glob("*");
foreach ($files as $file) {
    $matches = [];
    if (preg_match("/^(\d{2})(_.*\.(mp4|pdf))$/", $file, $matches)) {
        $next = $matches[1] + $inc;
        $next = str_pad($next, 2, "0", STR_PAD_LEFT);
        rename($file, $next . $matches[2]);
    }
}

?>