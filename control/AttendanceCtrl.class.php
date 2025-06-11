<?php

/**
 * Attendance Controller Class
 *
 * @author mzijlstra 2021-11-29
 */
#[Controller]
class AttendanceCtrl
{
    #[Inject('OverviewHlpr')]
    public $overviewCtr;

    #[Inject('ClassSessionDao')]
    public $classSessionDao;

    #[Inject('MeetingDao')]
    public $meetingDao;

    #[Inject('OfferingDao')]
    public $offeringDao;

    #[Inject('EnrollmentDao')]
    public $enrollmentDao;

    #[Inject('AttendanceDao')]
    public $attendanceDao;

    #[Inject('AttendanceExportDao')]
    public $attendanceExportDao;

    #[Inject('ExcusedDao')]
    public $excusedDao;

    #[Inject('MailHlpr')]
    public $mailHlpr;

    #[Inject('CamsDao')]
    public $camsDao;

    #[Inject('AttendanceConfigDao')]
    public $AttendanceConfigDao;

    #[Inject('UserDao')]
    public $userDao;

    /**
     * This function is really two in one. If the user is a student, or a user_id
     * is provided as a query parameter, it will show the student's attendance overview.
     *
     * If the user is an instructor or assistant, it will show the attendance overview.
     */
    #[Get(uri: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/attendance$", sec: 'student')]
    public function overview()
    {
        global $VIEW_DATA;

        // We're building on top of  overview -- run it first
        // this populates $VIEW_DATA with the overview related data
        $this->overviewCtr->overview();

        $offering_id = $VIEW_DATA['offering_id'];
        $days = $VIEW_DATA['days'];
        $course = $VIEW_DATA['course'];
        $block = $VIEW_DATA['block'];

        // get sessions for these days
        $sessions = $this->classSessionDao->allForOffering($offering_id);
        foreach ($sessions as $session) {
            $session['meetings'] = [];
            $days[$session['abbr']][$session['type']] = $session;
            $days[$session['abbr']][$session['type']]['meetings'] = [];
        }

        $isFaculty = false;
        $user_id = filter_input(INPUT_GET, 'user_id', FILTER_SANITIZE_NUMBER_INT);
        if ($user_id && ! hasMinAuth('instructor')) {
            // if user_id is given, but not an instructor, return 403
            return 'error/403.php';
        } else {
            $isFaculty = true;
        }
        if (! $user_id) {
            // if no user_id is given, use the current user
            $user_id = $_SESSION['user']['id'];
        }

        // get enrollment for this user
        $course = strtoupper($course);
        $auth = $this->enrollmentDao->checkEnrollmentAuth($user_id, $course, $block);

        $VIEW_DATA['title'] = 'Attendance';
        $VIEW_DATA['area'] = 'attendance';
        if ($auth['auth'] == 'student') {
            // get user data
            $user = $this->userDao->retrieve($user_id);
            $teamsName = $user['teamsName'];
            $attendance = $this->attendanceDao->getStudentOffering($teamsName, $offering_id);
            foreach ($attendance as $meeting) {
                $days[$meeting['abbr']][$meeting['stype']]['meetings'][] = $meeting;
            }

            if ($isFaculty) {
                $VIEW_DATA['title'] = "Attendance for {$user['knownAs']} {$user['lastname']}";
            } else {
                $VIEW_DATA['title'] = 'My Attendance';
            }
            $VIEW_DATA['days'] = $days;

            // show the student view:
            // red is absent
            // green is present
            // orange is late, middle missing, or left early
            // blue is excused
            return 'attendance/studentOverview.php';
        }

        // Add attendance data
        $meetings = $this->meetingDao->allForOffering($offering_id);
        foreach ($meetings as $meeting) {
            $days[$meeting['abbr']][$meeting['stype']]['meetings'][] = $meeting;
        }

        $enrollment = $this->enrollmentDao->getEnrollmentForOffering($offering_id);
        $excused_raw = $this->excusedDao->allForOffering($offering_id);
        $excused = [];
        foreach ($excused_raw as $student) {
            if (! isset($excused[$student['class_session_id']])) {
                $excused[$student['class_session_id']] = [];
            }
            $excused[$student['class_session_id']][] = $student;
        }
        $defaults = $this->AttendanceConfigDao->byId($offering_id);

        $VIEW_DATA['enrollment'] = $enrollment;
        $VIEW_DATA['excused'] = $excused;
        $VIEW_DATA['defaults'] = $defaults;
        $VIEW_DATA['days'] = $days;

        return 'attendance/attendance.php';
    }

