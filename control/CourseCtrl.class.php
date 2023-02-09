<?php
/**
 * Course Controller Class
 * @author mzijlstra 2021-10-07
 *
 * @Controller
 */
class CourseCtrl {
   	/**
	 * @Inject("CourseDao")
	 */
	public $courseDao;
   	/**
	 * @Inject("OfferingDao")
	 */
	public $offeringDao;
    /**
     * @Inject("VideoDao")
     */
    public $videoDao;
    /**
     * @Inject("DayDao")
     */
    public $dayDao;
    /**
     * @Inject('UserDao')
     */
    public $userDao;
    /**
     * @Inject('ClassSessionDao')
     */
    public $classSessionDao;
    /**
     * @Inject('QuizDao')
     */
    public $quizDao;

    /**
     * @GET(uri="!^/?$!", sec="applicant")
     */
    public function showCourses() {
        global $VIEW_DATA;

        if ($_SESSION['user']['type'] === 'applicant') {
            $offerings = $this->offeringDao->enrolled($_SESSION['user']['id']);
        } else {
            $offerings = $this->offeringDao->all();
        }
        
        $faculty = $this->userDao->faculty();

        $VIEW_DATA["title"] = "Course Offerings";
        $VIEW_DATA["offerings"] = $offerings;
        $VIEW_DATA["faculty"] = $faculty;
        return "courses.php";
    }

    /**
     * @POST(uri="!^/createCourse$!", sec="admin")
     */
    public function createCourse() {
        global $MY_BASE;

        $number = strtolower(filter_input(INPUT_POST, "number", FILTER_UNSAFE_RAW));
        $name = filter_input(INPUT_POST, "name", FILTER_UNSAFE_RAW);
		$fac_user_id = filter_input(INPUT_POST, "fac_user_id", FILTER_SANITIZE_NUMBER_INT);
        $block = filter_input(INPUT_POST, "block", FILTER_UNSAFE_RAW);
        $start = filter_input(INPUT_POST, "date", FILTER_UNSAFE_RAW);
        $daysPerLesson = filter_input(INPUT_POST, "daysPerLesson", FILTER_SANITIZE_NUMBER_INT);
        $lessonsPerRow = filter_input(INPUT_POST, "lessonsPerPart", FILTER_SANITIZE_NUMBER_INT);
        $lessonRows = filter_input(INPUT_POST, "lessonParts", FILTER_SANITIZE_NUMBER_INT);

        $this->courseDao->create($number, $name);
        $new_offering = $this->offeringDao->create($number, $block, $start, $fac_user_id, 
                                $daysPerLesson, $lessonsPerRow, $lessonRows);
        $this->dayDao->create($new_offering, $lessonsPerRow, $lessonRows);
        $this->classSessionDao->createForOffering($new_offering);
        // create directory structure last, as it cannot be rolled back
        // that is, if I actually implement transactions...
        $this->videoDao->create($number, $block, $lessonsPerRow, $lessonRows);

        return "Location: $MY_BASE";
    }

    /**
     * @POST(uri="!^/(cs\d{3})/(20\d{2}-\d{2})/clone$!", sec="admin")
     */
    public function cloneOffering() {
        global $URI_PARAMS;

        $course_number = $URI_PARAMS[1];
        $old_block = $URI_PARAMS[2];

		$offering_id = filter_input(INPUT_POST, "offering_id", FILTER_SANITIZE_NUMBER_INT);
		$fac_user_id = filter_input(INPUT_POST, "fac_user_id", FILTER_SANITIZE_NUMBER_INT);
        $block = filter_input(INPUT_POST, "block", FILTER_UNSAFE_RAW);
        $start = filter_input(INPUT_POST, "start", FILTER_UNSAFE_RAW);
        $daysPerLesson = filter_input(INPUT_POST, "daysPerLesson", FILTER_SANITIZE_NUMBER_INT);
        $lessonsPerRow = filter_input(INPUT_POST, "lessonsPerPart", FILTER_SANITIZE_NUMBER_INT);
        $lessonRows = filter_input(INPUT_POST, "lessonParts", FILTER_SANITIZE_NUMBER_INT);
        $hasQuiz = filter_input(INPUT_POST, "hasQuiz", FILTER_SANITIZE_NUMBER_INT);
        $hasLab = filter_input(INPUT_POST, "hasLab", FILTER_SANITIZE_NUMBER_INT);

        if ($hasQuiz == null) {
            $hasQuiz = 0;
        }
        if ($hasLab == null) {
            $hasLab = 0;
        }

        $this->videoDao->clone($course_number, $block, $old_block);
        $new_offering_id = $this->offeringDao->create($course_number, $block, 
                                    $start, $fac_user_id, 
                                    $daysPerLesson, $lessonsPerRow, $lessonRows,
                                    $hasQuiz, $hasLab);
        $this->dayDao->cloneDays($offering_id, $new_offering_id);
        $this->classSessionDao->createForOffering($new_offering_id);
        $this->quizDao->clone($offering_id, $new_offering_id);

        return "Location: ../$block/";
    }

    /**
     * @POST(uri="!^/(cs\d{3})/(20\d{2}-\d{2})/edit$!", sec="admin")
     */
    public function editDay() {
        global $URI_PARAMS;
        $block = $URI_PARAMS[2];

        $day_id = filter_input(INPUT_POST, "day_id", FILTER_SANITIZE_NUMBER_INT);
        $desc = filter_input(INPUT_POST, "desc", FILTER_UNSAFE_RAW);

        $this->dayDao->update($day_id, $desc);
        return "Location: ../{$block}/";
    }

    /**
     * @GET(uri="!^/(cs\d{3})/(20\d{2}-\d{2})/settings$!", sec="admin")
     */
    public function viewSettings() {
        global $URI_PARAMS;
		global $VIEW_DATA;

		$course = $URI_PARAMS[1];
		$block = $URI_PARAMS[2];

		$offering = $this->offeringDao->getOfferingByCourse($course, $block);

        $VIEW_DATA['title'] = "Settings";
        $VIEW_DATA['course']  = $course;
        $VIEW_DATA['block'] = $block;
        $VIEW_DATA['offering'] = $offering;

        return "offeringSettings.php";
    }

    /**
     * Expects AJAX
     * 
     * @POST(uri="!^/(cs\d{3})/(20\d{2}-\d{2})/settings$!", sec="admin")
     */
    public function updateSettings() {
        $id = filter_input(INPUT_POST, "id", FILTER_SANITIZE_NUMBER_INT);
        $block = filter_input(INPUT_POST, "block", FILTER_UNSAFE_RAW);
        $start = filter_input(INPUT_POST, "start", FILTER_UNSAFE_RAW);
        $daysPerLesson = filter_input(INPUT_POST, "daysPerLesson", FILTER_SANITIZE_NUMBER_INT);
        $lessonsPerPart = filter_input(INPUT_POST, "lessonsPerPart", FILTER_SANITIZE_NUMBER_INT);
        $lessonParts = filter_input(INPUT_POST, "lessonParts", FILTER_SANITIZE_NUMBER_INT);
        $hasQuiz = filter_input(INPUT_POST, "hasQuiz", FILTER_SANITIZE_NUMBER_INT);
        $hasLab = filter_input(INPUT_POST, "hasLab", FILTER_SANITIZE_NUMBER_INT);

        $this->offeringDao->update($id, $block, $start, $daysPerLesson, 
                            $lessonsPerPart, $lessonParts, $hasQuiz, $hasLab);
    }

}
