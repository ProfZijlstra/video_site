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
	 * @Inject("CommentDao")
	 */
	public $commentDao;
	/**
	 * @Inject("ReplyDao")
	 */
	public $replyDao;
	/**
	 * @Inject("VideoDao")
	 */
	public $videoDao;
	/**
	 * @Inject("OverviewHlpr")
	 */
	public $overviewHlpr;
	/**
	 * @Inject('UserDao');
	 */
	public $userDao;

    /**
     * Redirects to latest offering for a course
     * @GET(uri="!^/([a-z]{2,3}\d{3,4})/?$!", sec="observer")
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
			return "Location: $MY_BASE/{$course}/{$block}/";
		} else {
			// default to latest offering
			$data = $this->offeringDao->getLatestForCourse($course_num); 
			return "Location: $MY_BASE/{$data['number']}/{$data['block']}/";
		}

    }

	/**
	 * If the URL doesn't contain a video selection, just a day
	 * 
	 * @GET(uri="!^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/([A-Z][1-4][A-Z][1-7])/$!", sec="observer")
	 */
	public function only_day() {
		return "Location: 01";
	}

	/**
	 * @GET(uri="!^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/$!", sec="observer");
	 */
	public function offering() {
		// We're building on top of  overview -- run it first
        // this populates $VIEW_DATA with the overview related data
		$this->overviewHlpr->overview();

        global $URI_PARAMS;
		global $VIEW_DATA;

		$course_num = $URI_PARAMS[1];
		$course_detail = $this->courseDao->getCourse($course_num);

		if (!$course_detail) {
			return "error/404.php";
		}

		if ($_SESSION['user']['isAdmin']) {
			$VIEW_DATA['faculty'] = $this->userDao->faculty();
		}

		$VIEW_DATA["course"] = strtoupper($course_num);
		$VIEW_DATA["title"] = $course_detail["name"];
		$VIEW_DATA["faculty"] = $this->userDao->faculty();
		$VIEW_DATA['isRemembered'] = $_SESSION['user']['isRemembered'];

        return "offering.php";
	}

	/**
	 * @GET(uri="!^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/(W\dD\d)/(\d{2})$!", sec="observer")
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

		// get comments for selected video
		$comments = $this->commentDao->getAllFor($video_file["parts"][2], $user_id);
		// get the replies for those comments
		$replies = array();
		if ($comments) {
			$qids = array();
			foreach ($comments as $comment) {
				$qids[] = $comment["id"];
				$replies[$comment["id"]] = array();
			}
			$replies_data = $this->replyDao->getAllFor($qids, $user_id);
			foreach ($replies_data as $reply) {
				$replies[$reply["comment_id"]][] = $reply;
			}	
		}

		// construct PDF file name for this video
		$pdf_file = "res/{$course_num}/{$block}/{$day}/pdf/" .
			$video_file["parts"][0] . "_" . $video_file["parts"][1] . ".pdf";

		// fix video play speed if broken
		if (!$_SESSION['user']['speed'] || $_SESSION['user']['speed'] < 0.4) {
			$_SESSION['user']['speed'] = 1;
		}
		
		// settings
		$VIEW_DATA['speed'] = $_SESSION['user']['speed'];
		$VIEW_DATA['theater'] = $_SESSION['user']['theater'];
		$VIEW_DATA['autoplay'] = $_SESSION['user']['autoplay'];
		$VIEW_DATA['isRemembered'] = $_SESSION['user']['isRemembered'];

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
		$VIEW_DATA["offering"] = $offering_detail;

		// videos related
		$VIEW_DATA["video"] = $video;
		$VIEW_DATA["files"] = $file_info;
		$VIEW_DATA["totalDuration"] = $videos["totalDuration"];
		$VIEW_DATA["totalTime"] = $videos["totalTime"];
		$VIEW_DATA["pdf"] = file_exists($pdf_file);
		$VIEW_DATA["pdf_file"] = $pdf_file;

		// comments related
		$VIEW_DATA["parsedown"] = new Parsedown();
		$VIEW_DATA["comments"] = $comments;
		$VIEW_DATA["replies"] = $replies;

		return "video.php";
	}

	/**
	 * @POST(uri="!^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/(W\dD\d)/autoplay$!", sec="observer")
	 */
	public function autoplay() {
		$toggle = filter_input(INPUT_POST, "toggle");
		$_SESSION['user']["autoplay"] = $toggle;
		setcookie("autoplay", $toggle, time() + 7*24*60*60, "/videos");
	}

		/**
	 * @POST(uri="!^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/(W\dD\d)/theater$!", sec="observer")
	 */
	public function theater() {
		$toggle = filter_input(INPUT_POST, "toggle");
		$_SESSION['user']["theater"] = $toggle;
	}
}

?>
