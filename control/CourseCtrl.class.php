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
}
