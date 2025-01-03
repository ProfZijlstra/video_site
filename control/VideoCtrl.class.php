<?php

/**
 * Video Controller Class
 *
 * @author mzijlstra 05/18/2021
 */
#[Controller]
class VideoCtrl
{
    #[Inject('CourseDao')]
    public $courseDao;

    #[Inject('OfferingDao')]
    public $offeringDao;

    #[Inject('DayDao')]
    public $dayDao;

    #[Inject('EnrollmentDao')]
    public $enrollmentDao;

    #[Inject('CommentDao')]
    public $commentDao;

    #[Inject('ReplyDao')]
    public $replyDao;

    #[Inject('VideoDao')]
    public $videoDao;

    #[Inject('OverviewHlpr')]
    public $overviewHlpr;

    #[Inject('UserDao')]
    public $userDao;

    #[Inject('PdfDao')]
    public $pdfDao;

    #[Inject('LessonPartDao')]
    public $lessonPartDao;

    /**
     * Redirects to latest offering for a course
     */
    #[Get(uri: "^/([a-z]{2,3}\d{3,4})/?$", sec: 'observer')]
    public function loggedIn()
    {
        global $URI_PARAMS;
        global $MY_BASE;

        $course_num = $URI_PARAMS[1];
        $user_id = $_SESSION['user']['id'];

        // check enrollment
        $enrolled = $this->enrollmentDao->getEnrollmentForStudent($user_id);
        $offering = $this->offeringDao->getOfferingById($enrolled['offering_id']);
        $course = $offering['course_number'];
        if ($enrolled && $course_num == $course) {
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
     */
    #[Get(uri: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/([A-Z][1-4][A-Z][1-7])/$", sec: 'observer')]
    public function only_day()
    {
        return 'Location: 01';
    }

    #[Get(uri: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/$", sec: 'observer')]
    public function overview()
    {
        // We're building on top of  overview -- run it first
        // this populates $VIEW_DATA with the overview related data
        $this->overviewHlpr->overview();

        global $URI_PARAMS;
        global $VIEW_DATA;

        $course_num = $URI_PARAMS[1];
        $course_detail = $this->courseDao->getCourse($course_num);

        if (! $course_detail) {
            return 'error/404.php';
        }

        $VIEW_DATA['course'] = strtoupper($course_num);
        $VIEW_DATA['title'] = $course_detail['name'];
        $VIEW_DATA['area'] = 'course';
        $VIEW_DATA['faculty'] = $this->userDao->faculty();
        $VIEW_DATA['isRemembered'] = $_SESSION['user']['isRemembered'];

        return 'course/overview.php';
    }

    #[Get(uri: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/(W\dD\d)/(\d{2})$", sec: 'observer')]
    public function video()
    {
        global $URI_PARAMS;
        global $VIEW_DATA;
        $course_num = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $day = $URI_PARAMS[3];
        $file_idx = $URI_PARAMS[4];
        $user_id = $_SESSION['user']['id'];

        // retrieve course and offering data from db
        $course_detail = $this->courseDao->getCourse($course_num);
        $offering_detail = $this->offeringDao->getOfferingByCourse($course_num, $block);
        if (! $course_detail || ! $offering_detail) {
            return 'error/404.php';
        }
        $days_info = $this->dayDao->getDays($offering_detail['id']);

        // Make days associative array for calendar
        $days = [];
        foreach ($days_info as $day_info) {
            $days[$day_info['abbr']] = $day_info;
        }

        // more calendar related data
        $start = strtotime($offering_detail['start']);
        $now = time();
        $days_passed = floor(($now - $start) / (60 * 60 * 24));

        // get pdf and video related data
        $lessonParts = $this->lessonPartDao->forDay($course_num, $block, $day);
        $pdfs = $this->pdfDao->forDay($course_num, $block, $day);
        $videos = $this->videoDao->forDay($course_num, $block, $day);

        // get comments for all videos on this day
        $comments = [];
        $day_id = $this->dayDao->getDayId($course_num, $block, $day)[0];
        $comments = $this->commentDao->getAllForDay($day_id, $user_id);

        // get the replies for those comments
        $replies = [];
        if ($comments) {
            $cids = [];
            foreach ($comments as $vid_pdf) {
                foreach ($vid_pdf as $comment) {
                    $cids[] = $comment['id'];
                    $replies[$comment['id']] = [];
                }
            }
            $replies_data = $this->replyDao->getAllFor($cids, $user_id);
            foreach ($replies_data as $reply) {
                $replies[$reply['comment_id']][] = $reply;
            }
        }

        $VIEW_DATA['isRemembered'] = $_SESSION['user']['isRemembered'];

        // general course related
        $VIEW_DATA['course'] = $course_num;
        $VIEW_DATA['block'] = $block;
        $VIEW_DATA['day'] = $day;
        $VIEW_DATA['offering_id'] = $offering_detail['id'];
        $VIEW_DATA['title'] = $day.' - '.$days[$day]['desc'];

        // calendar related
        $VIEW_DATA['days'] = $days;
        $VIEW_DATA['start'] = $start;
        $VIEW_DATA['now'] = $now;
        $VIEW_DATA['page_w'] = $day[1];
        $VIEW_DATA['page_d'] = $day[3];
        $VIEW_DATA['curr_w'] = floor($days_passed / 7) + 1;
        $VIEW_DATA['curr_d'] = ($days_passed % 7) + 1;
        $VIEW_DATA['offering'] = $offering_detail;

        // videos related
        $VIEW_DATA['parts'] = $lessonParts;
        $VIEW_DATA['file_idx'] = $file_idx;
        $VIEW_DATA['videos'] = $videos['videos'];
        $VIEW_DATA['totalDuration'] = $videos['totalDuration'];
        $VIEW_DATA['totalTime'] = $videos['totalTime'];
        $VIEW_DATA['pdfs'] = $pdfs;

        // comments related
        require_once 'lib/Parsedown.php';
        $parsedown = new Parsedown;
        $parsedown->setSafeMode(true);
        $VIEW_DATA['parsedown'] = $parsedown;
        $VIEW_DATA['comments'] = $comments;
        $VIEW_DATA['replies'] = $replies;

        return 'course/video.php';
    }

    #[Post(uri: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/(W\dD\d)/title$", sec: 'instructor')]
    public function title()
    {
        global $URI_PARAMS;
        $course_num = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $day = $URI_PARAMS[3];

        $file = filter_input(INPUT_POST, 'file');
        $title = filter_input(INPUT_POST, 'title');
        // title should not have underscores
        if (strpos($title, '_') !== false) {
            http_response_code(400);

            return;
        }
        $res = $this->lessonPartDao->updateTitle($course_num, $block, $day, $file, $title);
        if (! $res) {
            http_response_code(500);
        }
    }

    #[Post(uri: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/(W\dD\d)/add$", sec: 'instructor')]
    public function addLessonPart()
    {
        global $URI_PARAMS;
        $course_num = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $day = $URI_PARAMS[3];

        $title = filter_input(INPUT_POST, 'title');
        // title should not have underscores
        if (strpos($title, '_') !== false) {
            http_response_code(400);

            return;
        }
        $res = $this->lessonPartDao->add($course_num, $block, $day, $title);
        if ($res === false) {
            http_response_code(500);
        }

        return "Location: $res";
    }

    #[Post(uri: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/(W\dD\d)/delete$", sec: 'instructor')]
    public function deleteLessonPart()
    {
        global $URI_PARAMS;
        $course_num = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $day = $URI_PARAMS[3];

        $part = filter_input(INPUT_POST, 'part');
        $res = $this->lessonPartDao->delete($course_num, $block, $day, $part);
        if (! $res) {
            http_response_code(500);
        }

        return 'Location: 01';
    }

    #[Post(uri: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/(W\dD\d)/reorder$", sec: 'instructor')]
    public function reorderLessonParts()
    {
        global $URI_PARAMS;
        $course_num = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $day = $URI_PARAMS[3];

        $order = filter_input(INPUT_POST, 'order');
        $ids = explode(',', $order);
        $res = $this->lessonPartDao->reorder($course_num, $block, $day, $ids);
        if (! $res) {
            http_response_code(500);
        }
    }

    /* TODO: everything below this needs to be refactored for the new UI */

    #[Post(uri: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/(W\dD\d)/increase$", sec: 'instructor')]
    public function increaseSequence()
    {
        global $URI_PARAMS;
        $course_num = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $day = $URI_PARAMS[3];

        $file = filter_input(INPUT_POST, 'file');
        $next_file = filter_input(INPUT_POST, 'next_file');

        $parts = explode('_', $file);
        $seq = intval($parts[0]);
        $seq += 1;

        $parts = explode('_', $next_file);
        $next_seq = intval($parts[0]);
        $next_seq -= 1;

        $this->videoDao->updateSequence($course_num, $block, $day, $file, $seq);
        $this->videoDao->updateSequence($course_num, $block, $day, $next_file, $next_seq);

        if ($seq < 10) {
            $seq = '0'.$seq;
        }

        return "Location: $seq";
    }

    #[Post(uri: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/(W\dD\d)/decrease$", sec: 'instructor')]
    public function decreaseSequence()
    {
        global $URI_PARAMS;
        $course_num = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $day = $URI_PARAMS[3];

        $file = filter_input(INPUT_POST, 'file');
        $prev_file = filter_input(INPUT_POST, 'prev_file');

        $parts = explode('_', $file);
        $seq = intval($parts[0]);
        $seq -= 1;

        $parts = explode('_', $prev_file);
        $prev_seq = intval($parts[0]);
        $prev_seq += 1;

        $this->videoDao->updateSequence($course_num, $block, $day, $file, $seq);
        $this->videoDao->updateSequence($course_num, $block, $day, $prev_file, $prev_seq);

        if ($seq < 10) {
            $seq = '0'.$seq;
        }

        return "Location: $seq";
    }

    /*#[Post(uri: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/(W\dD\d)/add$", sec: 'instructor')]*/
    public function addVideo()
    {
        global $URI_PARAMS;
        $course_num = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $day = $URI_PARAMS[3];

        // check upload error
        if ($_FILES['file']['error']) {
            echo 'Error uploading file (server settings)';

            return;
        }

        $video = $_FILES['file']['tmp_name'];
        $title = filter_input(INPUT_POST, 'title');

        // get duration
        $text = shell_exec("ffmpeg -i \"$video\" 2>&1");
        $matches = [];
        preg_match("/Duration: (\d\d:\d\d:\d\d\.\d\d)/", $text, $matches);
        if ($matches) {
            $duration = $matches[1];
        } else {
            // show error and exit
            echo 'Uploaded file does not appear to be a video';

            return;
        }

        // get next index number
        $idx = $this->videoDao->nextIndex($course_num, $block, $day);
        if ($idx < 10) {
            $idx = '0'.$idx;
        }

        // get current timestamp
        $now = new DateTimeImmutable;
        $timeStamp = $now->format('Y-m-d G-i-s');

        // finally move the uploaded file to the right location
        $name = "{$idx}_{$title}_{$timeStamp}_{$duration}.mp4";
        $this->videoDao->addVideo($course_num, $block, $day, $video, $name);

        return "Location: $idx";
    }
}
