<?php

/**
 * View Controller Class
 * @author mzijlstra 09/27/2021
 */

#[Controller]
class ViewCtrl
{
    #[Inject('ViewDao')]
    public $viewDao;
    #[Inject('EnrollmentDao')]
    public $enrollmentDao;
    #[Inject('OfferingDao')]
    public $offeringDao;
    #[Inject('CourseDao')]
    public $courseDao;
    #[Inject('DayDao')]
    public $dayDao;
    #[Inject('UserDao')]
    public $userDao;
    #[Inject('VideoDao')]
    public $videoDao;


    #[Get(uri: "!^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/W\dD\d/start.*$!", sec: "observer")]
    public function start()
    {
        $user_id = $_SESSION['user']['id'];
        $day_id = filter_input(INPUT_GET, "day_id");
        $video = filter_input(INPUT_GET, "video");
        return intval($this->viewDao->start($user_id, $day_id, $video, $_SESSION['user']['speed']));
    }


    #[Post(uri: "!^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/W\dD\d/stop$!", sec: "none")]
    public function stop()
    {
        global $URI_PARAMS;

        $view_id = filter_input(INPUT_POST, "view_id");
        $this->viewDao->stop($view_id, $_SESSION['user']['speed']);
    }


    #[Post(uri: "!^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/W\dD\d/speed$!", sec: "observer")]
    public function speed()
    {
        $speed = filter_input(INPUT_POST, "speed");
        $_SESSION['user']['speed'] = $speed;
        setcookie("viewspeed", $speed, time() + 7 * 24 * 60 * 60, "/videos");
    }

    #[Get(uri: "!^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/W\dD\d/pdf.*$!", sec: "observer")]
    public function pdf()
    {
        $user_id = $_SESSION['user']['id'];
        $day_id = filter_input(INPUT_GET, "day_id");
        $file = filter_input(INPUT_GET, "file");
        return intval($this->viewDao->pdf($user_id, $day_id, $file));
    }

    #[Get(uri: "!^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/W\dD\d/views/(\d+)?$!", sec: "instructor")]
    public function views()
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $course_num = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $user_id = $URI_PARAMS[3];

        $course_detail = $this->courseDao->getCourse($course_num);
        $offering_detail = $this->offeringDao->getOfferingByCourse($course_num, $block);
        $offering_id = $offering_detail['id'];

        if (!$course_detail || !$offering_detail) {
            return "error/404.php";
        }
        $days_info = $this->dayDao->getDays($offering_id);
        $views = $this->viewDao->person_views($offering_id, $user_id);
        $videos = $this->videoDao->forOffering($course_num, $block);
        $user = $this->userDao->retrieve($user_id);

        // Make days associative array
        $days = array();
        foreach ($days_info as $day) {
            $days[$day["abbr"]] = array("day" => $day);
        }
        foreach ($videos as $day => $day_videos) {
            $days[$day]["video"] = $day_videos;
        }
        foreach ($views as $view) {
            $days[$view["abbr"]]["video"]["file_info"][$view["video"]]["hours"] = $view["hours"];
            $days[$view["abbr"]]["video"]["file_info"][$view["video"]]["video_views"] = $view["video_views"];
            $days[$view["abbr"]]["video"]["file_info"][$view["video"]]["pdf"] = $view["pdf"];
        }

        $VIEW_DATA["user"] = $user;
        $VIEW_DATA["course"] = strtoupper($course_num);
        $VIEW_DATA["block"] = $block;
        $VIEW_DATA["title"] = $course_detail["name"];
        $VIEW_DATA["offering"] = $offering_detail;
        $VIEW_DATA["days"] = $days;

        return "views.php";
    }

    #[Get(uri: "!^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/info/?$!", sec: "instructor")]
    public function offering_info()
    {
        global $URI_PARAMS;

        $course_num = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];

        $offering_detail = $this->offeringDao->getOfferingByCourse($course_num, $block);
        $offering_id = $offering_detail['id'];
        $view_info = $this->viewDao->offering($offering_id);

        $days = array();
        foreach ($view_info as $day) {
            $days[$day["abbr"]] = $day;
        }
        $days['total'] = $this->viewDao->offering_total($offering_id);
        return $days; // array automatically json encodes 
    }

    #[Get(uri: "!^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/W\dD\d/info/?$!", sec: "instructor")]
    public function videos_info()
    {
        $day_id = filter_input(INPUT_GET, "day_id");
        $videos_info = $this->viewDao->day_views($day_id);
        $videos = array();
        foreach ($videos_info as $video) {
            $videos[$video["video"]] = $video;
        }
        $videos['total'] = $this->viewDao->day_total($day_id);
        return $videos; // array automatically json encodes 
    }

    #[Get(uri: "!^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/viewers$!", sec: "instructor")]
    public function offering_viewers()
    {
        $offering_id = filter_input(INPUT_GET, "offering_id");
        return $this->viewDao->offering_viewers($offering_id);
    }

    #[Get(uri: "!^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/W\dD\d/viewers$!", sec: "instructor")]
    public function day_viewers()
    {
        $day_id = filter_input(INPUT_GET, "day_id");
        return $this->viewDao->day_viewers($day_id);
    }

    #[Get(uri: "!^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/W\dD\d/\d{2}/viewers$!", sec: "instructor")]
    public function video_viewers()
    {
        $day_id = filter_input(INPUT_GET, "day_id");
        $video = filter_input(INPUT_GET, "video");
        return $this->viewDao->video_viewers($day_id, $video);
    }


    #[Get(uri: "!/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)(/.+)?/enrollment$!", sec: "instructor")]
    public function enrollemnt()
    {
        $offering_id = filter_input(INPUT_GET, "offering_id");
        $result = $this->enrollmentDao->getEnrollmentForOffering($offering_id);
        $ids = [];
        foreach ($result as $row) {
            $ids[$row["id"]] = $row;
        }
        return $ids;
    }
}

