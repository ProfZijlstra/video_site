<?php

/**
 * Lab Grading Controller Class
 * @author mzijlstra 2024-03-16
 */

#[Controller(path: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/lab")]
class LabGradingCtrl
{
    #[Inject('LabDao')]
    public $labDao;

    #[Inject('DeliverableDao')]
    public $deliverableDao;

    #[Inject('SubmissionDao')]
    public $submissionDao;

    #[Inject('OfferingDao')]
    public $offeringDao;

    #[Inject('EnrollmentDao')]
    public $enrollmentDao;

    #[Inject('UserDao')]
    public $userDao;

    #[Inject('MarkdownHlpr')]
    public $markdownCtrl;

    #[Get(uri: "/(\d+)/grade$", sec: "assistant")]
    public function gradeQuiz()
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $course = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $lab_id = $URI_PARAMS[3];
        $offering = $this->offeringDao->getOfferingByCourse($course, $block);
        $offering_id = $offering['id'];
        $lab = $this->labDao->byId($lab_id);
        $submissions = $this->submissionDao->forLab($lab_id);
        $deliverables = $this->deliverableDao->forLab($lab_id);
        $enrollment = $this->enrollmentDao->getEnrollmentForOffering($offering_id);
        $key = "id";
        $list = $enrollment;

        $students = [];
        foreach ($enrollment as $student) {
            $students[$student['id']] = $student;
        }
        $groups = [];
        if ($lab['type'] == 'group') {
            $key = "group";
            $groups = $this->enrollmentDao->getGroups($offering['id']);
            $list = $groups;
            foreach ($groups as $group) {
                $group['members'] = [];
            }
            foreach ($enrollment as $student) {
                $groups[$student['group']]['members'][] = $student;
            }
        }

        $absent = [];
        $taken = [];
        $extra = [];
        foreach ($list as $item) {
            $absent[$item[$key]] = $item; // they all start as absent
        }
        if ($lab['type'] == 'individual') {
            $key = "user_id";
        }
        foreach ($submissions as $submission) {
            if ($absent[$submission[$key]]) {
                unset($absent[$submission[$key]]);
                $taken[$submission[$key]] = $submission;
            } else {
                $extra[$submission[$key]] = $submission;
            }
        }

        $VIEW_DATA['course'] = $course;
        $VIEW_DATA['block'] = $block;
        $VIEW_DATA['title'] = "Grade Overview";
        $VIEW_DATA['lab'] = $lab;
        $VIEW_DATA['deliverables'] = $deliverables;
        $VIEW_DATA['absent'] = $absent;
        $VIEW_DATA['taken'] = $taken;
        $VIEW_DATA['extra'] = $extra;
        $VIEW_DATA['type'] = $lab['type'];
        $VIEW_DATA['students'] = $students;
        $VIEW_DATA['groups'] = $groups;

        return "lab/gradeOverview.php";
    }
}
