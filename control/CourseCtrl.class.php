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
     * @Inject('EnrollmentDao')
     */
    public $enrollmentDao;

    /**
     * @GET(uri="|^/?$|", sec="user")
     */
    public function showCourses() {
        global $VIEW_DATA;

        $latests = $this->offeringDao->allLatest();
        $newest = [];
        foreach ($latests as $latest) {
            $newest[$latest['course_number']] = $latest['block'];
        }
        $offerings = $this->offeringDao->all();
        $courses = $this->courseDao->all();
        $course_offering = [];
        foreach ($courses as $course) {
            $course_offering[$course["number"]] = [];
        }
        foreach ($offerings as $offering) {
            $course_offering[$offering["course_number"]][] = $offering;
        }

        $VIEW_DATA["courses"] = $courses;
        $VIEW_DATA["course_offerings"] = $course_offering;
        $VIEW_DATA["latest"] = $newest;
        return "courses.php";
    }

    /**
     * @POST(uri="|^/(cs\d{3})/(20\d{2}-\d{2})/clone$|", sec="admin")
     */
    public function cloneOffering() {
        global $URI_PARAMS;

        $course_number = $URI_PARAMS[1];
        $old_block = $URI_PARAMS[2];

		$offering_id = filter_input(INPUT_POST, "offering_id");
        $block = filter_input(INPUT_POST, "block");
        $start = filter_input(INPUT_POST, "date");

        // calculate stop date
        $stop = date_create($start);
        date_add($stop, date_interval_create_from_date_string("24 days"));
        $stop = date_format($stop, "Y-m-d");

        $this->videoDao->clone($course_number, $block, $old_block);
        $new_offering = $this->offeringDao->create($course_number, $block, $start, $stop);
        $this->dayDao->cloneDays($offering_id, $new_offering);
        return "Location: ../$block/";
    }

    /**
     * @POST(uri="|^/(cs\d{3})/(20\d{2}-\d{2})/edit$|", sec="admin")
     */
    public function editDay() {
        global $URI_PARAMS;
        $block = $URI_PARAMS[2];

        $day_id = filter_input(INPUT_POST, "day_id");
        $desc = filter_input(INPUT_POST, "desc");

        $this->dayDao->update($day_id, $desc);
        return "Location: ../${block}/";
    }

    /**
     * @GET(uri="|^/(cs\d{3})/(20\d{2}-\d{2})/enrollment$|", sec="admin")
     */
    public function viewEnrollment() {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $course_number = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];

        $offering = $this->offeringDao->getOfferingByCourse($course_number, $block);
        $enrollment = $this->enrollmentDao->getEnrollmentForOffering($offering['id']);

        $VIEW_DATA["course"] = $course_number;
        $VIEW_DATA["enrollment"] = $enrollment;
        $VIEW_DATA["offering"] = $offering;
        return "enrollment.php";
    }
}
