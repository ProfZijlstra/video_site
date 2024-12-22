<?php

/**
 * Lab Controller Class
 * @author mzijlstra 01/08/2024
 */

#[Controller(path: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/lab")]
class LabAdminCtrl
{
    #[Inject('OverviewHlpr')]
    public $overviewHlpr;

    #[Inject('LabDao')]
    public $labDao;

    #[Inject('DeliverableDao')]
    public $deliverableDao;

    #[Inject('SubmissionDao')]
    public $submissionDao;

    #[Inject('OfferingDao')]
    public $offeringDao;

    #[Inject('DayDao')]
    public $dayDao;

    #[Inject('LabAttachmentHlpr')]
    public $attachmentHlpr;

    #[Inject('AttachmentDao')]
    public $attachmentDao;

    #[Inject('MarkdownHlpr')]
    public $markdownCtrl;

    #[Inject('DownloadDao')]
    public $downloadDao;

    #[Inject('DeliveryDao')]
    public $deliveryDao;

    #[Inject('ZipDlActionDao')]
    public $zipDlActionDao;

    #[Inject('ZipUlCheckDao')]
    public $zipUlCheckDao;

    #[Inject('EnrollmentDao')]
    public $enrollmentDao;

    #[Get(uri: "$", sec: "student")]
    public function courseOverview()
    {
        // We're building on top of  overview -- run it first
        // this populates $VIEW_DATA with the overview related data
        $this->overviewHlpr->overview();

        global $VIEW_DATA;

        // get all labs for this offering
        $oid = $VIEW_DATA["offering_id"];
        if (
            $_SESSION['user']['isAdmin'] ||
            $_SESSION['user']['isFaculty']
        ) {
            $labs = $this->labDao->allForOffering($oid);
            $grading = $this->labDao->getInstructorGradingStatus($oid);
        } else {
            $labs = $this->labDao->visibleForOffering($oid);
            $user_id = $_SESSION['user']['id'];
            $grading = $this->labDao->getStudentGradingStatus($oid, $user_id);
        }

        $graded = [];
        foreach ($grading as $grade) {
            $graded[$grade['id']] = $grade;
        }
        $labTimes = [];
        foreach ($labs as $lab) {
            $labTimes[] = [
                "lab" => $lab, 
                "start" => strtotime($lab['start']),
                "stop" => strtotime($lab['stop']),
            ];
        }

        $VIEW_DATA['labTimes'] = $labTimes;
        $VIEW_DATA['title'] = 'Labs';
        $VIEW_DATA['area'] = 'lab';
        $VIEW_DATA['graded'] = $graded;
        $VIEW_DATA["isRemembered"] = $_SESSION['user']['isRemembered'];
        return "lab/overview.php";
    }

    #[Post(uri: "$", sec: "instructor")]
    public function addLab()
    {
        $day_id = filter_input(INPUT_POST, "day_id", FILTER_SANITIZE_NUMBER_INT);
        $name = filter_input(INPUT_POST, "name");
        $startdate = filter_input(INPUT_POST, "startdate");
        $stopdate = filter_input(INPUT_POST, "stopdate");
        $starttime = filter_input(INPUT_POST, "starttime");
        $stoptime = filter_input(INPUT_POST, "stoptime");

        $start = "{$startdate} {$starttime}";
        $stop = "{$stopdate} {$stoptime}";
        $id = $this->labDao->add($name, $day_id, $start, $stop);

        return "Location: lab/{$id}/edit"; // edit lab view
    }

    #[Get(uri: "/(\d+)/edit$", sec: "instructor")]
    public function editLab()
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $course_num = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $lab_id = $URI_PARAMS[3];

        $offering = $this->offeringDao->getOfferingByCourse($course_num, $block);
        $days = $this->dayDao->getDays($offering['id']);
        $deliverables = $this->deliverableDao->forLab($lab_id);
        $labPoints = 0;
        foreach ($deliverables as $deliv) {
            $labPoints += $deliv['points'];
        }
        $labzips = $this->attachmentDao->forOffering($offering['id'], "zip");

        $VIEW_DATA['days'] = $days;
        $VIEW_DATA['course'] = $course_num;
        $VIEW_DATA['block'] = $block;
        $VIEW_DATA['offering'] = $offering;
        $VIEW_DATA['lab'] = $this->labDao->byId($lab_id);
        $VIEW_DATA['labPoints'] = $labPoints;
        $VIEW_DATA['deliverables'] = $deliverables;
        $VIEW_DATA['attachments'] = $this->attachmentDao->forLab($lab_id);
        $VIEW_DATA['labzips'] = $labzips;
        $VIEW_DATA['title'] = "Edit Lab";

