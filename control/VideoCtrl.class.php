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
	 * @Inject("ViewDao")
	 */
	public $viewDao;
	/**
	 * @Inject("QuestionDao")
	 */
	public $questionDao;

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
     * Gets the enrollemnt for a given offering
     * It may be good to move this function in a different class
     * @GET(uri="|.+/enrollment$|")
     */
    public function enrollemnt() {
		$offering_id = filter_input(INPUT_GET, "offering_id");
        $result = $this->enrollmentDao->getEnrollmentForOffering($offering_id);
        $ids = [];
        foreach ($result as $row) {
            $ids[$row["id"]] = $row;
        }
        return $ids;
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
	 * @GET(uri="|^/(cs\d{3})/(20\d{2}-\d{2})/info/?$|", sec="admin");
	 */
	public function overview_info() {
        global $URI_PARAMS;
		global $VIEW_DATA;

		$course_num = $URI_PARAMS[1];
		$block = $URI_PARAMS[2];
	
		$offering_detail = $this->offeringDao->getOfferingByCourse($course_num, $block);
		$offering_id = $offering_detail['id'];
		$view_info = $this->viewDao->overview($offering_id);

		$days = array();
		foreach ($view_info as $day) {
			$days[$day["abbr"]] = $day;
		}
		$days['total'] = $this->viewDao->overview_total($offering_id);
		return $days; // array automatically json encodes 
	}

	/**
	 * @GET(uri="|^/(cs\d{3})/(20\d{2}-\d{2})/viewers$|", sec="admin")
	 */
	public function offering_viewers() {
		$offering_id = filter_input(INPUT_GET, "offering_id");
		return $this->viewDao->offering_viewers($offering_id);
	}


	/**
	 * @GET(uri="|^/(cs\d{3})/(20\d{2}-\d{2})/(W[1-4]D[1-7])/$|", sec="user")
	 */
	public function only_day() {
		return "Location: 01";
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
		$questions = $this->questionDao->getAllFor($file_info[$video_file]["parts"][2]);

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

		return "videos.php";
	}

	/**
	 * @GET(uri="|^/(cs\d{3})/(20\d{2}-\d{2})/(W[1-4]D[1-7])/info/?$|", sec="admin")
	 */
	public function videos_info() {
		$day_id = filter_input(INPUT_GET, "day_id");
		$videos_info = $this->viewDao->day_views($day_id);
		$videos = array();
		foreach ($videos_info as $video) {
			$videos[$video["video"]] = $video;
		}
		$videos['total'] = $this->viewDao->day_total($day_id);
		return $videos; // array automatically json encodes 
	}

	/**
	 * @GET(uri="|^/(cs\d{3})/(20\d{2}-\d{2})/(W[1-4]D[1-7])/viewers$|", sec="admin")
	 */
	public function day_viewers() {
		$day_id = filter_input(INPUT_GET, "day_id");
		return $this->viewDao->day_viewers($day_id);
	}

	/**
	 * @GET(uri="|^/cs\d{3}/20\d{2}-\d{2}/(W[1-4]D[1-7]/)?start.*$|", sec="user")
	 */
	public function start() {
		$user_id = $_SESSION['user']['id'];
		$day_id = filter_input(INPUT_GET, "day_id");
		$video = filter_input(INPUT_GET, "video");
		return intval($this->viewDao->start($user_id, $day_id, $video));
	}

	/**
	 * @POST(uri="|^/cs\d{3}/20\d{2}-\d{2}/(W[1-4]D[1-7]/)?stop$|", sec="user")
	 */
	public function stop() {
		$view_id = filter_input(INPUT_POST, "view_id");
		return $this->viewDao->stop($view_id);
	}

	/**
	 * @GET(uri="|^/cs\d{3}/20\d{2}-\d{2}/(W[1-4]D[1-7]/)?pdf.*$|", sec="user")
	 */
	public function pdf() {
		$user_id = $_SESSION['user']['id'];
		$day_id = filter_input(INPUT_GET, "day_id");
		$file = filter_input(INPUT_GET, "file");
		return intval($this->viewDao->pdf($user_id, $day_id, $file));
	}
}

?>
