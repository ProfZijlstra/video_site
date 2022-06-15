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
	 * @Inject("VideoDao")
	 */
	public $videoDao;

    /**
     * Redirects to latest offering for a course
     * @GET(uri="!^/(cs\d{3})/?$!", sec="user")
     */
    public function loggedIn() {
        global $URI_PARAMS;
		global $MY_BASE;

		$course_num = $URI_PARAMS[1];
		$user_id = $_SESSION['user']['id'];
		$enrolled = $this->enrollmentDao->getEnrollmentForStudent($user_id);
		if ($enrolled) {
			$offering = $this->offeringDao->getOfferingById($enrolled["offering_id"]);
			$course = $offering['course_number'];
			$block = $offering['block'];
			return "Location: $MY_BASE/${course}/${block}/";
		} else {
			// default to latest offering
			$data = $this->offeringDao->getLatestForCourse($course_num); 
			return "Location: $MY_BASE/${data['number']}/${data['block']}/";
		}

    }

	/**
	 * If the URL doesn't contain a video selection, just a day
	 * 
	 * @GET(uri="!^/(cs\d{3})/(20\d{2}-\d{2})/([A-Z][1-4][A-Z][1-7])/$!", sec="user")
	 */
	public function only_day() {
		return "Location: 01";
	}

	/**
	 * @GET(uri="!^/(cs\d{3})/(20\d{2}-\d{2})/$!", sec="user");
	 */
	public function offering() {
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
		$VIEW_DATA["block"] = $offering_detail['block'];
		$VIEW_DATA["title"] = $course_detail["name"];
		$VIEW_DATA["offering"] = $offering_detail;
		$VIEW_DATA["offering_id"] = $offering_detail["id"]; // for header.php
		$VIEW_DATA["start"] = strtotime($offering_detail['start']);
		$VIEW_DATA["days"] = $days;
		$VIEW_DATA["now"] = time();

        return "offering.php";
	}

	/**
	 * @GET(uri="!^/(cs\d{3})/(20\d{2}-\d{2})/(W[1-4]D[1-7])/(\d{2})$!", sec="user")
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

		// get video related data
		$video_file = array();
		$videos = $this->videoDao->forDay($course_num, $block, $day);
		$file_info = $videos["file_info"];
		foreach ($file_info as $file) {
			if ($file["parts"][0] == $video) {
				$video_file = $file;
				break;
			}
		}

		// get questions for selected video
		$questions = $this->questionDao->getAllFor($video_file["parts"][2], $user_id);
		// get the replies for those questions
		$replies = array();
		if ($questions) {
			$qids = array();
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
		$VIEW_DATA["offering_id"] = $offering_detail['id'];
		$VIEW_DATA["title"] = $day . " - " . $days[$day]["desc"];

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
		$VIEW_DATA["totalDuration"] = $videos["totalDuration"];
		$VIEW_DATA["totalTime"] = $videos["totalTime"];

		// questions related
		$VIEW_DATA["parsedown"] = new Parsedown();
		$VIEW_DATA["questions"] = $questions;
		$VIEW_DATA["replies"] = $replies;

		return "video.php";
	}

	/**
	 * @POST(uri="!^/(cs\d{3})/(20\d{2}-\d{2})/(W[1-4]D[1-7])/autoplay$!", sec="user")
	 */
	public function autoplay() {
		$toggle = filter_input(INPUT_POST, "toggle");
		$_SESSION['user']["autoplay"] = $toggle;
	}

		/**
	 * @POST(uri="!^/(cs\d{3})/(20\d{2}-\d{2})/(W[1-4]D[1-7])/theater$!", sec="user")
	 */
	public function theater() {
		$toggle = filter_input(INPUT_POST, "toggle");
		$_SESSION['user']["theater"] = $toggle;
	}
}

?>
