<?php

/**
 * View Controller Class
 * @author mzijlstra 09/27/2021
 *
 * @Controller
 */
class ViewCtrl {
	/**
	 * @Inject("ViewDao")
	 */
	public $viewDao;
	/**
	 * @Inject("EnrollmentDao")
	 */
	public $enrollmentDao;
   	/**
	 * @Inject("OfferingDao")
	 */
	public $offeringDao;


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
	 * @GET(uri="|^/(cs\d{3})/(20\d{2}-\d{2})/viewers$|", sec="admin")
	 */
	public function offering_viewers() {
		$offering_id = filter_input(INPUT_GET, "offering_id");
		return $this->viewDao->offering_viewers($offering_id);
	}

	/**
	 * @GET(uri="|^/(cs\d{3})/(20\d{2}-\d{2})/(W[1-4]D[1-7]/)+viewers$|", sec="admin")
	 */
	public function day_viewers() {
		$day_id = filter_input(INPUT_GET, "day_id");
		return $this->viewDao->day_viewers($day_id);
	}

	/**
	 * @GET(uri="|^/(cs\d{3})/(20\d{2}-\d{2})/(W[1-4]D[1-7])/\d{2}/viewers$|", sec="admin")
	 */
	public function video_viewers() {
		$day_id = filter_input(INPUT_GET, "day_id");
		$video = filter_input(INPUT_GET, "video");
		return $this->viewDao->video_viewers($day_id, $video);
	}


    /**
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
}