<?php

/**
 * Image Quiz Upload Helper Class
 * @author mzijlstra 01/08/2023
 */

#[Controller]
class ImageHlpr
{
    public function save($img, $path)
    {
        // based on https://stackoverflow.com/a/31128229/6933102
        $img = str_replace('data:image/png;base64,', '', $img);
        $img = str_replace(' ', '+', $img);
        $fileData = base64_decode($img);

        $this->ensureDirCreated($path);
        $ext = "png";
        $user_id = $_SESSION['user']['id'];
        $time = new DateTimeImmutable("now", new DateTimeZone(TIMEZONE));
        $ts = $time->format("Y-m-d_H:i:s");
        $dst = $path . "/{$ts}_{$user_id}.{$ext}";
        file_put_contents($dst, $fileData);

        return $dst;
    }

    public function delete($img)
    {
        unlink($img);
    }

    public function process($img_name, $path)
    {
        // stop if there was an upload error
        if ($_FILES[$img_name]['error'] != UPLOAD_ERR_OK) {
            return ["error" => "Upload Error"];
        }

        $img_file = $_FILES[$img_name]['tmp_name'];
        $ext = $this->getExtension($img_file);

        // stop if it wasn't recognized as an image
        if (!$ext) {
            return ["error" => "Not a Recognized Image Format"];
        }

        // move image to quiz location
        $dst = $this->moveImage($img_file, $ext, $path);

        return ["dst" => $dst];
    }

    private function getExtension($img_file)
    {
        $types = array(
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
        );
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        return array_search($finfo->file($img_file), $types, true);
    }

    private function moveImage($img_file, $ext, $path)
    {
        $user_id = $_SESSION['user']['id'];

        // resize the image to width 1024px
        if ($ext == "jpg") {
            $img = imagecreatefromjpeg($img_file);
        } elseif ($ext == "png") {
            $img = imagecreatefrompng($img_file);
        } elseif ($ext == "gif") {
            $img = imagecreatefromgif($img_file);
        }
        $resized = imagescale($img, 1024);

        // then save resized image to quiz location (no move_uploaded_file)
        $this->ensureDirCreated($path);
        $time = new DateTimeImmutable("now", new DateTimeZone(TIMEZONE));
        $ts = $time->format("Y-m-d_H:i:s");
        $dst = $path . "/{$ts}_{$user_id}.{$ext}";
        if ($ext == "jpg") {
            imagejpeg($resized, $dst);
        } elseif ($ext == "png") {
            imagepng($resized, $dst);
        } elseif ($ext == "gif") {
            imagegif($resized, $dst);
        }

        // remove the original image
        unlink($img_file);

        return $dst;
    }

    private function ensureDirCreated($dir)
    {
        if (!file_exists($dir) && !is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }
}
