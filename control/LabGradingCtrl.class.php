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

    #[Inject('SubmissionDao')]
    public $submissionDao;

    #[Inject('DeliverableDao')]
    public $deliverableDao;

    #[Inject('DeliveryDao')]
    public $deliveryDao;

    #[Inject('OfferingDao')]
    public $offeringDao;

    #[Inject('EnrollmentDao')]
    public $enrollmentDao;

    #[Inject('MarkdownHlpr')]
    public $markdownCtrl;

    #[Inject('UserDao')]
    public $userDao;

    #[Get(uri: "/(\d+)/grade$", sec: "assistant")]
    public function gradeLab()
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

        $students = [];
        $groups = [];
        $members = [];
        $absent = [];
        $taken = [];
        $extra = [];
        foreach ($enrollment as $student) {
            $students[$student['id']] = $student;
        }
        if ($lab['type'] == 'group') {
            $groups = $this->enrollmentDao->getGroups($offering['id']);
            foreach ($groups as $group) {
                $members[$group]  = [];
            }
            foreach ($enrollment as $student) {
                $members[$student['group']][] = $student;
            }
            foreach ($groups as $group) {
                $absent[$group] = $group;
            }
            $key = "group";
        } else {
            foreach ($enrollment as $student) {
                $absent[$student["id"]] = $student;
            }
            $key = "user_id";
        }

        // they all start absent
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
        $VIEW_DATA['students'] = $students;
        $VIEW_DATA['groups'] = $groups;
        $VIEW_DATA['members'] = $members;

        return "lab/gradeOverview.php";
    }

    #[Get(uri: "/(\d+)/deliverable/(\d+)$", sec: "assistant")]
    public function gradeDeliverable()
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $course = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $deliv_id = $URI_PARAMS[4];

        // get the deliverable and the deliveries
        $deliverable = $this->deliverableDao->byId($deliv_id);
        $deliveries = $this->deliveryDao->forDeliverable($deliv_id);

        $VIEW_DATA['course'] = $course;
        $VIEW_DATA['block'] = $block;
        $VIEW_DATA['title'] = "Grade Deliverable";
        $VIEW_DATA['deliverable'] = $deliverable;
        $VIEW_DATA['deliveries'] = $deliveries;

        return "lab/gradeDeliverable.php";
    }


    #[Get(uri: "/(\d+)/submission/(\d+)$", sec: "assistant")]
    public function gradeSubmission()
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $course = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $lab_id = $URI_PARAMS[3];
        $submission_id = $URI_PARAMS[4];

        $offering = $this->offeringDao->getOfferingByCourse($course, $block);
        $deliverables = $this->deliverableDao->forLab($lab_id);
        $submission = $this->submissionDao->byId($submission_id);
        $deliveries = $this->deliveryDao->forSubmission($submission_id);

        $members = [];
        if ($submission['group']) {
            $members = $this->enrollmentDao->getMembers($offering['id'], $submission['group']);
        } else {
            $members[] = $this->userDao->retrieve($submission['user_id']);
        }

        $VIEW_DATA['course'] = $course;
        $VIEW_DATA['block'] = $block;
        $VIEW_DATA['title'] = "Grade Submission";
        $VIEW_DATA['members'] = $members;
        $VIEW_DATA['submission'] = $submission;
        $VIEW_DATA['deliverables'] = $deliverables;
        $VIEW_DATA['deliveries'] = $deliveries;

        return "lab/gradeSubmission.php";
    }

    #[Put(uri: "/(\d+)/delivery/(\d+)$", sec: "assistant")]
    public function gradeDelivery()
    {
        global $URI_PARAMS;
        global $_PUT;

        $delivery_id = $URI_PARAMS[4];
        $points = $_PUT['points'];
        $shifted = $_PUT['comment'];
        $hasMarkDown = $_PUT['hasMarkDown'] ? 1 : 0;
        $comment = $this->markdownCtrl->ceasarShift($shifted);
        $this->deliveryDao->grade($delivery_id, $points, $comment, $hasMarkDown);
    }

    #[Post(uri: "/(\d+)/delivery/(\d+)$", sec: "assistant")]
    public function gradeForNotDelivered() // can be used for late delivery
    {
        global $URI_PARAMS;

        $lab_id = $URI_PARAMS[3];
        $deliverable_id = $URI_PARAMS[4];
        $submission_id = filter_input(INPUT_POST, "submission_id", FILTER_VALIDATE_INT);
        $user_id = filter_input(INPUT_POST, "user_id", FILTER_VALIDATE_INT);
        $group = filter_input(INPUT_POST, "group");
        $points = filter_input(INPUT_POST, "points", FILTER_VALIDATE_INT);
        $hasMarkDown = filter_input(INPUT_POST, "hasMarkDown") ? 1 : 0;
        $shifted = filter_input(INPUT_POST, "comment");
        $comment = $this->markdownCtrl->ceasarShift($shifted);


        if (!$submission_id) {
            $submission_id = $this->submissionDao->create(
                $lab_id,
                $user_id,
                $group
            );
        }

        $delivery_id = $this->deliveryDao->createGrade(
            $submission_id,
            $deliverable_id,
            $points,
            $comment,
            $hasMarkDown
        );
        return ["submission_id" => $submission_id, "id" => $delivery_id];
    }
}
