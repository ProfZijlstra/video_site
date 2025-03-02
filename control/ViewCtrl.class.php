<?php

/**
 * View Controller Class
 *
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

    #[Get(uri: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/W\dD\d/start.*$", sec: 'observer')]
    public function start(): int
    {
        $user_id = $_SESSION['user']['id'];
        $day_id = filter_input(INPUT_GET, 'day_id', FILTER_VALIDATE_INT);
        $speed = filter_input(INPUT_GET, 'speed', FILTER_VALIDATE_FLOAT);
        $video = urldecode(filter_input(INPUT_GET, 'video'));

        return intval($this->viewDao->start($user_id, $day_id, $video, $speed));
    }

    #[Post(uri: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/W\dD\d/stop$", sec: 'none')]
    public function stop(): void
    {
        $view_id = filter_input(INPUT_POST, 'view_id');
        $speed = filter_input(INPUT_POST, 'speed', FILTER_VALIDATE_FLOAT);
        $this->viewDao->stop($view_id, $speed);
    }

    #[Get(uri: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/W\dD\d/pdf.*$", sec: 'observer')]
    public function pdf(): int
    {
        $user_id = $_SESSION['user']['id'];
        $day_id = filter_input(INPUT_GET, 'day_id');
        $file = filter_input(INPUT_GET, 'file');

        return intval($this->viewDao->pdf($user_id, $day_id, $file));
    }

    #[Get(uri: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/chart$", sec: 'observer')]
    public function myOverviewStats()
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $block = $URI_PARAMS[2];
        $user_id = $_SESSION['user']['id'];

        $VIEW_DATA['title'] = "$block View Stats";
        $VIEW_DATA['type'] = 'normal';

        return $this->overviewStats($user_id);
    }

    #[Get(uri: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/chart/(\d+)$", sec: 'instructor')]
    public function studentOverviewStats()
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $block = $URI_PARAMS[2];
        $user_id = $URI_PARAMS[3];
        $user = $this->userDao->retrieve($user_id);

        $VIEW_DATA['title'] = "$block {$user['knownAs']} View Stats";
        $VIEW_DATA['type'] = 'student';

        return $this->overviewStats($user_id);
    }

    private function overviewStats($user_id): string
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $course_num = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];

        $offering = $this->offeringDao->getOfferingByCourse($course_num, $block);
        $days = $this->dayDao->getDays($offering['id']);
        $videos = $this->videoDao->forOffering($course_num, $block);
        $averages = $this->viewDao->offeringAverages($offering['id']);
        $person = $this->viewDao->offeringPerson($offering['id'], $user_id);
        $total = $this->viewDao->offeringTotal($offering['id']);

        $max = 0;
        foreach ($videos as $video_day) {
            $time = $video_day['totalDuration'] / 360000;
            if ($time > $max) {
                $max = $time;
            }
        }
        foreach ($person as $person_day) {
            $time = $person_day['time'];
            if ($time > $max) {
                $max = $time;
            }
        }
        foreach ($averages as $average_day) {
            if (! $average_day['users']) {
                continue;
            }
            $time = $average_day['time'] / $average_day['users'];
            if ($time > $max) {
                $max = $time;
            }
        }

        $VIEW_DATA['max'] = $max;
        $VIEW_DATA['days'] = $days;
        $VIEW_DATA['videos'] = $videos;
        $VIEW_DATA['averages'] = $averages;
        $VIEW_DATA['person'] = $person;
        $VIEW_DATA['total'] = $total;
        $VIEW_DATA['offering'] = $offering;
        $VIEW_DATA['course'] = $course_num;
        $VIEW_DATA['block'] = $block;

        return 'course/view/offeringStats.php';
    }

    #[Get(uri: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/userChart$", sec: 'instructor')]
    public function overviewUserStats(): string
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $course_num = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];

        $offering = $this->offeringDao->getOfferingByCourse($course_num, $block);
        $students = $this->enrollmentDao->getStudentsForOffering($offering['id']);
        $observers = $this->enrollmentDao->getObserversForOffering($offering['id']);
        $views = $this->viewDao->offeringUsers($offering['id']);

        $max = 0;
        foreach ($views as $view) {
            if ($view['time'] > $max) {
                $max = $view['time'];
            }
        }

        $no_view = [];
        $active_observers = [];
        foreach ($students as $student) {
            $no_view[$student['id']] = true;
        }
        foreach ($views as $view) {
            unset($no_view[$view['user_id']]);
            if ($observers[$view['user_id']]) {
                $active_observers[$view['user_id']] = $observers[$view['user_id']];
            }
        }

        $VIEW_DATA['max'] = $max;
        $VIEW_DATA['course'] = strtoupper($course_num);
        $VIEW_DATA['title'] = "$block User View Stats";
        $VIEW_DATA['block'] = $block;
        $VIEW_DATA['offering'] = $offering;
        $VIEW_DATA['students'] = $students;
        $VIEW_DATA['observers'] = $active_observers;
        $VIEW_DATA['views'] = $views;
        $VIEW_DATA['no_view'] = $no_view;

        return 'course/view/userStats.php';
    }

    #[Get(uri: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/data$", sec: 'instructor')]
    public function overviewData(): string
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $course_num = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];

        $offering = $this->offeringDao->getOfferingByCourse($course_num, $block);
        $data = $this->viewDao->offeringData($offering['id']);
        $enrolled = $this->enrollmentDao->getEnrollmentForOffering($offering['id']);
        $users = [];
        foreach ($enrolled as $user) {
            $users[$user['id']] = $user;
        }

        $VIEW_DATA['type'] = 'overview';
        $VIEW_DATA['title'] = "$block View Data";
        $VIEW_DATA['block'] = $block;
        $VIEW_DATA['course'] = $course_num;
        $VIEW_DATA['offering'] = $offering;
        $VIEW_DATA['data'] = $data;
        $VIEW_DATA['users'] = $users;

        return 'course/view/data.php';
    }

    #[Get(uri: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/(W\dD\d)/chart$", sec: 'observer')]
    public function myDayStats(): string
    {
        global $VIEW_DATA;
        global $URI_PARAMS;

        $block = $URI_PARAMS[2];
        $day = $URI_PARAMS[3];
        $user_id = $_SESSION['user']['id'];

        $VIEW_DATA['title'] = "$block $day View Stats";

        return $this->dayStats($user_id);
    }

    #[Get(uri: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/(W\dD\d)/chart/(\d+)$", sec: 'instructor')]
    public function studentDayStats(): string
    {
        global $VIEW_DATA;
        global $URI_PARAMS;

        $block = $URI_PARAMS[2];
        $day = $URI_PARAMS[3];
        $user_id = $URI_PARAMS[4];
        $user = $this->userDao->retrieve($user_id);

        $VIEW_DATA['title'] = "$block $day {$user['knownAs']} View Stats";

        return $this->dayStats($user_id);
    }

    private function dayStats($user_id): string
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $course_num = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $day = $URI_PARAMS[3];

        $offering = $this->offeringDao->getOfferingByCourse($course_num, $block);
        $videos = $this->videoDao->forDay($course_num, $block, $day);
        $averages = $this->viewDao->dayAverages($offering['id'], $day);
        $person = $this->viewDao->dayPerson($offering['id'], $day, $user_id);
        $total = $this->viewDao->dayTotal($offering['id'], $day);

        $max = 0;
        foreach ($videos['videos'] as $video) {
            $time = $video['duration'] / 360000;
            if ($time > $max) {
                $max = $time;
            }
        }
        foreach ($person as $idx => $person_day) {
            if (! intval($idx)) {
                continue;
            }
            $time = $person_day['time'];
            if ($time > $max) {
                $max = $time;
            }
        }
        foreach ($averages as $idx => $average_day) {
            if (! $average_day['users'] || ! intval($idx)) {
                continue;
            }
            $time = $average_day['time'] / $average_day['users'];
            if ($time > $max) {
                $max = $time;
            }
        }

        $VIEW_DATA['max'] = $max;
        $VIEW_DATA['videos'] = $videos;
        $VIEW_DATA['averages'] = $averages;
        $VIEW_DATA['person'] = $person;
        $VIEW_DATA['total'] = $total;
        $VIEW_DATA['offering'] = $offering;
        $VIEW_DATA['day'] = $day;
        $VIEW_DATA['course'] = $course_num;
        $VIEW_DATA['block'] = $block;

        return 'course/view/dayStats.php';
    }

    #[Get(uri: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/(W\dD\d)/userChart$", sec: 'instructor')]
    public function dayUserStats(): string
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $course_num = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $day = $URI_PARAMS[3];
        $user_id = $_SESSION['user']['id'];

        $offering = $this->offeringDao->getOfferingByCourse($course_num, $block);
        $students = $this->enrollmentDao->getStudentsForOffering($offering['id']);
        $observers = $this->enrollmentDao->getObserversForOffering($offering['id']);
        $views = $this->viewDao->dayUsers($offering['id'], $day);

        $max = 0;
        foreach ($views as $view) {
            if ($view['time'] > $max) {
                $max = $view['time'];
            }
        }

        $no_view = [];
        $active_observers = [];
        foreach ($students as $student) {
            $no_view[$student['id']] = true;
        }
        foreach ($views as $view) {
            unset($no_view[$view['user_id']]);
            if ($observers[$view['user_id']]) {
                $active_observers[$view['user_id']] = $observers[$view['user_id']];
            }
        }

        $VIEW_DATA['max'] = $max;
        $VIEW_DATA['course'] = strtoupper($course_num);
        $VIEW_DATA['title'] = "$block $day User Stats";
        $VIEW_DATA['block'] = $block;
        $VIEW_DATA['offering'] = $offering;
        $VIEW_DATA['students'] = $students;
        $VIEW_DATA['observers'] = $active_observers;
        $VIEW_DATA['views'] = $views;
        $VIEW_DATA['no_view'] = $no_view;

        return 'course/view/userStats.php';
    }

    #[Get(uri: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/(W\dD\d)/data$", sec: 'instructor')]
    public function dayData(): string
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $course_num = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $day = $URI_PARAMS[3];

        $offering = $this->offeringDao->getOfferingByCourse($course_num, $block);
        $data = $this->viewDao->dayData($offering['id'], $day);
        $enrolled = $this->enrollmentDao->getEnrollmentForOffering($offering['id']);
        $users = [];
        foreach ($enrolled as $user) {
            $users[$user['id']] = $user;
        }

        $VIEW_DATA['type'] = 'day';
        $VIEW_DATA['title'] = "$block $day View Data";
        $VIEW_DATA['block'] = $block;
        $VIEW_DATA['course'] = $course_num;
        $VIEW_DATA['offering'] = $offering;
        $VIEW_DATA['data'] = $data;
        $VIEW_DATA['users'] = $users;

        return 'course/view/data.php';
    }
}
