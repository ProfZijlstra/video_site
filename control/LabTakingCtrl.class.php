<?php

/**
 * Lab Taking Controller
 * @author mzijlstra 29 Feb 2024
 */

#[Controller(path: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/lab")]
class LabTakingCtrl
{
    #[Inject('LabDao')]
    public $labDao;

    #[Inject('OfferingDao')]
    public $offeringDao;

    #[Inject('EnrollmentDao')]
    public $enrollmentDao;

    #[Inject('SubmissionDao')]
    public $submissionDao;

    #[Inject('DeliverableDao')]
    public $deliverableDao;

    #[Inject('DeliveryDao')]
    public $deliveryDao;

    #[Inject('AttachmentDao')]
    public $attachmentDao;

    #[Inject('DownloadDao')]
    public $downloadDao;

    #[Inject('MarkdownHlpr')]
    public $markdownHlpr;

    #[Inject('ImageHlpr')]
    public $imageHlpr;

    #[Inject('LabAttachmentHlpr')]
    public $labAttachmentHlpr;

    /**
     * This function is really a 3 in one. 
     * 1. If it is used before the start time it shows a countdown timer
     * 2. If it is used after the stop time it shows a status for each deliverable
     * 3. If between start and stop the user can upload deliverables
     * 
     */
    #[Get(uri: "/(\d+)$", sec: "student")]
    public function viewLab()
    {
        global $URI_PARAMS;
        global $VIEW_DATA;


        $course = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $lab_id = $URI_PARAMS[3];

        $lab = $this->labDao->byId($lab_id);

        $tz = new DateTimeZone(TIMEZONE);
        $now = new DateTimeImmutable("now", $tz);
        $start = new DateTimeImmutable($lab['start'], $tz);
        $stop = new DateTimeImmutable($lab['stop'], $tz);

        $startDiff = $now->diff($start);
        $stopDiff = $now->diff($stop);

        $user_id = $_SESSION['user']['id'];
        $VIEW_DATA['course'] = $course;
        $VIEW_DATA['block'] = $block;
        $VIEW_DATA['lab'] = $lab;

        // lab is not open yet
        if ($startDiff->invert === 0) { // start is in the future
            // show countdown page
            $VIEW_DATA['title'] = "Lab Countdown";
            $VIEW_DATA['start'] = $startDiff;
            return "lab/countdown.php";
        }

        if ($lab['type'] == "group") {
            // get the group
            $enroll = $this->enrollmentDao->getEnrollment($user_id, $course, $block);
            $group = $enroll['group'] ?? null;
            if (!$group && $enroll['auth'] == 'instructor') {
                $group = 'instructor';
            }

            if ($group == null) {
                $VIEW_DATA['title'] = "No Group";
                return "lab/nogroup.php";
            }

            // get the submission (or null)
            $submission = $this->submissionDao->forGroup($group, $lab_id);
        } else { // type == "individual"
            $submission = $this->submissionDao->forUser($user_id, $lab_id);
        }

        $deliveries = [];
        if ($submission) {
            $deliveries = $this->deliveryDao->forSubmission($submission['id']);
        }

        $deliverables = $this->deliverableDao->forLab($lab_id);
        $labPoints = 0;
        foreach ($deliverables as $deliv) {
            $labPoints += $deliv['points'];
        }
        $typeDesc = [
            'txt' => 'Type text into the textbox',
            'img' => 'Upload an image',
            'pdf' => 'Upload a .pdf file',
            'url' => 'Write a URL in the text field',
            'zip' => 'Upload a .zip file',
        ];

        require_once("lib/Parsedown.php");
        $VIEW_DATA['stop'] = $stopDiff;
        $VIEW_DATA['group'] = $group;
        $VIEW_DATA['labPoints'] = $labPoints;
        $VIEW_DATA["parsedown"] = new Parsedown();
        $VIEW_DATA['attachments'] = $this->attachmentDao->forLab($lab_id);
        $VIEW_DATA['typeDesc'] = $typeDesc;
        $VIEW_DATA['deliverables'] = $deliverables;
        $VIEW_DATA['submission'] = $submission;
        $VIEW_DATA['deliveries'] = $deliveries;

        // lab is done / over
        if ($stopDiff->invert === 1) { // stop is in the past
            $received = 0;
            foreach ($deliveries as $delivery) {
                $received += $delivery['points'];
            }
            $VIEW_DATA['received'] = $received;
            $VIEW_DATA['title'] = "Lab Results";
            return "lab/results.php";
        }

        // lab is open
        $VIEW_DATA['title'] = "Lab: " . $lab['name'];
        return "lab/doLab.php";
    }

