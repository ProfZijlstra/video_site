<?php

/**
 * Attachment Lab Upload Helper Class
 * @author mzijlstra 2024-02-17
 */

#[Controller]
class AttachmentHlpr
{

    public function process($key, $lab_id)
    {
        global $URI_PARAMS;

        $course = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];

        // stop if there was an upload error
        if ($_FILES[$key]['error'] != UPLOAD_ERR_OK) {
            return ["error" => "Upload Error"];
        }
        if ($_FILES[$key]['size'] > 10485760) {
            return ["error" => "File too large, 10MB is the maximum"];
        }

        $curr = $_FILES[$key]['tmp_name'];
        $name = $_FILES[$key]['name'];
        $dst = "res/{$course}/{$block}/lab/{$lab_id}/{$name}";
        $this->ensureDirCreated("res/{$course}/{$block}/lab/");
        $this->ensureDirCreated("res/{$course}/{$block}/lab/{$lab_id}");
        move_uploaded_file($curr, $dst);

        return ["dst" => $dst, "name" => $name];
    }

    private function ensureDirCreated($dir)
    {
        if (!file_exists($dir) && !is_dir($dir)) {
            mkdir($dir);
        }
    }

    public function delete($attachment)
    {
        if ($attachment) {
            $file = $attachment['file'];
            unlink($file);
        }
    }
}
