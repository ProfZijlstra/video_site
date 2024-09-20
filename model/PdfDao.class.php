<?php

/**
 * Video Dao Class
 * @author mzijlstra 30 Aug 2024
 * 
 * This isn't a traditional DAO as it gets info from the filesystem 
 */

#[Repository]
class PdfDao {
    public function forDay($course_num, $block ,$day) {
        chdir("res/{$course_num}/{$block}/{$day}/pdf/");
        $files = glob("*.[pP][dD][fF]"); // pdf files
        $result = array();
        foreach ($files as $file) {
            $info = pathinfo($file);
            $parts = explode("_", $info['filename']);
            $result[$parts[0]] = [
                "type" => "pdf",
                "file" => $file,
                "duration" => 0,
                "parts" => explode("_", $info['filename'])
            ];
        }
        chdir("../../../../../");
        return $result;
    }
}