    #[Get(uri: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/attendance/config$", sec: 'assistant')]
    public function getConfig()
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $course_number = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $offering = $this->offeringDao->getOfferingByCourse($course_number, $block);
        $cams = $this->camsDao->get($offering['id']);
        $defaults = $this->AttendanceConfigDao->byId($offering['id']);

        if (! $defaults) {
            $defaults = [
                'AM_start' => '10:00',
                'AM_stop' => '12:30',
                'PM_start' => '13:30',
                'PM_stop' => '15:20',
                'inClass' => '1',
                'tz_offset' => '00:00',
            ];
        }

        $VIEW_DATA['course'] = $course_number;
        $VIEW_DATA['block'] = $block;
        $VIEW_DATA['offering'] = $offering;
        $VIEW_DATA['CAMS'] = $cams;
        $VIEW_DATA['defaults'] = $defaults;
        $VIEW_DATA['title'] = 'Attendance Configuration';

        return 'attendance/config.php';
    }

    /**
     * Expects AJAX
     */
    #[Post(uri: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/attendance/defaults$", sec: 'assistant')]
    public function updateDefaults()
    {
        global $URI_PARAMS;

        $course_number = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $offering = $this->offeringDao->getOfferingByCourse($course_number, $block);
        $AM_start = filter_input(INPUT_POST, 'AM_start');
        $AM_stop = filter_input(INPUT_POST, 'AM_stop');
        $PM_start = filter_input(INPUT_POST, 'PM_start');
        $PM_stop = filter_input(INPUT_POST, 'PM_stop');
        $inClass = filter_input(INPUT_POST, 'inClass', FILTER_SANITIZE_NUMBER_INT);
        $inClass = $inClass ? '1' : '0';
        $tz_offset = filter_input(INPUT_POST, 'tz_offset');

        $this->AttendanceConfigDao->saveOrUpdate(
            $offering['id'],
            $AM_start,
            $AM_stop,
            $PM_start,
            $PM_stop,
            $inClass,
            $tz_offset,
        );
    }

    #[Post(uri: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/attendance/config$", sec: 'assistant')]
    public function updateConfig()
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $course_number = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $course_id = filter_input(INPUT_POST, 'course_id', FILTER_SANITIZE_NUMBER_INT);
        $username = filter_input(INPUT_POST, 'username');
        $password = filter_input(INPUT_POST, 'password');

        $AM_id = filter_input(INPUT_POST, 'AM_id', FILTER_SANITIZE_NUMBER_INT);
        $PM_id = filter_input(INPUT_POST, 'PM_id', FILTER_SANITIZE_NUMBER_INT);
        $SAT_id = filter_input(INPUT_POST, 'SAT_id', FILTER_SANITIZE_NUMBER_INT);

        if (! $course_id || ! $username || ! $password) {
            return 'error/400.php';
        }

        if (! $AM_id || ! $PM_id || ! $SAT_id) {
            require_once 'control/CamsHlpr.class.php';
            $cams = [
                'course_id' => $course_id,
                'username' => $username,
            ];
            $hlpr = new CamsHlpr($cams);
            if ($hlpr->login($password) == false) {
                $VIEW_DATA['error'] = 'CAMS Login Failed - incorrect username or password';

                return 'Location: config';
            }

            $types = $hlpr->getSessionTypes();
            foreach ($types as $type => $id) {
                if (str_starts_with($type, 'MTWRF 10:00:00 AM')) {
                    $AM_id = $id;
                } elseif (str_starts_with($type, 'MTWRF 1:00:00 PM')) {
                    $PM_id = $id;
                } elseif (str_starts_with($type, 'S 10:00:00 AM')) {
                    $SAT_id = $id;
                }
            }
        }

        $offering = $this->offeringDao->getOfferingByCourse($course_number, $block);
        $this->camsDao->saveOrUpdate($offering['id'], $username, $course_id, $AM_id, $PM_id, $SAT_id);

        return 'Location: config';
    }

    #[Post(uri: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/excuse$", sec: 'assistant')]
    public function excuseAbsence()
    {
        $session_id = filter_input(INPUT_POST, 'session_id', FILTER_SANITIZE_NUMBER_INT);
        $teamsName = filter_input(INPUT_POST, 'teamsName');
        $reason = filter_input(INPUT_POST, 'reason');
        $this->excusedDao->add($session_id, $teamsName, $reason);

        return 'Location: attendance';
    }

    /**
     * Expects AJAX
     */
    #[Post(uri: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/delExcuse$", sec: 'assistant')]
    public function deleteExcuse()
    {
        $session_id = filter_input(INPUT_POST, 'session_id', FILTER_SANITIZE_NUMBER_INT);
        $teamsName = filter_input(INPUT_POST, 'teamsName');
        $this->excusedDao->delete($session_id, $teamsName);
    }

    #[Get(uri: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/physical/(W\d+)$", sec: 'assistant')]
    public function physicalAttendanceReport()
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $course_number = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $week = $URI_PARAMS[3];

        $offering = $this->offeringDao->getOfferingByCourse($course_number, $block);
        $attend = $this->attendanceExportDao->getPhysicalAttendance($offering['id'], $week);

        $VIEW_DATA['week'] = $week;
        $VIEW_DATA['course'] = $course_number;
        $VIEW_DATA['offering'] = $offering;
        $VIEW_DATA['block'] = $block;
        $VIEW_DATA['attend'] = $attend;
        $VIEW_DATA['title'] = $week.' Physical Attendance';

        return 'attendance/physical.php';
    }

    #[Get(uri: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/professionalism$", sec: 'assistant')]
    public function professionalismReport()
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $course_number = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];

        $offering = $this->offeringDao->getOfferingByCourse($course_number, $block);
        $prof_data = $this->attendanceDao->professionalism($offering['id']);

        $professionals = [];
        foreach ($prof_data as $student) {
            $professional = [];
            $professional['id'] = $student['studentID'];
            $professional['name'] = $student['knownAs'].' '.$student['lastname'];
            $professional['inClass'] = $student['inClass'];
            $professional['absent'] = $student['absent'];
            $professional['middleMissing'] = $student['middleMissing'];
            $professional['late'] = $student['late'];
            $professional['minsLate'] = $student['minsLate'];
            $professional['leaveEarly'] = $student['leaveEarly'];
            $professional['minsLeave'] = $student['minsLeave'];

            $late_secs = strtotime('1970-01-01 '.$student['minsLate'].'GMT');
            $leave_secs = strtotime('1970-01-01 '.$student['minsLeave'].'GMT');
            $absent_secs = $student['absent'] * 1800; // half hour per absent
            $midmis_secs = $student['middleMissing'] * 300; // 5 mins per mid miss
            $total_secs = $late_secs + $leave_secs + $absent_secs + $midmis_secs;
            $professional['totalSecs'] = $total_secs;
            $professional['total'] = gmdate('H:i:s', $total_secs);
            $professionals[] = $professional;
        }
        usort($professionals, 'AttendanceCtrl::byTotal');

        $VIEW_DATA['course'] = $course_number;
        $VIEW_DATA['block'] = $block;
        $VIEW_DATA['professionals'] = $professionals;
        $VIEW_DATA['title'] = 'Professionalism';

        return 'attendance/professionalism.php';
    }

    private static function byTotal($a, $b): int
    {
        return $a['totalSecs'] - $b['totalSecs'];
    }

    /**
     * Expects AJAX
     */
    #[Post(uri: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/physical/(W\d+)/email$", sec: 'assistant')]
    public function emailLowPhysical()
    {
        global $URI_PARAMS;

        $course_number = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $week = $URI_PARAMS[3];
        $minPhys = filter_input(INPUT_POST, 'minPhys', FILTER_SANITIZE_NUMBER_INT);

        $offering = $this->offeringDao->getOfferingByCourse($course_number, $block);
        $below = $this->attendanceExportDao->internationalPhysicalBelow($offering['id'], $week, $minPhys);
        $ins = $this->enrollmentDao->getTopInstructorFor($course_number, $block);
        $replyTo = [$ins['email'], $ins['teamsName']];

        foreach ($below as $student) {
            $to = [$student['email'], $student['teamsName']];
            $knownAs = trim($student['knownAs']);
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
            $this->mailHlpr->mail($to, 'Unexcused Absence', $message, $replyTo);
        }
    }

    #[Get(uri: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/attendance/(W\d+D\d+)/(AM|PM|SAT)$", sec: 'assistant')]
    public function exportReport()
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $course_number = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $day_abbr = $URI_PARAMS[3];
        $stype = $URI_PARAMS[4]; // AM or  PM

        $offering = $this->offeringDao->getOfferingByCourse($course_number, $block);
        $date = $this->offeringDao->getDate($offering, $day_abbr);

        $session = $this->classSessionDao->getSession(
            $course_number,
            $block,
            $day_abbr,
            $stype
        );
        $status = $this->classSessionDao->calcStatus($session['id']);

        // always regenerate the export report. Initially I didn't do so
        // as a form of 'optimization'. But not doing it just makes the
        // user experience worse, and it happens so rarely that it is not
        // worth the effort.
        $this->generateExportReport($session['id']);
        $session = $this->classSessionDao->getSessionById($session['id']);
        $exports = $this->attendanceExportDao->forSession($session['id']);

        $VIEW_DATA['day_abbr'] = $day_abbr;
        $VIEW_DATA['date'] = $date;
        $VIEW_DATA['course'] = $course_number;
        $VIEW_DATA['offering'] = $offering;
        $VIEW_DATA['block'] = $block;
        $VIEW_DATA['stype'] = $stype;
        $VIEW_DATA['session'] = $session;
        $VIEW_DATA['exports'] = $exports;
        $VIEW_DATA['title'] = $day_abbr.' '.$stype.' Attendance Export';

        return 'attendance/attendanceExport.php';
    }

    #[Post(uri: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/attendance/(W\d+D\d+)/export$", sec: 'instructor')]
    public function export()
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $course_number = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $day_abbr = $URI_PARAMS[3];

        $pwd = filter_input(INPUT_POST, 'password');
        $stype = filter_input(INPUT_POST, 'stype');
        $date = filter_input(INPUT_POST, 'date');
        $start = filter_input(INPUT_POST, 'start');
        $stop = filter_input(INPUT_POST, 'stop');

        // prepare the data
        $real_stype = $stype;
        if ($stype == 'SAT') {
            $real_stype = 'AM';
        }

        $parts = date_parse($date);
        $date = $parts['month'].'/'.$parts['day'].'/'.$parts['year'];

        $offering = $this->offeringDao->getOfferingByCourse($course_number, $block);
        $session = $this->classSessionDao->getSession(
            $course_number,
            $block,
            $day_abbr,
            $real_stype
        );
        $cams = $this->camsDao->get($offering['id']);
        $exports = $this->attendanceExportDao->forSession($session['id']);

        $students = [];
        foreach ($exports as $student) {
            $students[$student['studentID']] = $student;
        }

        // do the actual export
        try {
            require_once 'control/CamsHlpr.class.php';
            $hlpr = new CamsHlpr($cams);
            if ($hlpr->login($pwd) == false) {
                $VIEW_DATA['error'] = 'CAMS Login Failed -- incorrect username or password';

                return "Location: {$stype}";
            }
            $hlpr->submitAttendance($students, $stype, $date, $start, $stop);
            $hlpr->logout();
        } catch (Exception $e) {
            echo 'Is https://fac.miu.edu/ down?';

            return 'error/500.php';
        }

        // update class_session status to EXPORTED
        $stats = $this->classSessionDao->calcStatus($session['id']);
        $stats['status'] = 'EXPORTED';
        $this->classSessionDao->setStatus($stats);

        return 'Location: ../../attendance';
    }

    /**
     * Expects AJAX
     */
    #[Post(uri: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/attendance/W[1-4]D[1-6]/(AM|PM|SAT)/(\d+)$", sec: 'assistant')]
    public function updateExportRow()
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $this->attendanceExportDao->update($data);
    }

    private function generateExportReport($session_id)
    {
        // update session with status, start, stop, meetings
        $stats = $this->classSessionDao->calcStatus($session_id);
        $stats['status'] = 'GENERATED';
        if ($stats['meetings'] == 0) {
            $stats['status'] = 'NO_DATA';
        }
        $this->classSessionDao->setStatus($stats);
        if ($stats['status'] == 'NO_DATA') {
            return; // nothing to do
        }

        // get enrollemnt for offering
        $offering_id = $this->classSessionDao->getOfferingId($session_id);
        $enrollment = $this->enrollmentDao->getEnrollmentForOffering($offering_id);

        // make an enrolled lookup table
        $enrolled = [];
        foreach ($enrollment as $student) {
            $enrolled[$student['studentID']] = true;
        }

        // get excused for session
        $excused_list = $this->excusedDao->forClassSession($session_id);
        $excused = [];
        foreach ($excused_list as $excuse) {
            $excused[$excuse['teamsName']] = $excuse;
        }

        // for each enrolled need: studentId, status, inClass, comment
        // where status one of: present, excused, absent, late, left early, other
        // where status other only used if middle_missing (and nothing else)
        // where comment contains: minutes late, minutes left early, middle missing
        $attendance = $this->attendanceDao->getExportData($session_id);
        $exports = [];
        foreach ($attendance as $attendant) {
            if ($enrolled[$attendant['studentID']]) {
                $export = [];
                $export['studentID'] = $attendant['studentID'];
                $export['status'] = $this->getStatus($attendant);
                $export['inClass'] = $attendant['inClass'];
                $export['comment'] = $this->getComment($attendant);
                if ($excused[$attendant['teamsName']]) {
                    $reason = $excused[$attendant['teamsName']]['reason'];
                    $export['comment'] .= ' '.$reason;
                }
                $exports[$attendant['studentID']] = $export;
            }
        }
        $this->attendanceExportDao->clear($session_id);
        $this->attendanceExportDao->create($session_id, $exports);
    }

    private function getStatus($attendant)
    {
        if ($attendant['excused']) {
            return 'Excused';
        } elseif ($attendant['absent']) {
            return 'Absent';
        } elseif ($attendant['late']) {
            return 'Late';
        } elseif ($attendant['leaveEarly']) {
            return 'Left Early';
        } elseif ($attendant['middleMissing']) {
            return 'Other';
        } else {
            return 'Present';
        }
    }

    private function getComment($attendant)
    {
        $comments = [];
        if ($attendant['excused']) {
            $comments[] = 'excused';
        }
        if ($attendant['absent']) {
            $comments[] = 'absent';
        }
        if ($attendant['late']) {
            $mins = $attendant['minsLate'];
            $mins = substr($mins, $this->timePos($mins));
            $comments[] = "late: $mins mins";
        }
        if ($attendant['leaveEarly']) {
            $mins = $attendant['minsLeave'];
            $mins = substr($mins, $this->timePos($mins));
            $comments[] = "left early: $mins mins";
        }
        if ($attendant['middleMissing']) {
            $comments[] = 'missed middle';
        }

        return implode(', ', $comments);
    }

    private function timePos($time)
    {
        $i = 0;
        while ($time[$i] == '0' || $time[$i] == ':') {
            $i++;
        }

        return $i;
    }
}