    #[Get(uri: "/(\d+)/download/(\d+)$", sec: "student")]
    public function downloadFile()
    {
        global $URI_PARAMS;

        $course = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $lab_id = $URI_PARAMS[3];
        $attachment_id = $URI_PARAMS[4];

        $lab = $this->labDao->byId($lab_id);
        $attachment = $this->attachmentDao->byId($attachment_id);
        $file = "res/{$course}/{$block}/lab/{$lab_id}/download/{$attachment_id}/";

        // determine the donwload_id
        $user_id = $_SESSION['user']['id'];
        $group = null;
        $download_id = $user_id;
        if ($lab['type'] == "group") {
            $group = $this->enrollmentDao->getGroup($user_id, $course, $block);
            $download_id = $group;
        }

        $file .= "{$download_id}/{$attachment['name']}";
        if (!file_exists($file)) {
            // copy the unzipped files into a tmp dir
            $aid = $attachment['id'];
            $src = sys_get_temp_dir();
            $src .= "/lmz/unzip/{$aid}";
            if (!file_exists($src) || !is_dir($src)) {
                $this->labAttachmentHlpr->extract($attachment);
            }

            $dst = sys_get_temp_dir();
            $dst .= "/lmz/download/{$aid}/";
            $this->labAttachmentHlpr->ensureDirCreated($dst);
            $dst .= "{$download_id}";
            shell_exec("cp -r {$src} {$dst}");

            // TODO: perform zip_actions as specified in the DB

            // cerate a download event in the DB
            $this->downloadDao->add($aid, $user_id, $group);

            // based on: https://stackoverflow.com/a/4914807/6933102
            $this->labAttachmentHlpr->ensureDirCreated(dirname($file));
            $zip = new ZipArchive();
            $zip->open($file, ZipArchive::CREATE | ZipArchive::OVERWRITE);
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dst),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $f) {
                $filePath = $f->getRealPath();
                $relativePath = substr($filePath, strlen($dst));
                if (!$f->isDir()) {
                    $zip->addFile($filePath, $relativePath);
                } else {
                    $zip->addEmptyDir($relativePath);
                }
            }

            // closing creates the zip file
            $zip->close();

