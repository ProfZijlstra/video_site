<?php

/**
 * Image Quiz Upload Helper Class
 * @author mzijlstra 01/08/2023
 * 
 * @Controller
 */
class ImageHlpr {

    public function process($img_name, $question_id, $user_id) {
        // stop if there was an upload error
        if ($_FILES[$img_name]['error'] != UPLOAD_ERR_OK) {
            return [ "error" => "Upload Error" ];
        }

        $img_file = $_FILES[$img_name]['tmp_name'];
        $ext = $this->getExtension($img_file);

        // stop if it wasn't recognized as an image
        if (!$ext) {
            return [ "error" => "Not a Recognized Image Format" ];
        }

        // move image to quiz location
        $dst = $this->moveImage($img_file, $ext, $question_id, $user_id);

        return [ "dst" => $dst ];
    }

    private function getExtension($img_file) {
        $types = array(
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
        );
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        return array_search($finfo->file($img_file), $types, true);
    }

    private function moveImage($img_file, $ext, $question_id, $user_id) {
        global $URI_PARAMS;

        $course = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];

        $time = new DateTimeImmutable("now", new DateTimeZone(TIMEZONE));
        $ts = $time->format("Y-m-d_H:i:s");
        $dst = "res/{$course}/{$block}/quiz/{$question_id}/{$ts}_{$user_id}.{$ext}";
        $this->ensureDirCreated("res/{$course}/{$block}/quiz/");
        $this->ensureDirCreated("res/{$course}/{$block}/quiz/{$question_id}");
        move_uploaded_file($img_file, $dst);

        return $dst;
    }

    private function ensureDirCreated($dir) {
        if (!file_exists( $dir ) && !is_dir( $dir )) {
            $made = mkdir($dir);
        } 
    }

}

?>