#!/usr/bin/php

<?php
$videos = glob("*.mp4");

foreach ($videos as $video) {
	$text = shell_exec("ffmpeg -i \"$video\" 2>&1");
	$matches = array();
	preg_match("/Duration: (\d\d:\d\d:\d\d\.\d\d)/", $text, $matches);
	if ($matches) {
		$name = substr($video, 0, -4);
		$name = $name . "_" . $matches[1] . ".mp4";
		`mv "$video" "$name" `;
	} else {
		print($text);
	}
}

