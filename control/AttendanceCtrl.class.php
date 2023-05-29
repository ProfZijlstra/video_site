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
     * @Inject("OverviewHlpr")
     */
    public $overviewCtr;
    /**
     * @Inject("ClassSessionDao")
     */
    public $classSessionDao;
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
     * @Inject("ExcusedDao")
     */
    public $excusedDao;

    /**
     * @GET(uri="!^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/attendance$!", sec="assistant")
     */
    public function overview() {
        // We're building on top of  overview -- run it first
        // this populates $VIEW_DATA with the overview related data
        $this->overviewCtr->overview();

        global $VIEW_DATA;

        $offering_id = $VIEW_DATA["offering_id"];
        $days = $VIEW_DATA["days"];

        // get sessions for these days
        $sessions = $this->classSessionDao->allForOffering($offering_id);
        foreach ($sessions as $session) {
            $session["meetings"] = [];
            $days[$session["abbr"]][$session["type"]] = $session;
        }

        // Add attendance data
        $meetings = $this->meetingDao->allForOffering($offering_id);
        foreach ($meetings as $meeting) {
            $days[$meeting["abbr"]][$meeting["stype"]]["meetings"][] = $meeting;
        }

        $enrollment = $this->enrollmentDao->getEnrollmentForOffering($offering_id);
        $excused_raw = $this->excusedDao->allForOffering($offering_id);
        $excused = [];
        foreach ($excused_raw as $student) {
            if (!isset($excused[$student['class_session_id']])) {
                $excused[$student['class_session_id']] = [];
            }
            $excused[$student['class_session_id']][] = $student;
        }


        $VIEW_DATA["days"] = $days;
        $VIEW_DATA["title"] = "Attendance";
        $VIEW_DATA['enrollment'] = $enrollment;
        $VIEW_DATA['excused'] = $excused;

        return "attendance/attendance.php";
    }

    /**
     *  @POST(uri="!^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/excuse$!", sec="assistant")
     */
    public function excuseAbsence() {
        $session_id = filter_input(INPUT_POST, "session_id", FILTER_SANITIZE_NUMBER_INT);
        $teamsName = filter_input(INPUT_POST, "teamsName");
        $this->excusedDao->add($session_id, $teamsName);
        return "Location: attendance";
    }

    /**
     * Expects AJAX 
     * 
     * @POST(uri="!^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/delExcuse$!", sec="assistant")
     */
    public function deleteExcuse() {
        $session_id = filter_input(INPUT_POST, "session_id", FILTER_SANITIZE_NUMBER_INT);
        $teamsName = filter_input(INPUT_POST, "teamsName");
        $this->excusedDao->delete($session_id, $teamsName);
    }

    /**
     * @GET(uri="!^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/professionalism$!", sec="assistant")
     */
    public function professionalismReport() {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $course_number = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];

        $offering = $this->offeringDao->getOfferingByCourse($course_number, $block);
        $prof_data = $this->attendanceDao->professionalism($offering['id']);

        $professionals = [];
        foreach($prof_data as $student) {
            $professional = [];
            $professional["id"] = $student["studentID"];
            $professional["name"] = $student['knownAs'] . " " . $student['lastname'];
            $professional["inClass"] = $student["inClass"];
            $professional["absent"] = $student["absent"];
            $professional["middleMissing"] = $student["middleMissing"];
            $professional["late"] = $student["late"];
            $professional["minsLate"] = $student["minsLate"];
            $professional["leaveEarly"] = $student["leaveEarly"];
            $professional["minsLeave"] = $student["minsLeave"];

            $late_secs = strtotime('1970-01-01 ' . $student["minsLate"] . 'GMT');
            $leave_secs = strtotime('1970-01-01 ' . $student["minsLeave"] . 'GMT');
            $absent_secs = $student["absent"] * 1800; // half hour per absent
            $midmis_secs = $student["middleMissing"] * 300; // 5 mins per mid miss
            $total_secs = $late_secs + $leave_secs + $absent_secs + $midmis_secs;
            $professional["totalSecs"] = $total_secs;
            $professional["total"] = gmdate("H:i:s", $total_secs);
            $professionals[] = $professional;
        }
        usort($professionals, "AttendanceCtrl::byTotal");

        $VIEW_DATA["course"] = $course_number;
        $VIEW_DATA["block"] = $block;
        $VIEW_DATA["professionals"] = $professionals;
        $VIEW_DATA["title"] = "Professionalism";

        return "attendance/professionalism.php";
    }

    private static function byTotal($a, $b) {
        return $b["totalSecs"] - $a["totalSecs"];
    }

    /**
     * @GET(uri="!^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/physical/(W\d+)$!", sec="assistant")
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
        $VIEW_DATA["title"] = $week . " Physical Attendance";
        return "attendance/physical.php";
    }

    /**
     * Expects AJAX
     * 
     * @POST(uri="!^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/physical/(W\d+)/email$!", sec="assistant")
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
            mail($to, "Unexcused Absence", $message, $headers);
        }
    }

    /**
     * @GET(uri="!^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/attendance/(W\d+D\d+)/(AM|PM)$!", sec="assistant")
     */
    public function exportReport() {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $course_number = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $day_abbr = $URI_PARAMS[3];
        $stype = $URI_PARAMS[4]; // AM or  PM

        $session = $this->classSessionDao->getSession(
            $course_number, $block, $day_abbr, $stype);
        $status = $this->classSessionDao->calcStatus($session['id']);

        if ($session['generated'] != $status['meetings'] || 
                ($session["status"] != "GENERATED" && 
                 $session["status"] != "EXPORTED")) {
            $this->generateExportReport($session["id"]);
            $session = $this->classSessionDao->getSessionById($session["id"]);
        }
        $exports = $this->attendanceExportDao->forSession($session["id"]);

        $VIEW_DATA['course'] = $course_number;
        $VIEW_DATA['block'] = $block;
        $VIEW_DATA['stype'] = $stype;
        $VIEW_DATA['session'] = $session;
        $VIEW_DATA['exports'] = $exports;
        $VIEW_DATA['title'] = $day_abbr . " " .$stype . " Attendance Export";

        return "attendance/attendanceExport.php";
    }

    /**
     * @POST(uri="!^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/attendance/(W\d+D\d+)/(AM|PM)$!", sec="assistant")
     */
    public function regenExportReport() {
        global $URI_PARAMS;

        $course_number = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $day_abbr = $URI_PARAMS[3];
        $stype = $URI_PARAMS[4]; // AM or  PM

        $session = $this->classSessionDao->getSession(
            $course_number, $block, $day_abbr, $stype);

        $this->generateExportReport($session['id']);
        return "Location: $stype";
    }

    /**
     * Expects AJAX
     * 
     * @POST(uri="!^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/attendance/W[1-4]D[1-6]/(AM|PM)/(\d+)$!", sec="assistant")
     */
    public function updateExportRow() {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $this->attendanceExportDao->update($data);
    }

    private function generateExportReport($session_id) {
        // update session with status, start, stop, meetings
        $stats = $this->classSessionDao->calcStatus($session_id);
        $stats["status"] = "GENERATED";
        if ($stats["meetings"] == 0) {
            $stats["status"] = "NO_DATA";
        }
        $this->classSessionDao->setStatus($stats);
        if ($stats['status'] == "NO_DATA") {
            return; // nothing to do
        }

        // get enrollemnt for offering
        $offering_id = $this->classSessionDao->getOfferingId($session_id);
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