        return "lab/edit.php";
    }

    #[Get(uri: "/preview$", sec: "instructor")]
    public function previewLab() 
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $course = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $lab_id = filter_input(INPUT_GET, "l", FILTER_SANITIZE_NUMBER_INT);
        $user_id = $_SESSION['user']['id'];
        
        $offering = $this->offeringDao->getOfferingByCourse($course, $block);
        $lab = $this->labDao->byId($lab_id);
        $stopDiff = new DateInterval("PT1H"); // 1 hour

        require_once("lib/Parsedown.php");
        $parsedown = new Parsedown();
        $parsedown->setSafeMode(true);

        $VIEW_DATA['course'] = $course;
        $VIEW_DATA['block'] = $block;
        $VIEW_DATA['offering'] = $offering;
        $VIEW_DATA['lab'] = $lab;
        $VIEW_DATA["parsedown"] = $parsedown;
        $VIEW_DATA['title'] = "Lab: " . $lab['name'];
        $VIEW_DATA['stop'] = $stopDiff;
        $VIEW_DATA['group'] = 'instructor';

        $deliverables = $this->deliverableDao->forLab($lab_id);
        $labPoints = 0;
        $zips = [];
        foreach ($deliverables as $deliv) {
            $labPoints += $deliv['points'];
            if ($deliv['type'] == "zip") {
                $zips[] = $deliv['id'];
            }
        }
        $VIEW_DATA['labPoints'] = $labPoints;
        $VIEW_DATA['deliverables'] = $deliverables;
        $VIEW_DATA['attachments'] = $this->attachmentDao->forLab($lab_id);

        $submission = $this->submissionDao->forUser($user_id, $lab_id);
        $deliveries = $this->deliveryDao->forSubmission($submission['id']);

        $checks = [];
        foreach ($zips as $zip) {
            $checks[$zip] = $this->zipUlCheckDao->forDeliverable($zip);
        }
        $VIEW_DATA['checks'] = $checks;
        $VIEW_DATA['submission'] = $submission;
        $VIEW_DATA['deliveries'] = $deliveries;
        return "lab/doLab.php";
    }

    /**
     * Expects AJAX
     */
    #[Put(uri: "/(\d+)$", sec: "instructor")]
    public function updateLab()
    {
        global $URI_PARAMS;
        global $_PUT;

        $id = $URI_PARAMS[3];
        $visible = $_PUT["visible"];
        $shifted = $_PUT["name"];
        $name = $this->markdownCtrl->ceasarShift($shifted);
        $day_id = $_PUT["day_id"];
        $startdate = $_PUT["startdate"];
        $stopdate = $_PUT["stopdate"];
        $starttime = $_PUT["starttime"];
        $stoptime = $_PUT["stoptime"];
        $type = $_PUT["type"];
        $hasMarkDown = $_PUT["hasMarkDown"];
        $shifted = $_PUT["desc"];
        $desc = $this->markdownCtrl->ceasarShift($shifted);


        $visible = $visible ? 1 : 0;
        $hasMarkDown = $hasMarkDown ? 1 : 0;
        $start = "{$startdate} {$starttime}";
        $stop = "{$stopdate} {$stoptime}";

        $this->labDao->update($id, $visible, $name, $day_id, $start, $stop, $type, $hasMarkDown, $desc);
    }

    /**
     * Expects AJAX
     */
    #[Delete(uri: "/(\d+)$", sec: "instructor")]
    public function deleteLab()
    {
        global $URI_PARAMS;
        $id = $URI_PARAMS[3];

        // fail if lab has submissions 
        $subs = $this->submissionDao->forLab($id);
        if ($subs) {
            http_response_code(400);
            return ["error" => "Lab has submissions"];
        }

        // delete attachments
        $attachments = $this->attachmentDao->forLab($id);
        foreach ($attachments as $attachment) {
            $this->attachmentHlpr->delete($attachment);
            $this->attachmentDao->delete($attachment['id']);
        }
        // delete deliverables
        $this->deliverableDao->deleteAllForLab($id);

        // delete lab
        $this->labDao->delete($id);
    }

    /**
     * Expects AJAX / HTMX
     */
    #[Post(uri: "/(\d+)/attach$", sec: "instructor")]
    public function addAttachment()
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $lab_id = $URI_PARAMS[3];
        $deliverable_id = filter_input(INPUT_POST, "deliverable_id", FILTER_SANITIZE_NUMBER_INT);
        $lab = $this->labDao->byId($lab_id);
        $deliverable = $this->deliverableDao->byId($deliverable_id);

        try {
            $res = $this->attachmentHlpr->process('attachment', $lab, $deliverable);
            $type = "simple";
            if ($res['zip']) {
                $type = "zip";
                $res['type'] = $type;
            }
            $file = $res['file'];
            $name = $res['name'];
            $aid = $this->attachmentDao->add($type, $deliverable_id, $file, $name);
            $res['id'] = $aid;
        } catch (Exception $e) {
            error_log($e);
            http_response_code(500);
            return ["error" => "Failed to add attachment"];
        }

        $VIEW_DATA['attachment'] = $res;

        return "lab/attachment.php";  // attachment view
    }

    /**
     * Expects AJAX
     */
    #[Delete(uri: "/(\d+)/attach/(\d+)$", sec: "instructor")]
    public function delAttachment()
    {
        global $URI_PARAMS;

        $lab_id = $URI_PARAMS[3];
        $id = $URI_PARAMS[4];

        try {
            $attachment = $this->attachmentDao->byId($id);
            $this->downloadDao->deleteForAttachment($id);
            $this->zipDlActionDao->deleteForAttachment($id);
            $this->attachmentDao->delete($id);
            $this->attachmentHlpr->delete($attachment);
        } catch (Exception $e) {
            error_log($e);
            return ["error" => "Failed to remove attachment"];
        }
        return ["id" => $id];
    }

    /**
     * Expects AJAX / HTMX
     */
    #[Post(uri: "/(\d+)/deliverable$", sec: "instructor")]
    public function addDliverable()
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $lab_id = $URI_PARAMS[3];
        $type = filter_input(INPUT_POST, "type");
        $seq = filter_input(INPUT_POST, "seq", FILTER_SANITIZE_NUMBER_INT);

        try {
            $id = $this->deliverableDao->add($lab_id, $type, $seq);
        } catch (Exception $e) {
            error_log($e);
            return ["error" => "Failed to add deliverable"];
        }

        $VIEW_DATA['deliv'] = $this->deliverableDao->byId($id);

        return "lab/deliverable.php";  // deliverable view
    }

    /**
     * Expects AJAX
     */
    #[Delete(uri: "/(\d+)/deliverable/(\d+)$", sec: "instructor")]
    public function delDeliverable()
    {
        global $URI_PARAMS;

        $lab_id = $URI_PARAMS[3];
        $id = $URI_PARAMS[4];

        try {
            $this->deliverableDao->delete($id, $lab_id);
        } catch (Exception $e) {
            error_log($e);
            return ["error" => "Failed to remove deliverable"];
        }
        return ["id" => $id];
    }

    /**
     * Expects AJAX
     */
    #[Put(uri: "/(\d+)/deliverable/(\d+)$", sec: "instructor")]
    public function updateDeliverable()
    {
        global $URI_PARAMS;
        global $_PUT;

        $lab_id = $URI_PARAMS[3];
        $id = $URI_PARAMS[4];
        $points = $_PUT["points"];
        $shifted = $_PUT["desc"];
        $desc = $this->markdownCtrl->ceasarShift($shifted);
        $hasMarkDown = $_PUT["hasMarkDown"];


        $this->deliverableDao->update($id, $lab_id, $points, $desc, $hasMarkDown, null, null);
    }

    /**
     * Expects AJAX
     *
     * This sets the Zip Attachment for a zip type deliverable.
     * With this relationship we can check if a submission is based on a the 
     * zip attachment that the student downloaded (themselves).
     *
     * Once they upload the deliverable we go through the zip_actions to see if
     * the watermarks these actions created during download are present.
     */
    #[Put(uri: "/(\d+)/deliverable/(\d+)/zipAttachment$", sec: "instructor")]
    public function setZipAttachment()
    {
        global $URI_PARAMS;
        global $_PUT;

        $lab_id = $URI_PARAMS[3];
        $id = $URI_PARAMS[4];
        $zipAttachment_id = $_PUT["zipAttachment_id"];

        $this->deliverableDao->setZipAttachment($id, $lab_id, $zipAttachment_id);
    }

    /**
     * Zip Action related code
     */
    #[Get(uri: "/(\d+)/attachment/(\d+)$", sec: "instructor")]
    public function getZipFiles()
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $attachment_id = $URI_PARAMS[4];

        $output = [];
        $zip = new ZipArchive();
        $attachment = $this->attachmentDao->byId($attachment_id);
        if ($zip->open($attachment['file']) !== TRUE) {
            $output[] = "Error: Zip file could not be opened";
            return "lab/options.php";
        }
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            $output[$name] = $name;
        }
        $zip->close();
        $VIEW_DATA['output'] = $output;
        return "lab/options.php";
    }

    #[Get(uri: "/(\d+)/(\d+)/zipActions$", sec: "instructor")]
    public function getZipActions()
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $attachment_id = $URI_PARAMS[4];
        $actions = $this->zipDlActionDao->forAttachment($attachment_id);

        $VIEW_DATA['actions'] = $actions;
        return "lab/zipActions.php";
    }

    #[Post(uri: "/(\d+)/(\d+)/zipActions$", sec: "instructor")]
    public function addZipAction()
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $attachment_id = $URI_PARAMS[4];
        $type = filter_input(INPUT_POST, "type");
        $file = filter_input(INPUT_POST, "file");
        $byte = filter_input(INPUT_POST, "byte", FILTER_SANITIZE_NUMBER_INT);

        $this->zipDlActionDao->add($attachment_id, $type, $file, $byte);

        $VIEW_DATA['actions'] = $this->zipDlActionDao->forAttachment($attachment_id);
        return "lab/zipActions.php";  // zipAction view
    }

    #[Delete(uri: "/(\d+)/zipActions/(\d+)$", sec: "instructor")]
    public function deleteZipAction()
    {
        global $URI_PARAMS;

        $id = $URI_PARAMS[4];
        $this->zipDlActionDao->delete($id);
    }

    /**
     * Zip check related code
     */
    #[Get(uri: "/(\d+)/(\d+)/zipChecks$", sec: "instructor")]
    function getZipChecks()
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $deliverable_id = $URI_PARAMS[4];
        $checks = $this->zipUlCheckDao->forDeliverable($deliverable_id);

        $VIEW_DATA['checks'] = $checks;
        return "lab/zipChecks.php";
    }

    #[Post(uri: "/(\d+)/(\d+)/zipChecks$", sec: "instructor")]
    function addZipCheck()
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $deliverable_id = $URI_PARAMS[4];
        $type = filter_input(INPUT_POST, "type");
        $file = filter_input(INPUT_POST, "file");
        $byte = filter_input(INPUT_POST, "byte", FILTER_SANITIZE_NUMBER_INT);

        if (!$byte || $byte < 0) {
            $byte = 0;
        } 

        $this->zipUlCheckDao->add($deliverable_id, $type, $file, $byte);

        $VIEW_DATA['checks'] = $this->zipUlCheckDao->forDeliverable($deliverable_id);
        return "lab/zipChecks.php";  // zipCheck view
    }

    #[Delete(uri: "/(\d+)/zipChecks/(\d+)$", sec: "instructor")]
    function deleteZipCheck()
    {
        global $URI_PARAMS;

        $id = $URI_PARAMS[4];
        $this->zipUlCheckDao->delete($id);
    }
    
    #[Get(uri: "/report$", sec: "student")]
    public function resultsReport()
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $course = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];

        $offering = $this->offeringDao->getOfferingByCourse($course, $block);
        $enrolled = $this->enrollmentDao->getEnrollmentForOffering($offering['id']);

        // create data two dimensional array and initialize first 3 columns
        $data = [];
        if ($_SESSION['user']['isFaculty']) {
            foreach ($enrolled as $user) {
                if ($user['auth'] == 'instructor' || $user['auth'] == 'observer') {
                    continue;
                }
                $data[$user['id']] = [];
                $data[$user['id']][] = $user['studentID'];
                $data[$user['id']][] = $user['firstname'];
                $data[$user['id']][] = $user['lastname'];
            }
        } else {
            // only give them their own data
            foreach ($enrolled as $user) {
                if ($user['id'] == $_SESSION['user']['id']) {
                    $data[$user['id']] = [];
                    $data[$user['id']][] = $user['studentID'];
                    $data[$user['id']][] = $user['firstname'];
                    $data[$user['id']][] = $user['lastname'];
                }
            }

        }

        $labs = $this->labDao->allForOffering($offering['id']);

        // build CSV header and query for data fetching 
        $count = 1;
        $header = '"studentId","firstName","lastName",';
        foreach ($labs as $lab) {
            // build CSV header line
            $header .= '"' . $lab['abbr'] . '",';

            if ($lab['type'] == 'individual') {
                $pts = $this->labDao->getIndividuallabTotals($lab['id'], $offering['id']);
            } else if ($lab['type'] == 'group') {
                $pts = $this->labDao->getGroupLabTotals($lab['id'], $offering['id']);
            }
            
            // build data column for this lab
            foreach ($pts as $pt) {
                if (isset($data[$pt['user_id']])) {
                    $data[$pt['user_id']][] = number_format($pt['points'], 2);
                }
            }
            $count++;
        }

        $VIEW_DATA['course'] =  $course;
        $VIEW_DATA['block'] = $block;
        $VIEW_DATA['type'] = 'lab';
        $VIEW_DATA['colCount'] = $count + 3; // 3 are sid, first, last
        $VIEW_DATA['header'] = $header;
        $VIEW_DATA['data'] = $data;

        return "csv.php";
    }
}
