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
     * @Inject("MeetingDao")
     */
    public $meetingDao;
    /**
     * @Inject("AttendanceDataDao")
     */
    public $attendanceDataDao;
    /**
     * @Inject("AttendanceDao")
     */
    public $attendanceDao;
    /**
     * @Inject("VideoCtrl")
     */
    public $videoCtrl;
    /**
     * @Inject("EnrollmentDao")
     */
    public $enrollmentDao;
    /**
     * @Inject("DayDao")
     */
    public $dayDao;
    /**
     * @Inject("OfferingDao")
     */
    public $offeringDao;
    /**
     * @Inject("SessionDao")
     */
    public $sessionDao;


    /**
     * @GET(uri="|^/(cs\d{3})/(20\d{2}-\d{2})/attendance$|", sec="admin")
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
     * @GET(uri="|^/(cs\d{3})/(20\d{2}-\d{2})/meeting/(\d+)$|", sec="admin")
     */
    public function getMeeting()
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $course_number = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];

        $meeting_id = $URI_PARAMS[3];
        $meeting = $this->meetingDao->get($meeting_id);
        $attendance = $this->attendanceDao->forMeeting($meeting_id);

        $offering = $this->offeringDao->getOfferingByCourse($course_number, $block);
        $enrollment = $this->enrollmentDao->getEnrollmentForOffering($offering['id']);

        $visitors = [];
        $absent = [];
        $present = [];

        foreach ($attendance as $student) {
            if ($student["notEnrolled"]) {
                $visitors[] = $student;
            } else if ($student["absent"]) {
                $absent[] = $student;
            } else {
                $present[] = $student;
            }
        }

        $VIEW_DATA["course"] = $course_number;
        $VIEW_DATA["block"] = $block;
        $VIEW_DATA["offering_id"] = $offering["id"];
        $VIEW_DATA["meeting"] = $meeting;
        $VIEW_DATA["visitors"] = $visitors;
        $VIEW_DATA["absent"] = $absent;
        $VIEW_DATA["present"] = $present;

        return "meeting.php";
    }

    /**
     * @POST(uri="|^/(cs\d{3})/(20\d{2}-\d{2})/meeting/(\d+)$|", sec="admin")
     */
    public function updMeeting()
    {
        $meeting_id = filter_input(INPUT_POST, "id", FILTER_SANITIZE_NUMBER_INT);
        $title = filter_input(INPUT_POST, "title", FILTER_SANITIZE_STRING);
        $date = filter_input(INPUT_POST, "date", FILTER_SANITIZE_STRING);
        $start = filter_input(INPUT_POST, "start", FILTER_SANITIZE_STRING);
        $stop = filter_input(INPUT_POST, "stop", FILTER_SANITIZE_STRING);
        $weight = filter_input(INPUT_POST, "weight", FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        $this->meetingDao->update($meeting_id, $title, $date, $start, $stop, $weight);

        return "Location: $meeting_id";
    }

    /**
     * @POST(uri="|^/(cs\d{3})/(20\d{2}-\d{2})/meeting/regen/(\d+)$|", sec="admin")
     */
    public function regenReport()
    {
        $offering_id = filter_input(INPUT_POST, "offering_id", FILTER_SANITIZE_NUMBER_INT);
        $meeting_id = filter_input(INPUT_POST, "meeting_id", FILTER_SANITIZE_NUMBER_INT);
        $start = filter_input(INPUT_POST, "start", FILTER_SANITIZE_STRING);
        $stop = filter_input(INPUT_POST, "stop", FILTER_SANITIZE_STRING);
        $this->generateReport($offering_id, $meeting_id, $start, $stop);

        return "Location: ../$meeting_id";
    }

    /**
     * @POST(uri="|^/(cs\d{3})/(20\d{2}-\d{2})/meeting/attend/(\d+)$|", sec="admin")
     */
    public function updateAttendance()
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $this->attendanceDao->update($data);
    }

    /**
     * @POST(uri="|^/cs\d{3}/20\d{2}-\d{2}/meeting/(\d+)/absent$|", sec="admin")
     */
    public function markAbsent()
    {
        global $URI_PARAMS;

        $meeting_id = $URI_PARAMS[1];
        $attendance_id = filter_input(INPUT_POST, "attendance_id", FILTER_SANITIZE_NUMBER_INT);
        $this->attendanceDao->markAbsent($attendance_id, 1);

        return "Location: ../$meeting_id#$attendance_id";
    }

    /**
     * @POST(uri="|^/cs\d{3}/20\d{2}-\d{2}/meeting/(\d+)/present$|", sec="admin")
     */
    public function markPresent()
    {
        global $URI_PARAMS;

        $meeting_id = $URI_PARAMS[1];
        $attendance_id = filter_input(INPUT_POST, "attendance_id", FILTER_SANITIZE_NUMBER_INT);
        $this->attendanceDao->markAbsent($attendance_id, 0);

        return "Location: ../$meeting_id#$attendance_id";
    }

    /**
     * @POST(uri="|^/cs\d{3}/20\d{2}-\d{2}/meeting/(\d+)/emailAbsent$|", sec="admin")
     */
    public function emailAbsent() {
        global $URI_PARAMS;
        $meeting_id = $URI_PARAMS[1];
        $headers = 'From: "Manalabs Video System" <videos@manalabs.org> \r\n' .
        "Reply-To:<mzijlstra@miu.edu> ";
        $template = 
"

If you let your instructor know ahead of time when you are unable to attend it 
is possible to have an excused absence.

As is this unexcused absence will be added to your professionalism record
for this course.

Please make sure this does not happen again!

With kind regards,

Manalabs Attendance System.

";
        // get all unexcused absences for this meeting
        $absentees = $this->attendanceDao->unexcusedAbsentForMeeting($meeting_id);

        // for each absent student
        foreach ($absentees as $absent) {
            $to = $absent["email"];
            $message = 
"Hi " . trim($absent['knownAs']) . ",

We noticed you were absent from the ". $absent["title"]." meeting from its start
 at: ". $absent["start"]. " trough its end at: " . $absent["stop"]. "." .$template;

            echo $message;
            mail($to, "Unexcused Absence", $message, $headers);
        }
    }

    /**
     * @POST(uri="|^/cs\d{3}/20\d{2}-\d{2}/meeting/(\d+)/emailTardy$|", sec="admin")
     */
    public function emailTardy() {
        global $URI_PARAMS;
        $meeting_id = $URI_PARAMS[1];
        $headers = 'From: "Manalabs Video System" <videos@manalabs.org> \r\n' .
        "Reply-To:<mzijlstra@miu.edu> ";
        $template = 
"
If you let your instructor know ahead of time when you are unable to attend it 
is possible to have an excused absence.

As is, the minutes of unexcused absence will be added to your professionalism 
record for this course.

Please make sure this does not happen again!

With kind regards,

Manalabs Attendance System.

";
        // get all unexcused absences for this meeting
        $tardies = $this->attendanceDao->unexcusedTardyForMeeting($meeting_id);

        // for each absent student
        foreach ($tardies as $tardy) {
            $to = $tardy["email"];
            $message = 
"Hi " . trim($tardy['knownAs']) . ",

We noticed you were tardy for the ". $tardy["title"]." meeting that started at: 
". $tardy["start"]. " and stopped at: " . $tardy["stop"]. " 

";

            if ($tardy["arriveLate"]) {
                $message .= "\t-You arrived at: " . $tardy['arrive'] ."\n";
            }
            if ($tardy["middleMissing"]) {
                $message .= "\t-You missed a significant part in the middle of the meeting\n";
            }
            if ($tardy["leaveEarly"]) {
                $message .= "\t-You left early at: " . $tardy['left'] . "\n";
            }
            $message .= $template;

            echo $message;
            mail($to, "Unexcused Absence", $message, $headers);
        }
    }


    /**
     * @POST(uri="|^/(cs\d{3})/(20\d{2}-\d{2})/attendance$|", sec="admin")
     */
    public function addMeeting()
    {
        $session_id = filter_input(INPUT_POST, "session_id", FILTER_SANITIZE_NUMBER_INT);
        $start = filter_input(INPUT_POST, "start");
        if ($session_id && $_FILES["list"]) {
            $this->parseMeetingFile(
                $_FILES["list"]["tmp_name"],
                $_FILES["list"]["name"],
                $session_id, 
                $start
            );
        }

        return "Location: attendance";
    }

    /**
     * @POST(uri="|^/(cs\d{3})/(20\d{2}-\d{2})/meeting$|", sec="admin")
     */
    public function createMeeting() {
        $session_id = filter_input(INPUT_POST, "session_id", FILTER_SANITIZE_NUMBER_INT);
        $title = filter_input(INPUT_POST, "title");
        $date  = filter_input(INPUT_POST, "date");
        $start = filter_input(INPUT_POST, "start");
        $stop  = filter_input(INPUT_POST, "stop");

        // insert meeting into DB 
        $meeting_id = $this->meetingDao->add(
            $session_id,
            $title,
            $date,
            $start,
            $stop
        );
        
        // attach all enrolled students as 'present', no tardies
        $day = $this->sessionDao->getOfferingId($session_id);
        $enrolled = $this->enrollmentDao->getEnrollmentForOffering($day["offering_id"]);
        $attendance = [];
        foreach ($enrolled as $attendant) {
            $attendance[$attendant["teamsName"]] = [
                "notEnrolled" => 0,
                "absent" => 0,
                "arriveLate" => 0,
                "leaveEarly" => 0,
                "middleMissing" => 0
            ];
        }
        $this->attendanceDao->addReport($meeting_id, $attendance);

        return "Location: attendance";
    }

    /**
     * @POST(uri="|^/cs\d{3}/20\d{2}-\d{2}/meeting/(\d+)/delete$|", sec="admin")
     */
    public function deleteMeeting() {
        global $URI_PARAMS;

        $meeting_id = $URI_PARAMS[1];
        $this->attendanceDataDao->deleteForMeeting($meeting_id);
        $this->attendanceDao->deleteForMeeting($meeting_id);
        $this->meetingDao->delete($meeting_id);

        return "Location: ../../attendance";
    }

    private function parseMeetingFile($file, $filename, $session_id, $meeting_start)
    {
        // prepare file contents
        $text = mb_convert_encoding(file_get_contents($file), "UTF-8", "UTF-16LE");
        $lines = explode("\n", $text);

        // gather meeting data 
        $title = substr($filename, 0, strlen($filename) - 4);
        $fields = str_getcsv($lines[3], "\t");
        $date = $this->toIsoDate($fields[1]);
        $fields = str_getcsv($lines[4], "\t");
        $meeting_stop = $this->to24hour($fields[1]);

        // insert meeting into DB 
        $meeting_id = $this->meetingDao->add(
            $session_id,
            $title,
            $date,
            $meeting_start,
            $meeting_stop
        );

        // insert attendance lines
        for ($i = 8; $i < count($lines) - 1; $i++) {
            list($name, $start, $stop, $duration, $email, $role) =
                str_getcsv($lines[$i], "\t");
            $start = $this->to24hour($start);
            $stop = $this->to24hour($stop);

            $this->attendanceDataDao->add($meeting_id, $name, $start, $stop);
        }

        // generate report
        $day = $this->sessionDao->getOfferingId($session_id);
        $this->generateReport($day["offering_id"], $meeting_id, $meeting_start, $meeting_stop);
    }

    private function generateReport($offering_id, $meeting_id, $start, $stop)
    {
        // error margin -- how many minutes students can be late without trouble
        $margin = 3 * 60; // 3 minutes

        // get initial data
        $attendants = $this->attendanceDataDao->forMeeting($meeting_id);
        $enrolled = $this->enrollmentDao->getEnrollmentForOffering($offering_id);

        // put enrolled in hashmap for quick lookup
        $enrollment = [];
        foreach ($enrolled as $student) {
            $enrollment[$student["teamsName"]]  = true;
        }

        // find notEnrolled attendants (while constructing attendance array)
        $attendance = [];
        foreach ($attendants as $attendant) {
            $attendance[$attendant["teamsName"]] = [
                "notEnrolled" => 0,
                "absent" => 0,
                "arriveLate" => 0,
                "leaveEarly" => 0,
                "middleMissing" => 0
            ];

            if (!$enrollment[$attendant["teamsName"]]) {
                $attendance[$attendant["teamsName"]]["notEnrolled"] = 1;
            }
        }

        // find / add absent students
        foreach ($enrolled as $student) {
            if (!$attendance[$student["teamsName"]]) {
                $attendance[$student["teamsName"]] = [
                    "notEnrolled" => 0,
                    "absent" => 1,
                    "arriveLate" => 0,
                    "leaveEarly" => 0,
                    "middleMissing" => 0
                ];
            }
        }

        // mark those that arrived late and those that left early
        $attendants = $this->attendanceDataDao->uniqueUsersForMeeting($meeting_id);

        $start = strtotime($start) + $margin;
        $stop = strtotime($stop) - $margin;

        foreach ($attendants as $attendant) {
            if (strtotime($attendant["start"]) > $start) {
                $attendance[$attendant["teamsName"]]["arriveLate"] = 1;
            }
            if (strtotime($attendant["stop"]) < $stop) {
                $attendance[$attendant["teamsName"]]["leaveEarly"] = 1;
            }
        }

        // for those with multiple entrires check if middle missing 
        // by checking for a lack of duration
        $attendants = $this->attendanceDataDao->multiEntryForMeeting($meeting_id);
        $meeting_duration = $stop - $start;
        foreach ($attendants as $attendant) {
            if ($attendant['duration'] < $meeting_duration) {
                $attendance[$attendant["teamsName"]]['middleMissing'] = 1;
            }
        }

        // remove previous report (if in DB)
        $this->attendanceDao->remove($meeting_id);

        // insert attendance report into DB
        $this->attendanceDao->addReport($meeting_id, $attendance);
    }

    private function to24hour($str)
    {
        $parts = date_parse($str);
        return $parts["hour"] . ":" . $parts["minute"] . ":" . $parts["second"];
    }

    private function toIsoDate($str)
    {
        $parts = date_parse($str);
        return $parts["year"] . "-" . $parts["month"] . "-" . $parts["day"];
    }
}
