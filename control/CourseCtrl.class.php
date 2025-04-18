<?php

/**
 * Course Controller Class
 *
 * @author mzijlstra 2021-10-07
 */
#[Controller]
class CourseCtrl
{
    #[Inject('CourseDao')]
    public $courseDao;

    #[Inject('OfferingDao')]
    public $offeringDao;

    #[Inject('VideoDao')]
    public $videoDao;

    #[Inject('DayDao')]
    public $dayDao;

    #[Inject('UserDao')]
    public $userDao;

    #[Inject('ClassSessionDao')]
    public $classSessionDao;

    #[Inject('QuizDao')]
    public $quizDao;

    #[Inject('EnrollmentDao')]
    public $enrollmentDao;

    #[Inject('LabDao')]
    public $labDao;

    #[Inject('FileDao')]
    public $fileDao;

    #[Inject('CommentDao')]
    public $commentDao;

    #[Get(uri: '^/?$', sec: 'login')]
    public function showMyCourses()
    {
        global $VIEW_DATA;

        $user_id = $_SESSION['user']['id'];
        $offerings = $this->offeringDao->enrolled($user_id);

        if ($offerings) {
            $names = $this->instructorNames($offerings);
            $VIEW_DATA['names'] = $names;
        }

        if (hasMinAuth('admin')) {
            $faculty = $this->userDao->faculty();
            $VIEW_DATA['faculty'] = $faculty;
        }

        $VIEW_DATA['title'] = 'My Course Offerings';
        $VIEW_DATA['offerings'] = $offerings;
        $VIEW_DATA['type'] = 'my';

        return 'course/courses.php';
    }

    #[Get(uri: '^/all$', sec: 'login')]
    public function showAllCourses()
    {
        global $VIEW_DATA;

        $offerings = $this->offeringDao->all();
        $names = $this->instructorNames($offerings);

        if (hasMinAuth('admin')) {
            $faculty = $this->userDao->faculty();
            $VIEW_DATA['faculty'] = $faculty;
        }

        $VIEW_DATA['title'] = 'All Course Offerings';
        $VIEW_DATA['offerings'] = $offerings;
        $VIEW_DATA['names'] = $names;
        $VIEW_DATA['type'] = 'all';

        return 'course/courses.php';
    }

    #[Post(uri: '^/createCourse$', sec: 'admin')]
    public function createCourse()
    {
        global $MY_BASE;
        global $VIEW_DATA;

        $number = strtolower(filter_input(INPUT_POST, 'number', FILTER_UNSAFE_RAW));
        $name = filter_input(INPUT_POST, 'name', FILTER_UNSAFE_RAW);
        $fac_user_id = filter_input(INPUT_POST, 'fac_user_id', FILTER_SANITIZE_NUMBER_INT);
        $block = filter_input(INPUT_POST, 'block', FILTER_UNSAFE_RAW);
        $start = filter_input(INPUT_POST, 'date', FILTER_UNSAFE_RAW);
        $daysPerLesson = filter_input(INPUT_POST, 'daysPerLesson', FILTER_SANITIZE_NUMBER_INT);
        $lessonsPerRow = filter_input(INPUT_POST, 'lessonsPerPart', FILTER_SANITIZE_NUMBER_INT);
        $lessonRows = filter_input(INPUT_POST, 'lessonParts', FILTER_SANITIZE_NUMBER_INT);
        $start .= " 12:00:00";

        // only if offering doesn't exists
        $new_offering_id = $this->offeringDao->getOfferingByCourse($number, $block);
        if ($new_offering_id) {
            $VIEW_DATA['msg'] = "{$number} {$block} alread exists";

            return "Location: $MY_BASE";
        }

        // create if course doesn't exist
        if (! $this->courseDao->getCourse($number)) {
            $this->courseDao->create($number, $name);
        }

        $new_offering_id = $this->offeringDao->create(
            $number,
            $block,
            $start,
            $daysPerLesson,
            $lessonsPerRow,
            $lessonRows,
            0,
            0,
            1,
            0
        );
        $this->enrollmentDao->enroll($fac_user_id, $new_offering_id, 'instructor');
        $this->dayDao->create($new_offering_id, $lessonsPerRow, $lessonRows);
        $this->classSessionDao->createForOffering($new_offering_id);
        // create directory structure last, as it cannot be rolled back
        $this->videoDao->create($number, $block, $lessonsPerRow, $lessonRows);

        $VIEW_DATA['msg'] = "{$number} {$block} created";

        return "Location: $MY_BASE";
    }

