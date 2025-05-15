<?php

/**
 * Lab Taking Controller
 *
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

    #[Inject('ZipDlActionDao')]
    public $zipDlActionDao;

    #[Inject('ZipUlCheckDao')]
    public $zipUlCheckDao;

    #[Inject('ZipUlStatDao')]
    public $zipUlStatDao;

    /**
     * This function is really a 3 in one.
     * 1. If it is used before the start time it shows a countdown timer
     * 2. If it is used after the stop time it shows a status for each deliverable
     * 3. If between start and stop the user can upload deliverables
     */
    #[Get(uri: "/(\d+)(/(\d+))?$", sec: 'student')]
    public function viewLab()
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $fac_upd = false;
        $student_user_id = filter_input(INPUT_GET, 'student', FILTER_VALIDATE_INT);
        $user_id = $_SESSION['user']['id'];
        if ($student_user_id && $_SESSION['user']['isFaculty']) {
            $user_id = $student_user_id;
            $fac_upd = true;
        }

        $course = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $lab_id = $URI_PARAMS[3];
        $selected = $URI_PARAMS[5];

        $offering = $this->offeringDao->getOfferingByCourse($course, $block);
        $lab = $this->labDao->byId($lab_id);

        $tz = new DateTimeZone(TIMEZONE);
        $now = new DateTimeImmutable('now', $tz);
        $start = new DateTimeImmutable($lab['start'], $tz);
        $stop = new DateTimeImmutable($lab['stop'], $tz);

        $startDiff = $now->diff($start);
        $stopDiff = $now->diff($stop);

        $VIEW_DATA['course'] = $course;
        $VIEW_DATA['block'] = $block;
        $VIEW_DATA['offering'] = $offering;
        $VIEW_DATA['lab'] = $lab;

        // lab is not open yet
        if ($startDiff->invert === 0) { // start is in the future
            // show countdown page
            $VIEW_DATA['title'] = 'Lab Countdown';
            $VIEW_DATA['start'] = $startDiff;

            return 'lab/countdown.php';
        }

        if ($lab['type'] == 'group') {
            // get the group
            $enroll = $this->enrollmentDao->getEnrollment($user_id, $course, $block);
            $group = $enroll['group'] ?? null;
            if (! $group && $enroll['auth'] == 'instructor') {
                $group = 'instructor';
            }

            if ($group == null) {
                $VIEW_DATA['title'] = 'No Group';

                return 'lab/nogroup.php';
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
        $zips = [];
        foreach ($deliverables as $deliv) {
            $labPoints += $deliv['points'];
            if ($deliv['type'] == 'zip') {
                $zips[] = $deliv['id'];
            }
        }

        require_once 'lib/Parsedown.php';
        $parsedown = new Parsedown;
        $parsedown->setSafeMode(true);
        $VIEW_DATA['parsedown'] = $parsedown;
        $VIEW_DATA['stop'] = $stopDiff;
        $VIEW_DATA['group'] = $group;
        $VIEW_DATA['labPoints'] = $labPoints;
        $VIEW_DATA['attachments'] = $this->attachmentDao->forLab($lab_id);
        $VIEW_DATA['deliverables'] = $deliverables;
        $VIEW_DATA['submission'] = $submission;
        $VIEW_DATA['deliveries'] = $deliveries;

        $auth = $this->enrollmentDao->checkEnrollmentAuth($user_id, $course, $block);

        // lab is done / over
        // stop is in the past
        if (! $fac_upd && ($auth == 'observer' || $stopDiff->invert === 1)) {
            $received = 0;
            foreach ($deliveries as $delivery) {
                $received += $delivery['points'];
            }
            $VIEW_DATA['received'] = $received;
            $VIEW_DATA['title'] = 'Lab Results';

            return 'lab/results.php';
        }

        // lab is open
        // create submission if it does not exist
        if (! $submission) {
            $submission_id = $this->submissionDao->getOrCreate(
                $lab_id,
                $user_id,
                $group
            );
            $submission = $this->submissionDao->byId($submission_id);
            $VIEW_DATA['submission'] = $submission;
        }
        $checks = [];
        foreach ($zips as $zip) {
            $checks[$zip] = $this->zipUlCheckDao->forDeliverable($zip);
        }
        if ($fac_upd) {
            $VIEW_DATA['user_id'] = $student_user_id;
        }
        $VIEW_DATA['checks'] = $checks;
        $VIEW_DATA['title'] = 'Lab: '.$lab['name'];
        $VIEW_DATA['selected'] = $selected;

        return 'lab/doLab.php';
    }

    #[Get(uri: "/(\d+/)?(\d+)/download/(\d+)$", sec: 'student')]
    public function downloadFile()
    {
        global $URI_PARAMS;

        $course = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $lab_id = $URI_PARAMS[3];
        $length = count($URI_PARAMS);
        $attachment_id = $URI_PARAMS[$length - 1];

        $lab = $this->labDao->byId($lab_id);
        $attachment = $this->attachmentDao->byId($attachment_id);
        $lname = str_replace(' ', '_', $lab['name']);
        $deliverable = $this->deliverableDao->byId($attachment['deliverable_id']);
        $dseq = $deliverable['seq'];
        if (strlen($dseq) == 1) {
            $dseq = '0'.$dseq;
        }
        $zfile = "res/course/{$course}/{$block}/lab/{$lname}/{$dseq}/download/{$attachment_id}/";

        // determine the donwload_id
        $user_id = $_SESSION['user']['id'];
        $group = null;
        $download_id = $user_id;
        if ($lab['type'] == 'group') {
            $group = $this->enrollmentDao->getGroup($user_id, $course, $block);
            $download_id = $group;
        }

        $zfile .= "{$download_id}/{$attachment['name']}";
        if (! file_exists($zfile)) {
            // copy the unzipped files into a tmp dir
            $aid = $attachment['id'];
            $src = sys_get_temp_dir();
            $src .= "/lmz/unzip/{$aid}";
            if (! file_exists($src) || ! is_dir($src)) {
                $this->labAttachmentHlpr->extract($attachment);
            }

            $dst = sys_get_temp_dir();
            $dst .= "/lmz/download/{$aid}/";
            $this->labAttachmentHlpr->ensureDirCreated($dst);
            $dst .= "{$download_id}";
            shell_exec("cp -r {$src} {$dst}");

            // create a download event in the DB
            $id = $this->downloadDao->add($aid, $user_id, $group);

            // perform zip_actions as specified in the DB
            $actions = $this->zipDlActionDao->forAttachment($aid);
            foreach ($actions as $action) {
                $type = $action['type'];
                $file = $action['file'];
                $byte = $action['byte'];
                if ($type == 'text') {
                    $this->labAttachmentHlpr->wmTxt($dst, $file, $byte, $id);
                } elseif ($type = 'png') {
                    $this->labAttachmentHlpr->wmPng($dst, $file, $byte, $id);
                }
            }

            // based on: https://stackoverflow.com/a/4914807/6933102
            $this->labAttachmentHlpr->ensureDirCreated(dirname($zfile));
            $zip = new ZipArchive;
            $zip->open($zfile, ZipArchive::CREATE | ZipArchive::OVERWRITE);
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dst),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $f) {
                $filePath = $f->getRealPath();
                // +1 to remove the leading slash (it breaks windows unzip)
                $relativePath = substr($filePath, strlen($dst) + 1);
                if (! $f->isDir()) {
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
        header('Content-Disposition: attachment; filename='.basename($zfile));
        header('Content-Length: '.filesize($zfile));
        ob_clean();
        flush();
        readfile($zfile);
    }

    /**
     * Expects AJAX
     */
    #[Post(uri: "/(\d+)/txt$", sec: 'student')]
    public function addTxt()
    {
        global $URI_PARAMS;

        $lab_id = $URI_PARAMS[3];
        if (! $_SESSION['user']['isFaculty'] && $this->labEnded($lab_id)) {
            return ['error' => 'Lab is closed'];
        }

        $student_user_id = filter_input(INPUT_GET, 'student', FILTER_VALIDATE_INT);
        $user_id = $_SESSION['user']['id'];
        if ($student_user_id && $_SESSION['user']['isFaculty']) {
            $user_id = $student_user_id;
        }

        $submission_id = filter_input(INPUT_POST, 'submission_id', FILTER_VALIDATE_INT);
        $deliverable_id = filter_input(INPUT_POST, 'deliverable_id', FILTER_VALIDATE_INT);
        $group = filter_input(INPUT_POST, 'group');
        $completion = filter_input(INPUT_POST, 'completion', FILTER_VALIDATE_INT);
        $duration = filter_input(INPUT_POST, 'duration');
        $shifted = filter_input(INPUT_POST, 'text');
        $text = $this->markdownHlpr->ceasarShift($shifted);
        $hasMarkDown = filter_input(INPUT_POST, 'hasMarkDown', FILTER_VALIDATE_BOOLEAN);
        $stuShifted = filter_input(INPUT_POST, 'stuComment');
        $stuCmntHasMD = filter_input(INPUT_POST, 'stuCmntHasMD', FILTER_VALIDATE_BOOLEAN);
        $hasMarkDown = $hasMarkDown ? 1 : 0;

        $stuComment = null;
        if ($stuShifted) {
            $stuComment = $this->markdownHlpr->ceasarShift($stuShifted);
            $stuCmntHasMD = $stuCmntHasMD ? 1 : 0;
        }

        if (! $submission_id) {
            $submission_id = $this->submissionDao->getOrCreate(
                $lab_id,
                $user_id,
                $group
            );
        }

        $delivery_id = $this->deliveryDao->createTxt(
            $submission_id,
            $deliverable_id,
            $user_id,
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
    #[Put(uri: "/(\d+)/txt/(\d+)$", sec: 'student')]
    public function updateTxt()
    {
        global $URI_PARAMS;
        global $_PUT;

        $lab_id = $URI_PARAMS[3];
        if (! $_SESSION['user']['isFaculty'] && $this->labEnded($lab_id)) {
            return ['error' => 'Lab is closed'];
        }

        $student_user_id = filter_input(INPUT_GET, 'student', FILTER_VALIDATE_INT);
        $user_id = $_SESSION['user']['id'];
        if ($student_user_id && $_SESSION['user']['isFaculty']) {
            $user_id = $student_user_id;
        }

        $delivery_id = $URI_PARAMS[4];
        $completion = $_PUT['completion'];
        $duration = $_PUT['duration'];
        $shifted = $_PUT['text'];
        $text = $this->markdownHlpr->ceasarShift($shifted);
        $hasMarkDown = $_PUT['hasMarkDown'] ? 1 : 0;
        $stuShifted = $_PUT['stuComment'];

        $stuComment = null;
        $stuCmntHasMD = null;
        if ($stuShifted) {
            $stuComment = $this->markdownHlpr->ceasarShift($stuShifted);
            $stuCmntHasMD = $_PUT['stuCmntHasMD'] ? 1 : 0;
        }

        $this->deliveryDao->updateTxt(
            $delivery_id,
            $user_id,
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
    #[Post(uri: "/(\d+)/url$", sec: 'student')]
    public function addUrl()
    {
        global $URI_PARAMS;

        $lab_id = $URI_PARAMS[3];
        if (! $_SESSION['user']['isFaculty'] && $this->labEnded($lab_id)) {
            return ['error' => 'Lab is closed'];
        }

        $student_user_id = filter_input(INPUT_GET, 'student', FILTER_VALIDATE_INT);
        $user_id = $_SESSION['user']['id'];
        if ($student_user_id && $_SESSION['user']['isFaculty']) {
            $user_id = $student_user_id;
        }

        $submission_id = filter_input(INPUT_POST, 'submission_id', FILTER_VALIDATE_INT);
        $deliverable_id = filter_input(INPUT_POST, 'deliverable_id', FILTER_VALIDATE_INT);
        $group = filter_input(INPUT_POST, 'group');
        $completion = filter_input(INPUT_POST, 'completion', FILTER_VALIDATE_INT);
        $duration = filter_input(INPUT_POST, 'duration');
        $url = filter_input(INPUT_POST, 'url');
        $stuShifted = filter_input(INPUT_POST, 'stuComment');
        $stuCmntHasMD = filter_input(INPUT_POST, 'stuCmntHasMD', FILTER_VALIDATE_BOOLEAN);

        $stuComment = null;
        if ($stuShifted) {
            $stuComment = $this->markdownHlpr->ceasarShift($stuShifted);
            $stuCmntHasMD = $stuCmntHasMD ? 1 : 0;
        }

        if (! $submission_id) {
            $submission_id = $this->submissionDao->getOrCreate(
                $lab_id,
                $user_id,
                $group
            );
        }

        $delivery_id = $this->deliveryDao->createUrl(
            $submission_id,
            $deliverable_id,
            $user_id,
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
    #[Put(uri: "/(\d+)/url/(\d+)$", sec: 'student')]
    public function updateUrl()
    {
        global $URI_PARAMS;
        global $_PUT;

        $lab_id = $URI_PARAMS[3];
        if (! $_SESSION['user']['isFaculty'] && $this->labEnded($lab_id)) {
            return ['error' => 'Lab is closed'];
        }
        $student_user_id = filter_input(INPUT_GET, 'student', FILTER_VALIDATE_INT);
        $user_id = $_SESSION['user']['id'];
        if ($student_user_id && $_SESSION['user']['isFaculty']) {
            $user_id = $student_user_id;
        }

        $delivery_id = $URI_PARAMS[4];
        $completion = $_PUT['completion'];
        $duration = $_PUT['duration'];
        $url = $_PUT['url'];
        $stuShifted = $_PUT['stuComment'];

        $stuComment = null;
        $stuCmntHasMD = null;
        if ($stuShifted) {
            $stuComment = $this->markdownHlpr->ceasarShift($stuShifted);
            $stuCmntHasMD = $_PUT['stuCmntHasMD'] ? 1 : 0;
        }

        $this->deliveryDao->updateUrl(
            $delivery_id,
            $user_id,
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
    #[Post(uri: "/(\d+)/(img|pdf|zip)$", sec: 'student')]
    public function addFileStats()
    {
        global $URI_PARAMS;

        $lab_id = $URI_PARAMS[3];
        if (! $_SESSION['user']['isFaculty'] && $this->labEnded($lab_id)) {
            return ['error' => 'Lab is closed'];
        }
        $student_user_id = filter_input(INPUT_GET, 'student', FILTER_VALIDATE_INT);
        $user_id = $_SESSION['user']['id'];
        if ($student_user_id && $_SESSION['user']['isFaculty']) {
            $user_id = $student_user_id;
        }

        $submission_id = filter_input(INPUT_POST, 'submission_id', FILTER_VALIDATE_INT);
        $deliverable_id = filter_input(INPUT_POST, 'deliverable_id', FILTER_VALIDATE_INT);
        $group = filter_input(INPUT_POST, 'group');
        $completion = filter_input(INPUT_POST, 'completion', FILTER_VALIDATE_INT);
        $duration = filter_input(INPUT_POST, 'duration');
        $stuShifted = filter_input(INPUT_POST, 'stuComment');
        $stuCmntHasMD = filter_input(INPUT_POST, 'stuCmntHasMD', FILTER_VALIDATE_BOOLEAN);

        $stuComment = null;
        if ($stuShifted) {
            $stuComment = $this->markdownHlpr->ceasarShift($stuShifted);
            $stuCmntHasMD = $stuCmntHasMD ? 1 : 0;
        }

        if (! $submission_id) {
            $submission_id = $this->submissionDao->getOrCreate(
                $lab_id,
                $user_id,
                $group
            );
        }

        $delivery_id = $this->deliveryDao->createFile(
            $submission_id,
            $deliverable_id,
            $user_id,
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
    #[Put(uri: "/(\d+)/(img|pdf|zip)/(\d+)$", sec: 'student')]
    public function updateFileStats()
    {
        global $URI_PARAMS;
        global $_PUT;

        $lab_id = $URI_PARAMS[3];
        if (! $_SESSION['user']['isFaculty'] && $this->labEnded($lab_id)) {
            return ['error' => 'Lab is closed'];
        }

        $delivery_id = $URI_PARAMS[5];
        $completion = $_PUT['completion'];
        $duration = $_PUT['duration'];
        $stuShifted = $_PUT['stuComment'];

        $stuComment = null;
        $stuCmntHasMD = null;
        if ($stuShifted) {
            $stuComment = $this->markdownHlpr->ceasarShift($stuShifted);
            $stuCmntHasMD = $_PUT['stuCmntHasMD'] ? 1 : 0;
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
    #[Post(uri: "/(\d+)/(img|pdf|zip)/file$", sec: 'student')]
    public function addUpdateFile()
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
        $lab_id = $URI_PARAMS[3];
        $type = $URI_PARAMS[4];
        if (! $_SESSION['user']['isFaculty'] && $this->labEnded($lab_id)) {
            return ['error' => 'Lab is closed'];
        }
        $student_user_id = filter_input(INPUT_GET, 'student', FILTER_VALIDATE_INT);
        $user_id = $_SESSION['user']['id'];
        if ($student_user_id && $_SESSION['user']['isFaculty']) {
            $user_id = $student_user_id;
        }
        if ($type == 'pdf' && ! $this->labAttachmentHlpr->isPdfFile($_FILES['file']['tmp_name'])) {
            return ['error' => 'File does not seem to be a .pdf file'];
        }
        if ($type == 'zip' && ! $this->labAttachmentHlpr->isZipFile($_FILES['file']['tmp_name'])) {
            return ['error' => 'File does not seem to be a .zip file'];
        }

        $submission_id = filter_input(INPUT_POST, 'submission_id', FILTER_VALIDATE_INT);
        $deliverable_id = filter_input(INPUT_POST, 'deliverable_id', FILTER_VALIDATE_INT);
        $delivery_id = filter_input(INPUT_POST, 'delivery_id', FILTER_VALIDATE_INT);
        $group = filter_input(INPUT_POST, 'group');
        $completion = filter_input(INPUT_POST, 'completion', FILTER_VALIDATE_INT);
        $duration = filter_input(INPUT_POST, 'duration');
        $stuShifted = filter_input(INPUT_POST, 'stuComment');
        $stuCmntHasMD = filter_input(INPUT_POST, 'stuCmntHasMD', FILTER_VALIDATE_BOOLEAN);

        $stuComment = null;
        if ($stuShifted) {
            $stuComment = $this->markdownHlpr->ceasarShift($stuShifted);
            $stuCmntHasMD = $stuCmntHasMD ? 1 : 0;
        }

        if (! $submission_id) {
            $submission_id = $this->submissionDao->getOrCreate(
                $lab_id,
                $user_id,
                $group
            );
        }

        $lab = $this->labDao->byId($lab_id);
        $lname = str_replace(' ', '_', $lab['name']);
        $deliverable = $this->deliverableDao->byId($deliverable_id);
        $dseq = $deliverable['seq'];
        if (strlen($dseq) == 1) {
            $dseq = '0'.$dseq;
        }

        // process the file
        $error = false;
        $listing = null;
        if ($type == 'img') {
            $name = $_FILES['file']['name'];
            $path = "res/course/{$course}/{$block}/lab/{$lname}/{$dseq}/submit/";
            $res = $this->imageHlpr->process('file', $path);
            if ($res['error']) {
                return ['error' => $res['error']];
            }
            $dst = $res['dst'];
        } else { // pdf and zip
            if ($type == 'zip') {
                if (! $delivery_id) {
                    $delivery_id = $this->deliveryDao->createFileStats(
                        $submission_id,
                        $deliverable_id,
                        $user_id,
                        $completion,
                        $duration,
                        $stuComment,
                        $stuCmntHasMD
                    );
                }
                $result = $this->processUlZip(
                    $lab_id,
                    $deliverable_id,
                    $delivery_id,
                    $user_id
                );
                if ($result['error']) {
                    return ['error' => $result['error']];
                }
                $listing = $result['listing'];
                $blocked = $result['blocked'];
                if ($blocked) {
                    $error = 'Please fix the following in your zip file '
                        ."and upload again\n\n".implode("\n", $blocked);
                }
            }
            $curr = $_FILES['file']['tmp_name'];
            $name = $_FILES['file']['name'];
            $time = new DateTimeImmutable('now', new DateTimeZone(TIMEZONE));
            $ts = $time->format('Y-m-d_H:i:s');
            $dst = "res/course/{$course}/{$block}/lab/{$lname}/{$dseq}/submit/"
                ."{$submission_id}";
            if (! file_exists($dst) && ! is_dir($dst)) {
                mkdir($dst, 0777, true);
            }
            $dst .= "/{$ts}_{$user_id}_{$name}";
            move_uploaded_file($curr, $dst);
        }

        // create / update delivery in the db
        if (! $delivery_id) {
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
            if (! is_null($delivery['file']) && $delivery['file']) {
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

        $result = $this->deliveryDao->byId($delivery_id);
        if ($error) {
            $result['error'] = $error;
            $result['failed'] = array_keys($blocked);
        }

        return $result;
    }

    /**
     * Expects AJAX
     **/
    #[Post(uri: "/(\d+)/(\d+)/picture$", sec: 'student')]
    public function takePicture()
    {
        global $URI_PARAMS;

        $course = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $lab_id = $URI_PARAMS[3];
        $deliverable_id = $URI_PARAMS[4];

        // reject answers after lab stop time
        if (! $_SESSION['user']['isFaculty'] && $this->labEnded($lab_id, 30)) {
            return 'error/403.php';
        }
        $student_user_id = filter_input(INPUT_GET, 'student', FILTER_VALIDATE_INT);
        $user_id = $_SESSION['user']['id'];
        if ($student_user_id && $_SESSION['user']['isFaculty']) {
            $user_id = $student_user_id;
        }

        $delivery_id = filter_input(INPUT_POST, 'answer_id', FILTER_VALIDATE_INT);
        $img = filter_input(INPUT_POST, 'image');

        // get submission_id
        $lab = $this->labDao->byId($lab_id);
        if ($lab['type'] == 'group') {
            $group = $this->enrollmentDao->getGroup($user_id, $course, $block);
        } else {
            $group = null;
        }
        if ($delivery_id) {
            $submission_id = $this->deliveryDao->byId($delivery_id)['submission_id'];
        } else {
            // try to find submission by lab_id and user_id
            $submission = $this->submissionDao->forUser($user_id, $lab_id);
            $submission_id = $submission['id'] ?? null;

            // create submission if it does not exist
            if ($submission_id == null) {
                $submission_id = $this->submissionDao->getOrCreate(
                    $lab_id,
                    $user_id,
                    $group
                );
            }
        }

        $deliverable = $this->deliverableDao->byId($deliverable_id);
        $lname = str_replace(' ', '_', $lab['name']);
        $dseq = $deliverable['seq'];
        if (strlen($dseq) == 1) {
            $dseq = '0'.$dseq;
        }
        $path = "res/course/{$course}/{$block}/lab/{$lname}/{$dseq}/submit/";
        $dst = $this->imageHlpr->save($img, $path);
        $name = basename($dst);

        // create / update answer in the db
        if ($delivery_id) {
            $img = $this->deliveryDao->byId($delivery_id)['text'];
            $this->imageHlpr->delete($img);
            $this->deliveryDao->updatePicture(
                $delivery_id,
                $dst,
                $name
            );
        } else {
            $delivery_id = $this->deliveryDao->createPicture(
                $deliverable_id,
                $submission_id,
                $user_id,
                $dst,
                $name,
            );
        }

        return ['dst' => $dst, 'answer_id' => $delivery_id];
    }

    /**
     * Expects AJAX
     */
    #[Delete(uri: "/(\d+)/delivery/(\d+)$", sec: 'student')]
    public function deleteFile()
    {
        global $URI_PARAMS;

        $id = $URI_PARAMS[4];
        $user_id = $_SESSION['user']['id'];
        $delivery = $this->deliveryDao->byId($id);
        if ($user_id != $delivery['user_id']) {
            return ['error' => 'You are not the owner of this file'];
        }
        // remove the file from the filesystem
        if (! is_null($delivery['file']) && $delivery['file']) {
            unlink($delivery['file']);
        }
        // remove the delivery from the database
        $this->deliveryDao->delete($id);

        return ['success' => true];
    }

    private function labEnded($lab_id, $leewaySecs = 30)
    {
        $lab = $this->labDao->byId($lab_id);
        $tz = new DateTimeZone(TIMEZONE);
        $now = new DateTimeImmutable('now', $tz);
        $stop = new DateTimeImmutable($lab['stop'], $tz);
        // give leeway second
        $stop = $stop->add(new DateInterval("PT{$leewaySecs}S"));
        $stopDiff = $now->diff($stop);

        return $stopDiff->invert == 1; // is it in the past?
    }

    private function processUlZip(
        $lab_id,
        $deliverable_id,
        $delivery_id,
        $user_id)
    {
        $zipChecks = $this->zipUlCheckDao->forDeliverable($deliverable_id);
        $lab = $this->labDao->byId($lab_id);
        $tz = new DateTimeZone(TIMEZONE);
        $now_date = new DateTimeImmutable('now', $tz);
        $now = $now_date->format('Y-m-d H:i:s');
        $date = DateTime::createFromFormat('Y-m-d H:i:s', $lab['start'], $tz);
        $ts_lab = $date->getTimestamp();
        $listing = '';
        $listing_limit = 50;
        $listing_count = 0;

        // get the size of the zip file
        $filesize = filesize($_FILES['file']['tmp_name']);
        $size = $filesize;
        $power = 0;
        while ($size >= 1024) {
            $size /= 1024;
            $power++;
        }
        $size = round($size, 2);
        $units = ['B', 'KB', 'MB', 'GB'];
        $listing .= "<div class='zFile'>"
            ."<span class='name'>{$size} {$units[$power]}</span>"
            .'</div>';

        // initialize checks
        $timeFail = false;
        $results = []; // -1 is not seen, 0 is seen and bad, 1 is seen and good
        foreach ($zipChecks as $zipCheck) {
            if ($zipCheck['type'] == 'size_lt') {
                if ($filesize < $zipCheck['byte']) {
                    $results[$zipCheck['id']] = 1;
                } else {
                    $results[$zipCheck['id']] = 0;
                    $cmt = 'Zip file too large';
                    $this->zipUlStatDao->add($delivery_id, $now, $zipCheck['type'], '', $cmt);
                }
            } elseif ($zipCheck['type'] == 'size_gt') {
                if ($filesize > $zipCheck['byte']) {
                    $results[$zipCheck['id']] = 1;
                } else {
                    $results[$zipCheck['id']] = 0;
                    $cmt = 'Zip file too small';
                    $this->zipUlStatDao->add($delivery_id, $now, $zipCheck['type'], '', $cmt);
                }
            } else {
                $results[$zipCheck['id']] = -1;
            }
        }

        // look through the files in the zip
        $zip = new ZipArchive;
        if ($zip->open($_FILES['file']['tmp_name']) !== true) {
            return ['error' => 'Zip file could not be opened'];
        }
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            $bn = basename($name);
            if (
                str_starts_with($bn, '__MACOSX')
                || str_starts_with($bn, '.DS_Store')
                || str_starts_with($bn, '._')
            ) {
                continue;
            }
            $lname = strtolower($name); // for case insensitive comparison

            foreach ($zipChecks as $zipCheck) {
                $id = $zipCheck['id'];
                if ($results[$id] != -1) {
                    continue;
                }
                $type = $zipCheck['type'];
                $file = strtolower($zipCheck['file']); // for lowercase compare
                $byte = $zipCheck['byte'];

                if ($type == 'present' && $file == $lname) {
                    $results[$id] = 1;

                    continue;
                }
                if ($type == 'not_present' && $file == $lname) {
                    $results[$id] = 0;
                    $cmt = 'Was present';
                    $this->zipUlStatDao->add($delivery_id, $now, $type, $file, $cmt);

                    continue;
                }
                if (! str_ends_with($type, 'wm')) {
                    continue;
                }

                // get the file to look for a WM
                $data = $zip->getFromIndex($i);

                // get the WM
                if ($type == 'txt_wm' && $file == $lname) {
                    $wm = intval($this->labAttachmentHlpr->readTxtWm($data, $byte));
                }
                if ($type == 'png_wm' && $file == $lname) {
                    $wm = $this->labAttachmentHlpr->readPngWm($data, $byte);
                }

                // get the download record indicated by the WM
                $download = $this->downloadDao->getById($wm);
                if (! $download) {
                    $cmt = 'User never downloaded? No download record for WM';
                    $this->zipUlStatDao->add($delivery_id, $now, $type, $file, $cmt);
                    $results[$id] = 0;

                    continue;
                }
                $uid = $download['user_id'];

                // now that we have the download, might as well create a finer
                // precision start time for the time stamp checks
                $date = DateTime::createFromFormat('Y-m-d H:i:s', $download['created'], $tz);
                $ts_lab = $date->getTimestamp();

                // check if the watermark user is the upload user
                if ($uid != $user_id) {
                    $cmt = 'User_id does not match';
                    $this->zipUlStatDao->add($delivery_id, $now, $type, $file, $cmt);
                    $results[$id] = 0;
                } else {
                    $results[$id] = 1;
                }
            }

            // do timestamp checks to see if the file was created after the
            // lab started (or if we got the download time, after download)
            $mtime = $zip->statIndex($i)['mtime'];
            $class = 'time';
            if ($mtime < $ts_lab) {
                $timeFail = true;
                $class .= ' old';
            }

            if ($listing_count < $listing_limit) {
                $name = htmlspecialchars($name);
                $listing .= '<div class="zFile">';
                $listing .= "<span class='name'>{$name}</span>";
                $listing .= "<span class='{$class}'>";
                $listing .= date('Y-m-d H:i:s', $mtime).'</span>';
                $listing .= '</div>';
                $listing_count++;
            } elseif ($listing_count == $listing_limit) {
                $listing .= "<div class='zFile'>... [Max {$listing_limit}: "
                    ."of {$zip->numFiles} "
                    .'Listing Truncated]</div>';
                $listing_count++;
            }
        }
        $zip->close();

        // finalize checks
        foreach ($zipChecks as $zipCheck) {
            $id = $zipCheck['id'];
            $type = $zipCheck['type'];
            $file = $zipCheck['file'];
            $cmt = 'Required file not found';
            if ($type !== 'not_present' && $results[$id] < 0) {
                $this->zipUlStatDao->add($delivery_id, $now, $type, $file, $cmt);
            } elseif ($type == 'not_present' && $results[$id] == -1) {
                $results[$id] = 1;
            }
        }
        if ($timeFail) {
            $type = 'timestamp';
            $file = 'see zip file listing';
            $cmt = 'Files(s) from before lab start';
            $this->zipUlStatDao->add($delivery_id, $now, $type, $file, $cmt);
        }

        // create blocked error messages
        $blocked = [];
        foreach ($zipChecks as $zipCheck) {
            if ($zipCheck['block'] && $results[$zipCheck['id']] != 1) {
                $id = $zipCheck['id'];
                $file = $zipCheck['file'];
                $msg = '';
                switch ($zipCheck['type']) {
                    case 'present':
                        $msg = "- Required file {$file} is not present";
                        break;
                    case 'not_present':
                        $msg = "- {$file} should not be present";
                        break;
                    case 'txt_wm':
                    case 'png_wm':
                        $msg = '- Identity check failed. Did you write this code?';
                        // because we want to avoid showing multiple ID checks
                        $id = $deliverable_id;
                        break;
                    case 'size_lt':
                        $msg = '- Zip file is too large';
                        break;
                    case 'size_gt':
                        $msg = '- Zip file is too small';
                        break;
                }
                $blocked[$id] = $msg;
            }
        }

        return ['listing' => $listing, 'blocked' => $blocked];
    }
}
