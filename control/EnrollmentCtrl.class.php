<?php

/**
 * Enrollment Controller Class
 * @author mzijlstra 2023-01-04
 */

#[Controller(path: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)")]
class EnrollmentCtrl
{
    #[Inject('OfferingDao')]
    public $offeringDao;
    #[Inject('EnrollmentDao')]
    public $enrollmentDao;
    #[Inject('UserDao')]
    public $userDao;
    #[Inject('MailHlpr')]
    public $mailHlpr;


    #[Get(uri: "/enrolled$", sec: "instructor")]
    public function viewEnrollment()
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $course_number = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];

        $offering = $this->offeringDao->getOfferingByCourse($course_number, $block);
        $enrollment = $this->enrollmentDao->getEnrollmentForOffering($offering['id']);

        $instructors = [];
        $assistants = [];
        $students = [];
        $observers = [];

        foreach ($enrollment as $person) {
            if ($person['auth'] == "instructor") {
                $instructors[] = $person;
            } else if ($person['auth'] == "assistant") {
                $assistants[] = $person;
            } else if ($person['auth'] == "student") {
                $students[] = $person;
            } else {
                $observers[] = $person;
            }
        }

        $VIEW_DATA['instructors'] = $instructors;
        $VIEW_DATA['assistants'] = $assistants;
        $VIEW_DATA['students'] = $students;
        $VIEW_DATA['observers'] = $observers;
        $VIEW_DATA['offering'] = $offering;
        $VIEW_DATA["course"] = $course_number;
        $VIEW_DATA["block"] = $block;
        $VIEW_DATA["offering_id"] = $offering["id"];
        $VIEW_DATA["title"] = "Enrollment";
        return "enrollment.php";
    }


    #[Post(uri: "/enrolled$", sec: "instructor")]
    public function replaceEnrollment()
    {
        $offering_id = filter_input(INPUT_POST, "offering_id", FILTER_SANITIZE_NUMBER_INT);
        if ($offering_id && $_FILES["list"]) {
            // delete current enrollment
            $this->enrollmentDao->deleteStudentEnrollment($offering_id);

            // parse file for new students
            $this->enrollStudentsInFile($_FILES["list"]["tmp_name"], $offering_id);
        }

        return "Location: enrolled";
    }


    #[Post(uri: "/observe$", sec: "login")]
    public function requestObserve()
    {
        global $URI_PARAMS;
        $course = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $user_id = $_SESSION['user']['id'];
        $first = $_SESSION['user']['first'];
        $last = $_SESSION['user']['last'];
        $offering = $this->offeringDao->getOfferingByCourse($course, $block);
        $oid = $offering['id'];
        //$this->enrollmentDao->enroll($user_id, $offering['id'], "observer");

        $msg = <<<EOD
$first $last would like to join $course $block as observer.

Approve or deny this request at:
https://manalabs.org/videos/observe?uid=$user_id&oid=$oid

EOD;
        $ins = $this->enrollmentDao->getTopInstructorFor($course, $block);
        $to = [$ins['email'], $ins['teamsName']];
        $this->mailHlpr->mail($to, "Observer Request", $msg);

        $msg = <<<EOD
Your request to join $course $block as observer has been emailed to the
system administrator. You will receive another email when your request has been
granted or denied.

Note: this is an automated email
EOD;

        $email = $_SESSION['user']['email'];
        $name = "{$first} {$last}";
        $to = [$email, $name];
        $this->mailHlpr->mail($to, "Observer Request", $msg);

        return "Location: ../$block/";
    }

    #[Get(uri: "/observe$", sec: "instructor")]
    public function showRequest()
    {
        global $VIEW_DATA;

        $offering_id = filter_input(INPUT_GET, "oid", FILTER_SANITIZE_NUMBER_INT);
        $user_id = filter_input(INPUT_GET, "uid", FILTER_SANITIZE_NUMBER_INT);

        $user = $this->userDao->retrieve($user_id);
        $offering = $this->offeringDao->getOfferingById($offering_id);

        $VIEW_DATA['first'] = $user['firstname'];
        $VIEW_DATA['last'] = $user['lastname'];
        $VIEW_DATA['course'] = $offering['course_number'];
        $VIEW_DATA['block'] = $offering['block'];
        $VIEW_DATA['offering_id'] = $offering_id;
        $VIEW_DATA['user_id'] = $user_id;
        $VIEW_DATA['title'] = "Review Request";
        return "reviewRequest.php";
    }


    #[Post(uri: "/observe$", sec: "instructor")]
    public function observerAllowDeny()
    {
        $offering_id = filter_input(INPUT_POST, "oid", FILTER_SANITIZE_NUMBER_INT);
        $user_id = filter_input(INPUT_POST, "uid", FILTER_SANITIZE_NUMBER_INT);
        $allow = filter_input(INPUT_POST, "allow", FILTER_SANITIZE_NUMBER_INT);

        $user = $this->userDao->retrieve($user_id);
        $offering = $this->offeringDao->getOfferingById($offering_id);

        $email = $user['email'];
        $course = $offering['course_number'];
        $block = $offering['block'];
        $teamsName = $user['teamsName'];
        $to = [$email, $teamsName];

        if ($allow) {
            $this->enrollmentDao->enroll($user_id, $offering_id, "observer");
            $subject = "Request Accepted";
            $msg = "Your request to join $course $block has been accepted.";
        } else {
            $subject = "Request Denied";
            $msg = "Your request to join $course $block has been denied.";
        }

        $this->mailHlpr->mail($to, $subject, $msg);

        return "Location: ../videos/";
    }



    #[Post(uri: "/enroll$", sec: "instructor")]
    public function enroll()
    {
        global $VIEW_DATA;
        // receive offering_id, email and auth
        $offering_id = filter_input(INPUT_POST, "offering_id", FILTER_SANITIZE_NUMBER_INT);
        $auth = filter_input(INPUT_POST, "auth");
        $email = filter_input(INPUT_POST, "email");

        // if email missing return "missing email"
        if (!$email) {
            $VIEW_DATA['msg'] = "Error: Missing Email Address";
            return "Location: enrolled";
        }

        // check if user exists by email (receive user_id)
        $user_id = $this->userDao->getUserId($email);
        if ($user_id) {
            // check if this user is already enrolled
            if (!$this->enrollmentDao->isEnrolled($user_id, $offering_id)) {
                $this->enrollmentDao->enroll($user_id, $offering_id, $auth);
                $VIEW_DATA['msg'] = "Existing user {$email} enrolled";
            } else {
                $VIEW_DATA['msg'] = "User {$email} was already enrolled";
            }
            return "Location: enrolled";
        }

        // receive first, last, knownAs, studentId, teamsName, pass 
        $first = filter_input(INPUT_POST, "first");
        $last = filter_input(INPUT_POST, "last");
        $knownAs = filter_input(INPUT_POST, "knownAs");
        $pass = filter_input(INPUT_POST, "pass");
        $studentID = filter_input(INPUT_POST, "studentID");
        $teamsName = filter_input(INPUT_POST, "teamsName");
        if (!$first || !$last || !$pass) {
            $VIEW_DATA['msg'] = "Error: Missing given names, family names, or password";
            return "Location: enrolled";
        }
        if ($studentID == "" || !is_numeric($studentID)) {
            $studentID = 0;
        }
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $user_id = $this->userDao->insert(
            $first,
            $last,
            $knownAs,
            $email,
            $studentID,
            $teamsName,
            $hash,
            1,
            0,
            0
        );
        $this->enrollmentDao->enroll($user_id, $offering_id, $auth);

        $VIEW_DATA['msg'] = "Enrolled new user {$email}";
        return "Location: enrolled";
    }


    #[Post(uri: "/config_enroll$", sec: "instructor")]
    public function update()
    {
        $offering_id = filter_input(INPUT_POST, "offering_id", FILTER_SANITIZE_NUMBER_INT);
        $user_id = filter_input(INPUT_POST, "user_id", FILTER_SANITIZE_NUMBER_INT);
        $auth = filter_input(INPUT_POST, "auth");
        $group = filter_input(INPUT_POST, "group");

        $this->enrollmentDao->update($user_id, $offering_id, $auth, $group);
        return "Location: enrolled";
    }


    #[Post(uri: "/unenroll$", sec: "instructor")]
    public function unenroll()
    {
        $offering_id = filter_input(INPUT_POST, "offering_id", FILTER_SANITIZE_NUMBER_INT);
        $enrollment_id = filter_input(INPUT_POST, "eid", FILTER_SANITIZE_NUMBER_INT);
        $this->enrollmentDao->unenroll($enrollment_id, $offering_id);
        return "Location: enrolled";
    }

    private function enrollStudentsInFile($file, $offering_id)
    {
        $lines = file($file);

        # The CSV file should be formatted like a copy pasted infosys classlist
        foreach ($lines as $line) {

            # lines that do not start with an index and a studentId are ignored
            if (preg_match("/^\d+\s*,\s*0{3}-\d{2}-\d{4}/", $line)) {
                list($idx, $sid, $first, $middle, $last, $email) = str_getcsv($line);

                # create user if not already in DB
                $user_id = $this->userDao->getUserId($email);
                if (!$user_id) {
                    $user_id = $this->createAccount($sid, $first, $middle, $last, $email);
                }

                # enroll in the offering
                $this->enrollmentDao->enroll($user_id, $offering_id, "student");
            }
        }
    }

    private function createAccount($sid, $first, $middle, $last, $email)
    {
        $given = trim($first) . " " . trim($middle);
        $teamsName = trim($given) . " " . trim($last);
        # transform social security formatted student ID into 6 digit 
        $matches = array();
        preg_match("/0{3}-([169]\d)-(\d{4})/", $sid, $matches);
        $id6 = $matches[1] . $matches[2];
        // make initial password be the 6 digit student ID
        $hash = password_hash($id6, PASSWORD_DEFAULT);

        $user_id = $this->userDao->insert(
            $given,
            $last,
            $first,
            $email,
            $id6,
            $teamsName,
            $hash,
            1,
            0,
            0
        );

        # create custom welcome message
        $message =
            "Dear $first $middle $last,

Professor Michael Zijlstra's course has its lecture videos at: https://manalabs.org/videos/

To access these videos the following account has been created for you:

user: $email
pass: $id6

Please do not reply to this email, instead please ask your questions in class!

Enjoy your course,

Manalabs.org Automated Account Creator
";

        #email the user about his newly created account
        $to = [$email, $teamsName];
        $this->mailHlpr->mail($to, "Prof Zijlstra's manalabs.org account", $message);
        return $user_id;
    }
}