    #[Post(uri: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/clone$", sec: 'instructor')]
    public function cloneOffering()
    {
        global $MY_BASE;
        global $URI_PARAMS;
        global $VIEW_DATA;

        $course_number = $URI_PARAMS[1];
        $old_block = $URI_PARAMS[2];

        $offering_id = filter_input(INPUT_POST, 'offering_id', FILTER_SANITIZE_NUMBER_INT);
        $fac_user_id = filter_input(INPUT_POST, 'fac_user_id', FILTER_SANITIZE_NUMBER_INT);
        $block = filter_input(INPUT_POST, 'block', FILTER_UNSAFE_RAW);
        $start = filter_input(INPUT_POST, 'start', FILTER_UNSAFE_RAW);
        $daysPerLesson = filter_input(INPUT_POST, 'daysPerLesson', FILTER_SANITIZE_NUMBER_INT);
        $lessonsPerRow = filter_input(INPUT_POST, 'lessonsPerPart', FILTER_SANITIZE_NUMBER_INT);
        $lessonRows = filter_input(INPUT_POST, 'lessonParts', FILTER_SANITIZE_NUMBER_INT);
        $hasQuiz = filter_input(INPUT_POST, 'hasQuiz', FILTER_SANITIZE_NUMBER_INT);
        $hasLab = filter_input(INPUT_POST, 'hasLab', FILTER_SANITIZE_NUMBER_INT);
        $showDates = filter_input(INPUT_POST, 'showDates', FILTER_SANITIZE_NUMBER_INT);
        $usesFlowcharts = filter_input(INPUT_POST, 'usesFlowcharts', FILTER_SANITIZE_NUMBER_INT);
        $cloneFiles = filter_input(INPUT_POST, 'cloneFiles', FILTER_SANITIZE_NUMBER_INT);
        $cloneComments = filter_input(INPUT_POST, 'cloneComments', FILTER_SANITIZE_NUMBER_INT);
        $start .= " 12:00:00";

        if ($hasQuiz == null) {
            $hasQuiz = 0;
        }
        if ($hasLab == null) {
            $hasLab = 0;
        }
        if ($showDates == null) {
            $showDates = 0;
        }
        if ($usesFlowcharts == null) {
            $usesFlowcharts = 0;
        }

        $new_offering_id = $this->offeringDao->getOfferingByCourse($course_number, $block);
        if ($new_offering_id) {
            $VIEW_DATA['msg'] = "{$course_number} {$block} alread exists";

            return "Location: $MY_BASE";
        }

        $new_offering_id = $this->offeringDao->create(
            $course_number,
            $block,
            $start,
            $daysPerLesson,
            $lessonsPerRow,
            $lessonRows,
            $hasQuiz,
            $hasLab,
            $showDates,
            $usesFlowcharts,
        );
        $this->enrollmentDao->enroll($fac_user_id, $new_offering_id, 'instructor');
        $this->dayDao->cloneDays($offering_id, $new_offering_id);
        $this->classSessionDao->createForOffering($new_offering_id);
        $this->quizDao->clone($offering_id, $new_offering_id);
        $this->labDao->clone($offering_id, $new_offering_id);

        if ($cloneComments) {
            $this->commentDao->clone($offering_id, $new_offering_id);
        }

        try {
            $this->videoDao->clone($course_number, $block, $old_block);
            if ($cloneFiles) {
                $this->fileDao->clone($course_number, $block, $old_block);
            }
        } catch (Exception $e) {
            $this->fileDao->delete($course_number, $block);
            // throw so that the db also rolls back and the error is logged
            throw $e;
        }

        return "Location: ../$block/";
    }

