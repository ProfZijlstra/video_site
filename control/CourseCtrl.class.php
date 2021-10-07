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

        $VIEW_DATA["courses"] = $this->courseDao->all();
        $VIEW_DATA["offerings"] = $this->offeringDao->all();
        $VIEW_DATA["latest"] = $newest;
        return "courses.php";
    }
}
