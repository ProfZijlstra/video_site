<?php

/**
 * File (resources) upload / download
 *
 * @author mzijlstra 22 oct 2024
 */
#[Controller(path: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/file")]
class FileCtrl
{
    #[Inject('FileDao')]
    public $fileDao;

    #[Inject('OfferingDao')]
    public $offeringDao;

    #[Get(uri: '$', sec: 'student')]
    public function overview(): string
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $course = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];

        $offering = $this->offeringDao->getOfferingByCourse($course, $block);
        // get the files in the base dir
        $dir = 'public';
        if (hasMinAuth('instructor')) {
            $dir = '';
            $norem = ['public', 'lecture', 'quiz', 'lab'];
            $VIEW_DATA['norem'] = $norem;
        }
        $fs = $this->fileDao->getFiles($course, $block, $dir);

        $VIEW_DATA['title'] = 'Files';
        $VIEW_DATA['course'] = $course;
        $VIEW_DATA['block'] = $block;
        $VIEW_DATA['offering'] = $offering;
        $VIEW_DATA['area'] = 'file';
        $VIEW_DATA['parent'] = $dir;
        $VIEW_DATA['dirs'] = $fs['dirs'];
        $VIEW_DATA['files'] = $fs['files'];

        return 'file/overview.php';
    }

    /**
     * Expects AJAX call, will return HTML
     *
     * @return string containing the template file to render
     */
    #[Get(uri: '/dir$', sec: 'student')]
    public function getDir(): string
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $dir = filter_input(INPUT_GET, 'dir');
        if (str_contains($dir, '..')) {
            return 'error/400.php';
        }

        $course = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];

        $fs = $this->fileDao->getFiles($course, $block, $dir);
        $VIEW_DATA['course'] = $course;
        $VIEW_DATA['block'] = $block;
        $VIEW_DATA['parent'] = $dir;
        $VIEW_DATA['dirs'] = $fs['dirs'];
        $VIEW_DATA['files'] = $fs['files'];

        return 'file/listing.php';
    }

    #[Post(uri: '/upload', sec: 'instructor')]
    public function upload(): array
    {
        // stop if there was an upload error
        if ($_FILES['file']['error'] != UPLOAD_ERR_OK) {
            return ['error' => 'Upload Error'];
        }
        if ($_FILES['file']['size'] > 52428800) {
            return ['error' => 'File too large, 50MB is the maximum'];
        }

        // gather all the input data
        global $URI_PARAMS;
        $course = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $location = filter_input(INPUT_POST, 'location');

        $curr = $_FILES['file']['tmp_name'];
        $name = $_FILES['file']['name'];
        $dst = "res/{$course}/{$block}/{$location}/{$name}";
        move_uploaded_file($curr, $dst);

        return ['ok' => $dst];
    }
}