    #[Post(uri: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/edit$", sec: 'instructor')]
    public function editDay()
    {
        global $URI_PARAMS;
        $block = $URI_PARAMS[2];

        $day_id = filter_input(INPUT_POST, 'day_id', FILTER_SANITIZE_NUMBER_INT);
        $desc = filter_input(INPUT_POST, 'desc', FILTER_UNSAFE_RAW);

        $this->dayDao->update($day_id, $desc);

        return "Location: ../{$block}/";
    }

    #[Get(uri: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/settings$", sec: 'instructor')]
    public function viewSettings()
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $course = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];

        $offering = $this->offeringDao->getOfferingByCourse($course, $block);
        $VIEW_DATA['title'] = 'Settings';
        $VIEW_DATA['course'] = $course;
        $VIEW_DATA['block'] = $block;
        $VIEW_DATA['offering'] = $offering;

        return 'course/offeringSettings.php';
    }

    /**
     * Expects AJAX
     */
    #[Post(uri: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/settings$", sec: 'instructor')]
    public function updateSettings()
    {
        $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
        $block = filter_input(INPUT_POST, 'block', FILTER_UNSAFE_RAW);
        $start = filter_input(INPUT_POST, 'start', FILTER_UNSAFE_RAW);
        $daysPerLesson = filter_input(INPUT_POST, 'daysPerLesson', FILTER_SANITIZE_NUMBER_INT);
        $lessonsPerPart = filter_input(INPUT_POST, 'lessonsPerPart', FILTER_SANITIZE_NUMBER_INT);
        $lessonParts = filter_input(INPUT_POST, 'lessonParts', FILTER_SANITIZE_NUMBER_INT);
        $hasQuiz = filter_input(INPUT_POST, 'hasQuiz', FILTER_SANITIZE_NUMBER_INT);
        $hasLab = filter_input(INPUT_POST, 'hasLab', FILTER_SANITIZE_NUMBER_INT);
        $showDates = filter_input(INPUT_POST, 'showDates', FILTER_SANITIZE_NUMBER_INT);
        $usesFlowcharts = filter_input(INPUT_POST, 'usesFlowcharts', FILTER_SANITIZE_NUMBER_INT);

        $this->offeringDao->update(
            $id,
            $block,
            $start,
            $daysPerLesson,
            $lessonsPerPart,
            $lessonParts,
            $hasQuiz,
            $hasLab,
            $showDates,
            $usesFlowcharts,
        );
    }

    #[Post(uri: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/delete$", sec: 'admin')]
    public function delete()
    {
        global $URI_PARAMS;
        $course = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];

        $id = filter_input(INPUT_POST, 'offering_id', FILTER_SANITIZE_NUMBER_INT);
        $this->offeringDao->delete($id);
        $this->fileDao->delete($course, $block);

        return 'Location: ../../';
    }

    private function instructorNames($offerings)
    {
        $ids = [];
        foreach ($offerings as $offering) {
            $ids[] = $offering['id'];
        }
        $instructors = $this->enrollmentDao->getInstructorsForOfferings($ids);
        $names = [];
        foreach ($instructors as $ins) {
            if (! $names[$ins['offering_id']]) {
                $names[$ins['offering_id']] =
                    $ins['knownAs'][0].'. '.$ins['lastname'];
            } else {
                $names[$ins['offering_id']] .=
                    ', '.$ins['knownAs'][0].'. '.$ins['lastname'];
            }
        }

        return $names;
    }
}
