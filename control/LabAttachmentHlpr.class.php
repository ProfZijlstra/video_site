<?php

/**
 * Attachment Lab Upload Helper Class
 * @author mzijlstra 2024-02-17
 */

#[Controller]
class LabAttachmentHlpr
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
        $dst = "res/{$course}/{$block}/lab/{$lab_id}/";
        $zip = false;
        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if ($extension == "zip" && $this->isZipFile($curr)) {
            $zip = true;
            $dst .= "upload/";
        } else {
            $dst .= "attachment/";
        }
        $this->ensureDirCreated($dst);
        $dst .= $name;
        move_uploaded_file($curr, $dst);

        return ["file" => $dst, "name" => $name, "lab_id" => $lab_id, "zip" => $zip];
    }

    public function extract($attachment)
    {
        $aid = $attachment['id'];
        $dir = sys_get_temp_dir();
        $dir .= "/lmz/unzip/{$aid}/";
        if (file_exists($dir) && is_dir($dir)) {
            print("No need to unzip\n");
            return; // already extracted
        }

        $this->ensureDirCreated($dir);
        $zip = new ZipArchive();
        if ($zip->open($attachment['file']) === TRUE) {
            $zip->extractTo($dir);
            $zip->close();
        } else {
            throw new Exception("Failed to extract zip file");
        }
    }

    public function delete($attachment)
    {
        if ($attachment) {
            $file = $attachment['file'];
            unlink($file);
            if (
                $attachment['type'] === "lab zip"
                || $attachment['type'] === "deliv zip"
            ) {
                $dir = dirname(dirname($file));
                $dir .= "download/{$attachment['id']}/";
                shell_exec("rm -rf {$dir}");
            }
        }
    }

    // from: https://stackoverflow.com/questions/9098678/
    public function isZipFile($path)
    {
        $fh = fopen($path, 'r');
        $bytes = fread($fh, 4);
        fclose($fh);
        // ZIP file magic number is PK\003\004
        return ('504b0304' === bin2hex($bytes));
    }

    public function isPdfFile($path)
    {
        $fh = fopen($path, 'r');
        $bytes = fread($fh, 4);
        fclose($fh);
        return ($bytes === "%PDF");
    }

    public function ensureDirCreated($dir)
    {
        if (!file_exists($dir) && !is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }
}
