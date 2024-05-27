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

    #[Inject('ZipDlActionDao')]
    public $zipDlActionDao;

    #[Inject('DownloadDao')]
    public $downloadDao;

    #[Get(uri: "$", sec: "observer")]
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

        // integrate the labs data into the days data
        foreach ($VIEW_DATA['days'] as $day) {
            $day['labs'] = array();
        }
        foreach ($labs as $lab) {
            $VIEW_DATA['days'][$lab['abbr']]['labs'][] = $lab;
        }

        $VIEW_DATA['title'] = 'Labs';
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
        $VIEW_DATA['lab'] = $this->labDao->byId($lab_id);
        $VIEW_DATA['labPoints'] = $labPoints;
        $VIEW_DATA['deliverables'] = $deliverables;
        $VIEW_DATA['attachments'] = $this->attachmentDao->forLab($lab_id);
        $VIEW_DATA['labzips'] = $labzips;
        $VIEW_DATA['title'] = "Edit Lab";

        return "lab/edit.php";
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
        $name = $_PUT["name"];
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

        $id = $URI_PARAMS[3];

        try {
            $res = $this->attachmentHlpr->process('attachment', $id);
            $type = "simple";
            if ($res['zip']) {
                $type = "zip";
                $res['type'] = $type;
            }
            $file = $res['file'];
            $name = $res['name'];
            $aid = $this->attachmentDao->add($type, $id, $file, $name);
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
            $this->attachmentDao->delete($id, $lab_id);
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
}
