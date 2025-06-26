<?php

/**
 * OverviewCtr class -- wanted it to be a trait or super class, but that doesn't
 * play nice with the annotations system
 *
 * @author mzijlstra 12/27/2022
 */
#[Controller]
class OverviewCtrl
{
    #[Inject('OfferingDao')]
    public $offeringDao;

    #[Inject('DayDao')]
    public $dayDao;

    #[Inject('EnrollmentDao')]
    public $enrollmentDao;

    #[Inject('CourseDao')]
    public $courseDao;

    #[Inject('QuizDao')]
    public $quizDao;

    #[Inject('LabDao')]
    public $labDao;

    /**
     * Forces a remap of the annotations (used when not running in dev mode)
     */
    #[Get(uri: '^/remap$', sec: 'admin')]
    public function remap(): string
    {
        require 'AnnotationReader.class.php';
        $ac = new AnnotationReader;
        $ac->scan()->create_context();
        $ac->write('context.php');

        return 'Location: ../videos/';
    }

    /**
     * Redirects to latest offering for a course
     */
    #[Get(uri: "^/([a-z]{2,3}\d{3,4})/?$", sec: 'observer')]
    public function loggedIn(): string
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
     * Overview for a course offering
     *
     * This is the main overview page for a course offering, it shows the
     * schedule, quizzes, and labs
     */
    #[Get(uri: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/$", sec: 'observer')]
    public function offeringOverview(): string
    {
        // We're building on top of  overview -- run it first
        // this populates $VIEW_DATA with the overview related data
        $this->overview();

        global $URI_PARAMS;
        global $VIEW_DATA;

        $course_num = $URI_PARAMS[1];
        $course_detail = $this->courseDao->getCourse($course_num);

        if (! $course_detail) {
            return 'error/404.php';
        }

        // get all quizzes for this offering
        $oid = $VIEW_DATA['offering_id'];
        if (
            $_SESSION['user']['isAdmin'] ||
            $_SESSION['user']['isFaculty']
        ) {
            $quizzes = $this->quizDao->allForOffering($oid);
            $quiz_grading = $this->quizDao->getInstructorGradingStatus($oid);
            $labs = $this->labDao->allForOffering($oid);
            $lab_grading = $this->labDao->getInstructorGradingStatus($oid);
        } else {
            $user_id = $_SESSION['user']['id'];
            $quizzes = $this->quizDao->visibleForOffering($oid);
            $quiz_grading = $this->quizDao->getStudentGradingStatus($oid, $user_id);
            $labs = $this->labDao->visibleForOffering($oid);
            $lab_grading = $this->labDao->getStudentGradingStatus($oid, $user_id);
        }

        $quiz_graded = [];
        foreach ($quiz_grading as $grade) {
            $quiz_graded[$grade['id']] = $grade;
        }
        $lab_graded = [];
        foreach ($lab_grading as $grade) {
            $lab_graded[$grade['id']] = $grade;
        }
        $quiz_status = [];
        foreach ($quizzes as $quiz) {
            $status = 'not-started';
            if (isset($quiz_graded[$quiz['id']])
                && $quiz_graded[$quiz['id']]['answers'] != 0) {
                $status = 'graded';
                if ($quiz_graded[$quiz['id']]['ungraded'] != 0) {
                    $status = 'ungraded';
                }
            }
            $quiz_status[$quiz['id']] = $status;
        }
        $lab_status = [];
        foreach ($labs as $lab) {
            $status = 'not-started';
            if (isset($lab_graded[$lab['id']])
                && $lab_graded[$lab['id']]['answers'] != 0) {
                $status = 'graded';
                if ($lab_graded[$lab['id']]['ungraded'] != 0) {
                    $status = 'ungraded';
                }
            }
            $lab_status[$lab['id']] = $status;
        }

        // integrate the quizzes data into the days data
        foreach ($VIEW_DATA['days'] as $day) {
            $day['quizzes'] = [];
        }
        foreach ($quizzes as $quiz) {
            $VIEW_DATA['days'][$quiz['abbr']]['quizzes'][] = $quiz;
        }

        // labs are not associated with a day as they an be multiple days
        $labTimes = [];
        foreach ($labs as $lab) {
            $labTimes[] = [
                'lab' => $lab,
                'start' => strtotime($lab['start']),
                'stop' => strtotime($lab['stop']),
            ];
        }

        $VIEW_DATA['isRemembered'] = $_SESSION['user']['isRemembered'];
        $VIEW_DATA['labTimes'] = $labTimes;
        $VIEW_DATA['quiz_status'] = $quiz_status;
        $VIEW_DATA['lab_status'] = $lab_status;
        $VIEW_DATA['title'] = $course_detail['name'];
        $VIEW_DATA['area'] = 'course';

        return 'course/overview.php';
    }

    #[Get(uri: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/stat$", sec: 'observer')]
    public function statsOverview(): string
    {
        global $VIEW_DATA;
        global $URI_PARAMS;
        $course_num = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];

        $VIEW_DATA['course'] = strtoupper($course_num);
        $VIEW_DATA['block'] = $block;
        $VIEW_DATA['area'] = 'stat';

        return 'stat/overview.php';
    }

    /**
     * Helper function for overview (used both by general overview and attendance)
     */
    public function overview(): void
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $course_num = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];

        $offering_detail = $this->offeringDao->getOfferingByCourse($course_num, $block);
        $days_info = $this->dayDao->getDays($offering_detail['id']);

        // Make days associative array for calendar
        $days = [];
        foreach ($days_info as $day) {
            $days[$day['abbr']] = $day;
        }

        $VIEW_DATA['course'] = strtoupper($course_num);
        $VIEW_DATA['block'] = $offering_detail['block'];
        $VIEW_DATA['offering'] = $offering_detail;
        $VIEW_DATA['offering_id'] = $offering_detail['id']; // for header.php
        $VIEW_DATA['start'] = strtotime($offering_detail['start']);
        $VIEW_DATA['days'] = $days;
        $VIEW_DATA['now'] = time();
    }
}
