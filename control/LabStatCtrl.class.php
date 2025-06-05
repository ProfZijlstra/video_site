<?php

/**
* Lab Statistics Controller
*
* @author mzijlstra 01 jun 2025
*/

#[Controller(path: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/lab")]
class LabStatCtrl
{
    #[Inject('DeliveryDao')]
    public $deliveryDao;

    #[Inject('OfferingDao')]
    public $offeringDao;

    #[Inject('DayDao')]
    public $dayDao;

    #[Inject('UserDao')]
    public $userDao;

    #[Inject('EnrollmentDao')]
    public $enrollmentDao;

    #[Get(uri: "/chart$", sec: 'observer')]
    public function myOverviewStats()
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $block = $URI_PARAMS[2];
        $user_id = $_SESSION['user']['id'];

        $VIEW_DATA['title'] = "$block Lab Stats";
        $VIEW_DATA['type'] = 'normal';

        return $this->overviewStats($user_id);
    }

    #[Get(uri: "/chart/(\d+)$", sec: 'instructor')]
    public function studentOverviewStats()
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $block = $URI_PARAMS[2];
        $user_id = $URI_PARAMS[3];
        $user = $this->userDao->retrieve($user_id);

        $VIEW_DATA['title'] = "$block Lab Stats: {$user['knownAs']} ";
        $VIEW_DATA['type'] = 'student';

        return $this->overviewStats($user_id);
    }

    private function overviewStats($user_id)
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $course_num = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $offering = $this->offeringDao->getOfferingByCourse($course_num, $block);
        $days = $this->dayDao->getDays($offering['id']);
        $possible = $this->deliveryDao->offeringPossible($offering['id']);
        $averages = $this->deliveryDao->offeringAverages($offering['id']);
        $person = $this->deliveryDao->offeringPerson($offering['id'], $user_id);

        $max = 0;
        foreach ($possible as $p) {
            if ($p['points'] > $max) {
                $max = $p['points'];
            }
        }

        $VIEW_DATA['max'] = $max;
        $VIEW_DATA['days'] = $days;
        $VIEW_DATA['possible'] = $possible;
        $VIEW_DATA['averages'] = $averages;
        $VIEW_DATA['person'] = $person;
        $VIEW_DATA['offering'] = $offering;
        $VIEW_DATA['course'] = $course_num;
        $VIEW_DATA['block'] = $block;

        return 'lab/offeringStats.php';
    }

    #[Get(uri: "/userChart$", sec: 'instructor')]
    public function overviewUserStats(): string
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $course_num = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];

        $offering = $this->offeringDao->getOfferingByCourse($course_num, $block);
        $students = $this->enrollmentDao->getStudentsForOffering($offering['id']);
        $observers = $this->enrollmentDao->getObserversForOffering($offering['id']);
        $studentsPoints = $this->deliveryDao->offeringUsers($offering['id']);

        $max = 0;
        foreach ($studentsPoints as $points) {
            if ($points > $max) {
                $max = $points;
            }
        }

        $VIEW_DATA['max'] = $max;
        $VIEW_DATA['course'] = strtoupper($course_num);
        $VIEW_DATA['title'] = "$block User Lab Stats";
        $VIEW_DATA['block'] = $block;
        $VIEW_DATA['offering'] = $offering;
        $VIEW_DATA['students'] = $students;
        $VIEW_DATA['points'] = $studentsPoints;

        return 'lab/userStats.php';
    }
}
