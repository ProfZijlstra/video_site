<?php

/**
 * Attendance Controller Class
 * @author mzijlstra 2021-11-29
 *
 * @Controller
 */
class AttendanceCtrl
{
    /**
     * @Inject("VideoCtrl")
     */
    public $videoCtrl;
    /**
     * @Inject("SessionDao")
     */
    public $sessionDao;
    /**
     * @Inject("MeetingDao")
     */
    public $meetingDao;
    /**
     * @Inject("OfferingDao")
     */
    public $offeringDao;
    /**
     * @Inject("EnrollmentDao")
     */
    public $enrollmentDao;
    /**
     * @Inject("AttendanceDao")
     */
    public $attendanceDao;
    /**
     * @Inject("AttendanceExportDao")
     */
    public $attendanceExportDao;

    /**
     * @GET(uri="!^/(cs\d{3})/(20\d{2}-\d{2})/attendance$!"", sec="admin")
     */
    public function overview()
    {
        global $VIEW_DATA;

        // We're going to build on top of offering overview -- run it first
        // this populates $VIEW_DATA with the overview related data
        $this->videoCtrl->offering();
        $days = $VIEW_DATA["days"];

        // get sessions for these days
        $sessions = $this->sessionDao->allForOffering($VIEW_DATA["offering"]["id"]);
        foreach ($sessions as $session) {
            $session["meetings"] = [];
            $days[$session["abbr"]][$session["type"]] = $session;
        }

        // Add attendance data
        $meetings = $this->meetingDao->allForOffering($VIEW_DATA["offering"]["id"]);
        foreach ($meetings as $meeting) {
            $days[$meeting["abbr"]][$meeting["stype"]]["meetings"][] = $meeting;
        }
        $VIEW_DATA["days"] = $days;

        return "attendance.php";
    }

    /**
     * @GET(uri="!^/(cs\d{3})/(20\d{2}-\d{2})/physical/(W[1-4])$!"", sec="admin")
     */
    public function physicalAttendanceReport() {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $course_number = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $week = $URI_PARAMS[3];

        $offering = $this->offeringDao->getOfferingByCourse($course_number, $block);
        $attend = $this->attendanceExportDao->getPhysicalAttendance($offering['id'], $week);

        $VIEW_DATA["week"] = $week;
        $VIEW_DATA["course"] = $course_number;
        $VIEW_DATA["block"] = $block;
        $VIEW_DATA["attend"] = $attend;

        return "physical.php";
    }

    /**
     * @POST(uri="!^/(cs\d{3})/(20\d{2}-\d{2})/physical/(W[1-4])/email$!"", sec="admin")
     */
    public function emailLowPhysical() {
        global $URI_PARAMS;
        $course_number = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $week = $URI_PARAMS[3];
		$minPhys = filter_input(INPUT_POST, "minPhys", FILTER_SANITIZE_NUMBER_INT);

        $offering = $this->offeringDao->getOfferingByCourse($course_number, $block);
        $below = $this->attendanceExportDao->internationalPhysicalBelow($offering['id'], $week, $minPhys);

        $headers = 'From: "Manalabs Video System" <videos@manalabs.org> \r\n' .
        "Reply-To:<mzijlstra@miu.edu> ";
        foreach ($below as $student) {
            $to = $student['email'];
            $knownAs =trim($student['knownAs']);
            $inClass = $student['inClass']; 
            $remaining = $minPhys - $inClass;
            $message = 
"Hi $knownAs,

Our records indicate that you physically attended $inClass session(s). This is 
below the required minimum of $minPhys in-class sessions per week.

Please be sure to attend $remaining more session(s) before the weekend. 

With kind regards,

Manalabs Attendance System.

";
            echo $message;
            mail($to, "Unexcused Absence", $message, $headers);
        }
    }

