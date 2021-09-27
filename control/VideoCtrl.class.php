<?php
require "lib/Parsedown.php";

/**
 * Video Controller Class
 * @author mzijlstra 05/18/2021
 *
 * @Controller
 */
class VideoCtrl {

	/**
	 * @Inject("CourseDao")
	 */
	public $courseDao;
	/**
	 * @Inject("OfferingDao")
	 */
	public $offeringDao;
	/**
	 * @Inject("DayDao")
	 */
	public $dayDao;
	/**
	 * @Inject("EnrollmentDao")
	 */
	public $enrollmentDao;
	/**
	 * @Inject("QuestionDao")
	 */
	public $questionDao;
	/**
	 * @Inject("ReplyDao")
	 */
	public $replyDao;

    /**
     * Redirects a successful login to overview
     * @GET(uri="|^/(cs\d{3})?/?$|", sec="user")
     */
    public function loggedIn() {

		$user_id = $_SESSION['user']['id'];
		$enrolled = $this->enrollmentDao->getEnrollmentForStudent($user_id);
		if ($enrolled) {
			$offering = $this->offeringDao->getOfferingById($enrolled["offering_id"]);
			$course = $offering['course_number'];
			$block = $offering['block'];
			return "Location: /videos/${course}/${block}/";
		} else {
			// I guess we'll default to 
			return "Location: /videos/cs472/2021-08/";
		}

    }

	/**
	 * If the URL doesn't contain a video selection, just a day
	 * 
	 * @GET(uri="|^/(cs\d{3})/(20\d{2}-\d{2})/(W[1-4]D[1-7])/$|", sec="user")
	 */
	public function only_day() {
		return "Location: 01";
	}

	/**
	 * @GET(uri="|^/(cs\d{3})/(20\d{2}-\d{2})/$|", sec="user");
	 */
	public function overview() {
        global $URI_PARAMS;
		global $VIEW_DATA;

		$course_num = $URI_PARAMS[1];
		$block = $URI_PARAMS[2];

		$course_detail = $this->courseDao->getCourse($course_num);
		$offering_detail = $this->offeringDao->getOfferingByCourse($course_num, $block);
		$offering_id = $offering_detail['id'];

		if (!$course_detail || !$offering_detail) {
			return "error/404.php";
		}
		$days_info = $this->dayDao->getDays($offering_id);

		// Make days associative array for calendar
		$days = array();
		foreach ($days_info as $day) {
			$days[$day["abbr"]] = $day;
		}

		$VIEW_DATA["course"] = strtoupper($course_num);
		$VIEW_DATA["title"] = $course_detail["name"];
		$VIEW_DATA["offering"] = $offering_detail;
		$VIEW_DATA["start"] = strtotime($offering_detail['start']);
		$VIEW_DATA["days"] = $days;

        return "overview.php";
	}

	/**
	 * @GET(uri="|^/(cs\d{3})/(20\d{2}-\d{2})/(W[1-4]D[1-7])/(\d{2})$|", sec="user")
	 */
	public function video() {
        global $URI_PARAMS;
		global $VIEW_DATA;
		$course_num = $URI_PARAMS[1];
		$block = $URI_PARAMS[2];
		$day = $URI_PARAMS[3];
		$video = $URI_PARAMS[4];
		$user_id = $_SESSION['user']['id'];

		// retrieve course and offering data from db
		$course_detail = $this->courseDao->getCourse($course_num);
		$offering_detail = $this->offeringDao->getOfferingByCourse($course_num, $block);
		if (!$course_detail || !$offering_detail) {
			return "error/404.php";
		}
		$days_info = $this->dayDao->getDays($offering_detail['id']);

		// Make days associative array for calendar
		$days = array();
		foreach ($days_info as $day_info) {
			$days[$day_info["abbr"]] = $day_info;
		}

		// more calendar related data
		$start = strtotime($offering_detail['start']);
		$now = time();
		$days_passed = floor(($now - $start) / (60 * 60 * 24));
		$page_w = $day[1];
		$page_d = $day[3];
		$curr_w = floor($days_passed / 7) + 1;
		$curr_d = ($days_passed % 7) + 1;

		// get video related data from filesystem
		chdir("res/${course_num}/${block}/${day}/vid/");
		$files = glob("*.mp4");
		$file_info = array();
		$totalDuration = 0;
		foreach ($files as $file) {
			$matches = array();
			preg_match("/.*(\d\d):(\d\d):(\d\d)\.(\d\d)\.mp4/", $file, $matches);
			$hours = $matches[1];
			$minutes = $matches[2];
			$seconds = $matches[3];
			$hundreth = $matches[4];
			// duration in hundreth of a second
			$duration = $hundreth + ($seconds * 100) + ($minutes * 60 * 100) + ($hours * 60 * 60 * 100);
			$totalDuration += $duration;
			$file_info[$file] = array();
			$file_info[$file]["duration"] = $duration;
			$file_info[$file]["parts"] = explode("_", $file);
			if ($file_info[$file]["parts"][0] == $video) {
				$video_file = $file;
			}
		}
		$totalHours = floor($totalDuration / (60 * 60 * 100));
		$totalMinutes = floor($totalDuration / (60*100) % 60);
		$totalSeconds = floor($totalDuration / 100 % 60);
		$totalTime = "";
		if ($totalHours > 0) {
			$totalTime .= $totalHours . ":";
		}
		$totalTime .= str_pad($totalMinutes, 2, "0", STR_PAD_LEFT) . ":";
		$totalTime .= str_pad($totalSeconds, 2, "0", STR_PAD_LEFT);

		// get questions for selected video
		$questions = $this->questionDao->getAllFor($file_info[$video_file]["parts"][2], $user_id);
		// get the replies for those questions
		if ($questions) {
			$qids = array();
			$replies = array();
			foreach ($questions as $question) {
				$qids[] = $question["id"];
				$replies[$question["id"]] = array();
			}
			$replies_data = $this->replyDao->getAllFor($qids, $user_id);
			foreach ($replies_data as $reply) {
				$replies[$reply["question_id"]][] = $reply;
			}	
		}

		// general course related
		$VIEW_DATA["course"] = $course_num;
		$VIEW_DATA["title"] = $course_detail['name'];
		$VIEW_DATA["block"] = $block;
		$VIEW_DATA["day"] = $day;

		// calendar related
		$VIEW_DATA["days"] = $days;
		$VIEW_DATA["start"] = $start;
		$VIEW_DATA["now"] = $now;
		$VIEW_DATA["page_w"] = $day[1];
		$VIEW_DATA["page_d"] = $day[3];
		$VIEW_DATA["curr_w"] = floor($days_passed / 7) + 1;
		$VIEW_DATA["curr_d"] = ($days_passed % 7) + 1;

		// videos related
		$VIEW_DATA["video"] = $video;
		$VIEW_DATA["files"] = $file_info;
		$VIEW_DATA["totalDuration"] = $totalDuration;
		$VIEW_DATA["totalTime"] = $totalTime;

		// questions related
		$VIEW_DATA["parsedown"] = new Parsedown();
		$VIEW_DATA["questions"] = $questions;
		$VIEW_DATA["replies"] = $replies;

		return "video.php";
	}
}

?>
