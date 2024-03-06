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

    #[Inject('DeliversDao')]
    public $deliversDao;

    #[Inject('AttachmentDao')]
    public $attachmentDao;

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
            if ($lab['type'] == "Group") {
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
            } else { // type == "Individual"
                $submission = $this->submissionDao->forUser($user_id, $lab_id);
            }

            $delivers = [];
            if ($submission) {
                $delivers = $this->deliversDao->forSubmission($submission['id']);
            }

            require_once("lib/Parsedown.php");
            $VIEW_DATA['title'] = "Lab: " . $lab['name'];
            $VIEW_DATA['stop'] = $stopDiff;
            $VIEW_DATA["parsedown"] = new Parsedown();
            $VIEW_DATA['attachments'] = $this->attachmentDao->forLab($lab_id);
            $VIEW_DATA['deliverables'] = $this->deliverableDao->forLab($lab_id);
            $VIEW_DATA['submission'] = $submission;
            $VIEW_DATA['delivers'] = $delivers;
            return "lab/doLab.php";
        }
    }
}
