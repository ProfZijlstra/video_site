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
        } else { // the lab is open
        }
    }
}
