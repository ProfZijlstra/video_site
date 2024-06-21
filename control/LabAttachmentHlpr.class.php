<?php

/**
 * Attachment Lab Upload Helper Class
 * @author mzijlstra 2024-02-17
 */

#[Controller]
class LabAttachmentHlpr
{
    private static $zero;
    private static $one;

    public function __construct()
    {
        self::$zero = pack("CCC", 0xe2, 0x80, 0x8b); // zero width space
        self::$one = pack("CCC", 0xe2, 0x80, 0x8c); // zero width non-joiner
    }

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
            if ($attachment['type'] === "zip") {
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

    public function readTxtWm($data, $byte) {
        $watermark = substr($data, $byte, 32 * 3);
        $watermark = str_replace(self::$zero, '0', $watermark);
        $watermark = str_replace(self::$one, '1', $watermark);
        return bindec($watermark);
    }

    public function readPngWm($data, $byte) {
        $png = imagecreatefromstring($data);
        $info = getimagesizefromstring($data);
        if (!$png || !$info) {
            return false;
        }
        $width = $info[0];
        $y = floor($byte / $width);
        $x = $byte - $y * $width;

        $out = 0;
        for ($i = 0; $i < 32; $i++) {
            $color = imagecolorat($png, $x, $y);
            $r = ($color >> 16) & 0xFF;
            $out = ($out << 1) | ($r & 1);
            $x++;
            if ($x >= $width) {
                $x = 0;
                $y++;
            }
        }
        return $out;
    }

    public function wmTxt($path, $inzip, $bytepos, $num) 
    {
        $wm = $this->makeTxtWm($num);
        $filename = "{$path}/{$inzip}";
        $contents = file_get_contents($filename);
        $file = fopen($filename, "w");
        fwrite($file, $contents, $bytepos);
        $contents = substr($contents, $bytepos);
        fwrite($file, $wm);
        fwrite($file, $contents);
        fclose($file);
    }

    public function wmPng($path, $file, $byte, $num) 
    {
        $filename = "{$path}/{$file}";
        $watermark = decbin($num);
        while (strlen($watermark) < 32) {
            $watermark = '0' . $watermark;
        }
        $png = imagecreatefrompng($filename);
        $info = getimagesize($filename);
        $width = $info[0];
        $y = floor($byte / $width);
        $x = $byte - $y * $width;

        for ($i = 0; $i < 32; $i++) {
            $color = imagecolorat($png, $x, $y);
            $r = ($color >> 16) & 0xFF;

            if ($watermark[$i] == '1') {
                $r |= 1; // set last bit to 1
            } else {
                $r &= 0xFE; // set last bit to 0
            }
            $color = ($color & 0xFF00FFFF) | ($r << 16);
            imagesetpixel($png, $x, $y, $color);

            $x++;
            if ($x >= $width) {
                $x = 0;
                $y++;
            }
        }

        imagepng($png, $filename);
    }

    private function makeTxtWm($num) 
    {
        $num = decbin($num);
        // pad with zeros to 32 bits
        while (strlen($num) < 32) {
            $num = '0' . $num;
        }
        $num = str_replace('0', self::$zero, $num);
        $num = str_replace('1', self::$one, $num);
        return $num;
    }
}