    /**
     * @GET(uri="~^/(cs\d{3})/(20\d{2}-\d{2})/attendance/(W[1-4]D[1-6])/(AM|PM)$~", sec="admin")
     */
    public function exportReport() {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $course_number = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $day_abbr = $URI_PARAMS[3];
        $stype = $URI_PARAMS[4]; // AM or  PM

        $session = $this->sessionDao->getSession(
            $course_number, $block, $day_abbr, $stype);
        $status = $this->sessionDao->calcStatus($session['id']);

        if ($session['generated'] != $status['meetings'] || 
                ($session["status"] != "GENERATED" && 
                 $session["status"] != "EXPORTED")) {
            $this->generateExportReport($session["id"]);
            $session = $this->sessionDao->getSessionById($session["id"]);
        }
        $exports = $this->attendanceExportDao->forSession($session["id"]);

        $VIEW_DATA['course'] = $course_number;
        $VIEW_DATA['block'] = $block;
        $VIEW_DATA['day_abbr'] = $day_abbr;
        $VIEW_DATA['stype'] = $stype;
        $VIEW_DATA['session'] = $session;
        $VIEW_DATA['exports'] = $exports;

        return "attendanceExport.php";
    }

    /**
     * @POST(uri="~^/(cs\d{3})/(20\d{2}-\d{2})/attendance/(W[1-4]D[1-6])/(AM|PM)$~", sec="admin")
     */
    public function regenExportReport() {
        global $URI_PARAMS;

        $course_number = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $day_abbr = $URI_PARAMS[3];
        $stype = $URI_PARAMS[4]; // AM or  PM

        $session = $this->sessionDao->getSession(
            $course_number, $block, $day_abbr, $stype);

        $this->generateExportReport($session['id']);
        return "Location: $stype";
    }

    /**
     * @POST(uri="~^/cs\d{3}/20\d{2}-\d{2}/attendance/W[1-4]D[1-6]/(AM|PM)/(\d+)$~", sec="admin")
     */
    public function updateExportRow() {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $this->attendanceExportDao->update($data);
    }

    private function generateExportReport($session_id) {
        // update session with status, start, stop, meetings
        $stats = $this->sessionDao->calcStatus($session_id);
        $stats["status"] = "GENERATED";
        if ($stats["meetings"] == 0) {
            $stats["status"] = "NO_DATA";
        }
        $this->sessionDao->setStatus($stats);
        if ($stats['status'] == "NO_DATA") {
            return; // nothing to do
        }

        // get enrollemnt for offering
        $offering_id = $this->sessionDao->getOfferingId($session_id);
        $enrollment = $this->enrollmentDao->getEnrollmentForOffering($offering_id);

        // make an enrolled lookup table
        $enrolled = [];
        foreach ($enrollment as $student) {
            $enrolled[$student["studentID"]] = true;
        }

        // for each enrolled need: studentId, status, inClass, comment
        // where status one of: present, excused, absent, late, left early, other
        // where status other only used if middle_missing (and nothing else)
        // where comment contains: minutes late, minutes left early, middle missing
        $attendance = $this->attendanceDao->getExportData($session_id);
        $exports = [];
        foreach ($attendance as $attendant) {
            if ($enrolled[$attendant["studentID"]]) {
                $export = [];
                $export["studentID"] = $attendant["studentID"];
                $export["status"] = $this->getStatus($attendant);
                $export["inClass"] = $attendant["inClass"];
                $export["comment"] = $this->getComment($attendant);
                $exports[] = $export;
            }
        }
        $this->attendanceExportDao->clear($session_id);
        $this->attendanceExportDao->create($session_id, $exports);
    }

    private function getStatus($attendant) {
        if ($attendant["excused"]) {
            return "excused";
        } else if ($attendant["absent"]) {
            return "absent";
        } else if ($attendant["late"]) {
            return "late";
        } else if ($attendant["leaveEarly"]) {
            return "left early";
        } else if ($attendant["middleMissing"]) {
            return "other";
        } else {
            return "present";
        }
    }

    private function getComment($attendant) {
        $comments = [];
        if ($attendant["absent"]) {
            $comments[] = "absent";
        } 
        if ($attendant["late"]) {
            $mins = $attendant["minsLate"];
            $mins = substr($mins, $this->timePos($mins));
            $comments[] = "late: $mins mins" ; 
        } 
        if ($attendant["leaveEarly"]) {
            $mins = $attendant["minsLeave"];
            $mins = substr($mins, $this->timePos($mins));
            $comments[] = "left early: $mins mins";
        } 
        if ($attendant["middleMissing"]) {
            $comments[] =  "missed middle";
        }        
        return implode(", ", $comments);
    }

    private function timePos($time) {
        $i = 0;
        while ($time[$i] == "0" || $time[$i] == ":") {
            $i++;
        }
        return $i;
    }
}
