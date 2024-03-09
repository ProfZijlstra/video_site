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

    #[Inject('MarkdownHlpr')]
    public $markdownHlpr;

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

        if ($startDiff->invert === 0) { // start is in the future
            // show countdown page
            $VIEW_DATA['title'] = "Lab Countdown";
            $VIEW_DATA['start'] = $startDiff;
            return "lab/countdown.php";
        } else if ($stopDiff->invert === 1) { // stop is in the past
            // TODO: implement the lab closed / grade status page
        } else { // the lab is open
            if ($lab['type'] == "group") {
                // get the group
                $enroll = $this->enrollmentDao->getEnrollment($user_id, $course, $block);
                $group = $enroll['group'] ?? null;
                if (!$group && $enroll['auth'] == 'instructor') {
                    $group = 'instructor';
                }

                if ($group == null) {
                    // TODO: implement this error page
                    $VIEW_DATA['title'] = "Lab: " . $lab['name'];
                    $VIEW_DATA['error'] = "You need to be in a group for this lab";
                    return "lab/error.php";
                }

                // get the submission (or null)
                $submission = $this->submissionDao->forGroup($group, $lab_id);
            } else { // type == "individual"
                $submission = $this->submissionDao->forUser($user_id, $lab_id);
            }

            $delivered = [];
            if ($submission) {
                $delivered = $this->deliveryDao->forSubmission($submission['id']);
            }

            $deliverables = $this->deliverableDao->forLab($lab_id);
            $labPoints = 0;
            foreach ($deliverables as $deliv) {
                $labPoints += $deliv['points'];
            }
            $typeDesc = [
                'txt' => 'Type text into the textbox',
                'img' => 'Upload an image',
                'pdf' => 'Upload a pdf file',
                'url' => 'Write a URL in the text field',
                'zip' => 'Upload a code zip file',
            ];

            require_once("lib/Parsedown.php");
            $VIEW_DATA['title'] = "Lab: " . $lab['name'];
            $VIEW_DATA['stop'] = $stopDiff;
            $VIEW_DATA['group'] = $group;
            $VIEW_DATA['labPoints'] = $labPoints;
            $VIEW_DATA["parsedown"] = new Parsedown();
            $VIEW_DATA['attachments'] = $this->attachmentDao->forLab($lab_id);
            $VIEW_DATA['typeDesc'] = $typeDesc;
            $VIEW_DATA['deliverables'] = $deliverables;
            $VIEW_DATA['submission'] = $submission;
            $VIEW_DATA['delivered'] = $delivered;
            return "lab/doLab.php";
        }
    }

    /**
     * Expects AJAX
     */
    #[Post(uri: "/(\d+)/txt$", sec: "student")]
    public function addTxt()
    {
        global $URI_PARAMS;

        $lab_id = $URI_PARAMS[3];
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
        $stuComment = $this->markdownHlpr->ceasarShift($stuShifted);
        $hasMarkDown = $hasMarkDown ? 1 :  0;
        $stuCmntHasMD = $stuCmntHasMD ? 1 : 0;

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

        $delivery_id = $URI_PARAMS[4];
        $completion = $_PUT["completion"];
        $duration =   $_PUT["duration"];
        $shifted =    $_PUT["text"];
        $text = $this->markdownHlpr->ceasarShift($shifted);
        $hasMarkDown = $_PUT["hasMarkDown"];

        $stuShifted =  $_PUT["stuComment"];
        $stuCmntHasMD = $_PUT["stuCmntHasMD"];
        $stuComment = $this->markdownHlpr->ceasarShift($stuShifted);

        $hasMarkDown = $hasMarkDown ? 1 : 0;
        $stuCmntHasMD = $stuCmntHasMD ? 1 : 0;

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
        $submission_id = filter_input(INPUT_POST, "submission_id", FILTER_VALIDATE_INT);
        $deliverable_id = filter_input(INPUT_POST, "deliverable_id", FILTER_VALIDATE_INT);
        $group = filter_input(INPUT_POST, "group");
        $completion = filter_input(INPUT_POST, "completion", FILTER_VALIDATE_INT);
        $duration = filter_input(INPUT_POST, "duration");
        $url = filter_input(INPUT_POST, "url");
        $stuShifted = filter_input(INPUT_POST, "stuComment");
        $stuCmntHasMD = filter_input(INPUT_POST, "stuCmntHasMD", FILTER_VALIDATE_BOOLEAN);
        $stuComment = $this->markdownHlpr->ceasarShift($stuShifted);
        $stuCmntHasMD = $stuCmntHasMD ? 1 : 0;

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

        $delivery_id = $URI_PARAMS[4];
        $completion = $_PUT["completion"];
        $duration =   $_PUT["duration"];
        $url = $_PUT["url"];
        $stuShifted =  $_PUT["stuComment"];
        $stuComment = $this->markdownHlpr->ceasarShift($stuShifted);
        $stuCmntHasMD = $_PUT["stuCmntHasMD"] ? 1 : 0;

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
}
