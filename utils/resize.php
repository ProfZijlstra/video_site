<?php

mkdir('resized');

// do a directory listing
$dir = opendir('.');
while ($file = readdir($dir)) {
    if (preg_match('/\.jpg$/', $file)) {
        $img = imagecreatefromjpeg($file);
        $resized = imagescale($img, 1280);
        imagejpeg($resized, 'resized/' . $file);
    }
}


?>