            // delete the tmp dir
            shell_exec("rm -rf {$dst}");
        }

        // based on: https://stackoverflow.com/a/2882523/6933102
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Content-Length: ' . filesize($file));
        ob_clean();
        flush();
        readfile($file);
    }

    /**
     * Expects AJAX
     */
    #[Post(uri: "/(\d+)/txt$", sec: "student")]
    public function addTxt()
    {
        global $URI_PARAMS;

        $lab_id = $URI_PARAMS[3];
        if ($this->labEnded($lab_id)) {
            return ["error" => "Lab is closed"];
        }

        $submission_id = filter_input(INPUT_POST, "submission_id", FILTER_VALIDATE_INT);
        $deliverable_id = filter_input(INPUT_POST, "deliverable_id", FILTER_VALIDATE_INT);
        $group = filter_input(INPUT_POST, "group");
        $completion = filter_input(INPUT_POST, "completion", FILTER_VALIDATE_INT);
        $duration = filter_input(INPUT_POST, "duration");
        $shifted = filter_input(INPUT_POST, "text");
        $text = $this->markdownHlpr->ceasarShift($shifted);
        $hasMarkDown = filter_input(INPUT_POST, "hasMarkDown", FILTER_VALIDATE_BOOLEAN);
        $stuShifted = filter_input(INPUT_POST, "stuComment");
        $stuCmntHasMD = filter_input(INPUT_POST, "stuCmntHasMD", FILTER_VALIDATE_BOOLEAN);
        $hasMarkDown = $hasMarkDown ? 1 : 0;

        $stuComment = NULL;
        if ($stuShifted) {
            $stuComment = $this->markdownHlpr->ceasarShift($stuShifted);
            $stuCmntHasMD = $stuCmntHasMD ? 1 : 0;
        }

        if (!$submission_id) {
            $submission_id = $this->submissionDao->create(
                $lab_id,
                $_SESSION['user']['id'],
                $group
            );
        }

        $delivery_id = $this->deliveryDao->createTxt(
            $submission_id,
            $deliverable_id,
            $_SESSION['user']['id'],
            $completion,
            $duration,
            $text,
            $hasMarkDown,
            $stuComment,
            $stuCmntHasMD
        );

        return $this->deliveryDao->byId($delivery_id);
    }

    /**
     * Expects AJAX
     */
    #[Put(uri: "/(\d+)/txt/(\d+)$", sec: "student")]
    public function updateTxt()
    {
        global $URI_PARAMS;
        global $_PUT;

        $lab_id = $URI_PARAMS[3];
        if ($this->labEnded($lab_id)) {
            return ["error" => "Lab is closed"];
        }

        $delivery_id = $URI_PARAMS[4];
        $completion = $_PUT["completion"];
        $duration =   $_PUT["duration"];
        $shifted =    $_PUT["text"];
        $text = $this->markdownHlpr->ceasarShift($shifted);
        $hasMarkDown = $_PUT["hasMarkDown"] ? 1 : 0;
        $stuShifted =  $_PUT["stuComment"];

        $stuComment = NULL;
        $stuCmntHasMD = NULL;
        if ($stuShifted) {
            $stuComment = $this->markdownHlpr->ceasarShift($stuShifted);
            $stuCmntHasMD = $_PUT["stuCmntHasMD"] ? 1 : 0;
        }


        $this->deliveryDao->updateTxt(
            $delivery_id,
            $_SESSION['user']['id'],
            $completion,
            $duration,
            $text,
            $hasMarkDown,
            $stuComment,
            $stuCmntHasMD
        );

        return $this->deliveryDao->byId($delivery_id);
    }

    /**
     * Expects AJAX
     */
    #[Post(uri: "/(\d+)/url$", sec: "student")]
    public function addUrl()
    {
        global $URI_PARAMS;

        $lab_id = $URI_PARAMS[3];
        if ($this->labEnded($lab_id)) {
            return ["error" => "Lab is closed"];
        }

        $submission_id = filter_input(INPUT_POST, "submission_id", FILTER_VALIDATE_INT);
        $deliverable_id = filter_input(INPUT_POST, "deliverable_id", FILTER_VALIDATE_INT);
        $group = filter_input(INPUT_POST, "group");
        $completion = filter_input(INPUT_POST, "completion", FILTER_VALIDATE_INT);
        $duration = filter_input(INPUT_POST, "duration");
        $url = filter_input(INPUT_POST, "url");
        $stuShifted = filter_input(INPUT_POST, "stuComment");
        $stuCmntHasMD = filter_input(INPUT_POST, "stuCmntHasMD", FILTER_VALIDATE_BOOLEAN);

        $stuComment = NULL;
        if ($stuShifted) {
            $stuComment = $this->markdownHlpr->ceasarShift($stuShifted);
            $stuCmntHasMD = $stuCmntHasMD ? 1 : 0;
        }

        if (!$submission_id) {
            $submission_id = $this->submissionDao->create(
                $lab_id,
                $_SESSION['user']['id'],
                $group
            );
        }

        $delivery_id = $this->deliveryDao->createUrl(
            $submission_id,
            $deliverable_id,
            $_SESSION['user']['id'],
            $completion,
            $duration,
            $url,
            $stuComment,
            $stuCmntHasMD
        );

        return $this->deliveryDao->byId($delivery_id);
    }

    /**
     * Expects AJAX
     */
    #[Put(uri: "/(\d+)/url/(\d+)$", sec: "student")]
    public function updateUrl()
    {
        global $URI_PARAMS;
        global $_PUT;

        $lab_id = $URI_PARAMS[3];
        if ($this->labEnded($lab_id)) {
            return ["error" => "Lab is closed"];
        }

        $delivery_id = $URI_PARAMS[4];
        $completion = $_PUT["completion"];
        $duration =   $_PUT["duration"];
        $url = $_PUT["url"];
        $stuShifted =  $_PUT["stuComment"];

        $stuComment = NULL;
        $stuCmntHasMD = NULL;
        if ($stuShifted) {
            $stuComment = $this->markdownHlpr->ceasarShift($stuShifted);
            $stuCmntHasMD = $_PUT["stuCmntHasMD"] ? 1 : 0;
        }

        $this->deliveryDao->updateUrl(
            $delivery_id,
            $_SESSION['user']['id'],
            $completion,
            $duration,
            $url,
            $stuComment,
            $stuCmntHasMD
        );

        return $this->deliveryDao->byId($delivery_id);
    }

    /**
     * Expects AJAX
     */
    #[Post(uri: "/(\d+)/(img|pdf|zip)$", sec: "student")]
    public function addFileStats()
    {
        global $URI_PARAMS;

        $lab_id = $URI_PARAMS[3];
        if ($this->labEnded($lab_id)) {
            return ["error" => "Lab is closed"];
        }

        $submission_id = filter_input(INPUT_POST, "submission_id", FILTER_VALIDATE_INT);
        $deliverable_id = filter_input(INPUT_POST, "deliverable_id", FILTER_VALIDATE_INT);
        $group = filter_input(INPUT_POST, "group");
        $completion = filter_input(INPUT_POST, "completion", FILTER_VALIDATE_INT);
        $duration = filter_input(INPUT_POST, "duration");
        $stuShifted = filter_input(INPUT_POST, "stuComment");
        $stuCmntHasMD = filter_input(INPUT_POST, "stuCmntHasMD", FILTER_VALIDATE_BOOLEAN);

        $stuComment = NULL;
        if ($stuShifted) {
            $stuComment = $this->markdownHlpr->ceasarShift($stuShifted);
            $stuCmntHasMD = $stuCmntHasMD ? 1 : 0;
        }

        if (!$submission_id) {
            $submission_id = $this->submissionDao->create(
                $lab_id,
                $_SESSION['user']['id'],
                $group
            );
        }

        $delivery_id = $this->deliveryDao->createFile(
            $submission_id,
            $deliverable_id,
            $_SESSION['user']['id'],
            $completion,
            $duration,
            '',
            '',
            '',
            $stuComment,
            $stuCmntHasMD
        );

        return $this->deliveryDao->byId($delivery_id);
    }

    /**
     * Expects AJAX
     */
    #[Put(uri: "/(\d+)/(img|pdf|zip)/(\d+)$", sec: "student")]
    public function updateFileStats()
    {
        global $URI_PARAMS;
        global $_PUT;

        $lab_id = $URI_PARAMS[3];
        if ($this->labEnded($lab_id)) {
            return ["error" => "Lab is closed"];
        }

        $delivery_id = $URI_PARAMS[5];
        $completion = $_PUT["completion"];
        $duration =   $_PUT["duration"];
        $stuShifted =  $_PUT["stuComment"];

        $stuComment = NULL;
        $stuCmntHasMD = NULL;
        if ($stuShifted) {
            $stuComment = $this->markdownHlpr->ceasarShift($stuShifted);
            $stuCmntHasMD = $_PUT["stuCmntHasMD"] ? 1 : 0;
        }

        $this->deliveryDao->updateFileStats(
            $delivery_id,
            $completion,
            $duration,
            $stuComment,
            $stuCmntHasMD
        );

        return $this->deliveryDao->byId($delivery_id);
    }

    /**
     * Expects AJAX
     */
    #[Post(uri: "/(\d+)/(img|pdf|zip)/file$", sec: "student")]
    public function addUpdateFile()
    {
        // stop if there was an upload error
        if ($_FILES["file"]['error'] != UPLOAD_ERR_OK) {
            return ["error" => "Upload Error"];
        }
        if ($_FILES["file"]['size'] > 5252880) {
            return ["error" => "File too large, 50MB is the maximum"];
        }

        global $URI_PARAMS;
        $course = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $lab_id = $URI_PARAMS[3];
        $type = $URI_PARAMS[4];
        if ($this->labEnded($lab_id)) {
            return ["error" => "Lab is closed"];
        }
        if ($type == 'pdf' && !$this->labAttachmentHlpr->isPdfFile($_FILES["file"]['tmp_name'])) {
            return ["error" => "File does not seem to be a .pdf file"];
        }
        if ($type == 'zip' && !$this->labAttachmentHlpr->isZipFile($_FILES["file"]['tmp_name'])) {
            return ["error" => "File does not seem to be a .zip file"];
        }

        $submission_id = filter_input(INPUT_POST, "submission_id", FILTER_VALIDATE_INT);
        $deliverable_id = filter_input(INPUT_POST, "deliverable_id", FILTER_VALIDATE_INT);
        $delivery_id = filter_input(INPUT_POST, "delivery_id", FILTER_VALIDATE_INT);
        $group = filter_input(INPUT_POST, "group");
        $completion = filter_input(INPUT_POST, "completion", FILTER_VALIDATE_INT);
        $duration = filter_input(INPUT_POST, "duration");
        $stuShifted = filter_input(INPUT_POST, "stuComment");
        $stuCmntHasMD = filter_input(INPUT_POST, "stuCmntHasMD", FILTER_VALIDATE_BOOLEAN);
        $user_id = $_SESSION['user']['id'];

        $stuComment = NULL;
        if ($stuShifted) {
            $stuComment = $this->markdownHlpr->ceasarShift($stuShifted);
            $stuCmntHasMD = $stuCmntHasMD ? 1 : 0;
        }

        if (!$submission_id) {
            $submission_id = $this->submissionDao->create(
                $lab_id,
                $_SESSION['user']['id'],
                $group
            );
        }

        $listing = null;
        if ($type == 'img') {
            $name = $_FILES["file"]['name'];
            $path = "res/{$course}/{$block}/lab/{$lab_id}/submit/{$submission_id}";
            $res = $this->imageHlpr->process('file', $path);
            if ($res['error']) {
                return ["error" => $res['error']];
            }
            $dst = $res['dst'];
        } else { // pdf and zip
            if ($type == 'zip') {
                $zip = new ZipArchive();
                if ($zip->open($_FILES["file"]['tmp_name']) !== TRUE) {
                    return ["error" => "Zip file could not be opened"];
                }
                $listing = "";
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $listing .= $zip->getNameIndex($i) . "\n";
                }
                $zip->close();
            }
            $curr = $_FILES["file"]['tmp_name'];
            $name = $_FILES["file"]['name'];
            $time = new DateTimeImmutable("now", new DateTimeZone(TIMEZONE));
            $ts = $time->format("Y-m-d_H:i:s");
            $dst = "res/{$course}/{$block}/lab/{$lab_id}/submit/"
                . "{$submission_id}";
            if (!file_exists($dst) && !is_dir($dst)) {
                mkdir($dst, 0777, true);
            }
            $dst .= "/{$ts}_{$user_id}_{$name}";
            move_uploaded_file($curr, $dst);
        }

        if (!$delivery_id) {
            $delivery_id = $this->deliveryDao->createFile(
                $submission_id,
                $deliverable_id,
                $user_id,
                $completion,
                $duration,
                $listing,
                $dst,
                $name,
                $stuComment,
                $stuCmntHasMD
            );
        } else {
            $delivery = $this->deliveryDao->byId($delivery_id);
            if ($delivery['file']) {
                unlink($delivery['file']);
            }

            $this->deliveryDao->updateFile(
                $delivery_id,
                $user_id,
                $completion,
                $duration,
                $listing,
                $dst,
                $name,
                $stuComment,
                $stuCmntHasMD
            );
        }

        return $this->deliveryDao->byId($delivery_id);
    }

    private function labEnded($lab_id, $leewaySecs = 30)
    {
        $lab = $this->labDao->byId($lab_id);
        $tz = new DateTimeZone(TIMEZONE);
        $now = new DateTimeImmutable("now", $tz);
        $stop = new DateTimeImmutable($lab['stop'], $tz);
        // give leeway second 
        $stop = $stop->add(new DateInterval("PT{$leewaySecs}S"));
        $stopDiff = $now->diff($stop);
        return $stopDiff->invert == 1; // is it in the past?
    }
}
