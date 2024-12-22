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

    #[Inject('AttachmentDao')]
    public $attachmentDao;

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
        $observers = [];
        $groups = [];
        $members = [];
        $absent = [];

        $taken = [];
        $extra = [];
        $none = [];
        foreach ($enrollment as $student) {
            if ($student['auth'] == 'observer') {
                $observers[$student['id']] = $student;
            }
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
                if (!$group) {
                    continue;
                }
                $absent[$group] = $group;
            }
            $key = "group";
        } else {
            // they all start absent
            foreach ($enrollment as $student) {
                if ($student['auth'] == 'observer') {
                    continue;
                }
                $absent[$student["id"]] = $student;
            }
            $key = "user_id";
        }

        // sort submissions into taken, none, extra
        foreach ($submissions as $submission) {
            if ($absent[$submission[$key]]) {
                if ($submission['delivs'] > 0) {
                    $taken[$submission[$key]] = $submission;
                } else {
                    $none[$submission[$key]] = $submission;
                }
                unset($absent[$submission[$key]]);
            } else {
                $extra[$submission[$key]] = $submission;
            }
        }

        $VIEW_DATA['course'] = $course;
        $VIEW_DATA['block'] = $block;
        $VIEW_DATA['title'] = "Grade Overview";
        $VIEW_DATA['offering'] = $offering;
        $VIEW_DATA['lab'] = $lab;
        $VIEW_DATA['deliverables'] = $deliverables;
        $VIEW_DATA['absent'] = $absent;
        $VIEW_DATA['none'] = $none;
        $VIEW_DATA['taken'] = $taken;
        $VIEW_DATA['extra'] = $extra;
        $VIEW_DATA['students'] = $students;
        $VIEW_DATA['observers'] = $observers;
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
        $lab_id = $URI_PARAMS[3];
        $deliv_id = $URI_PARAMS[4];

        $offering = $this->offeringDao->getOfferingByCourse($course, $block);
        // get the deliverable and the deliveries
        $deliverables = $this->deliverableDao->forLab($lab_id);
        $deliveries = $this->deliveryDao->forDeliverable($deliv_id);

        $next_id = null;
        $prev_id = null;
        $deliverable = null;
        for ($i = 0; $i < count($deliverables); $i++) {
            if ($deliverables[$i]['id'] == $deliv_id) {
                $deliverable = $deliverables[$i];
                if ($i > 0) {
                    $prev_id = $deliverables[$i - 1]['id'];
                }
                if ($i < count($deliverables) - 1) {
                    $next_id = $deliverables[$i + 1]['id'];
                }
                break;
            }
        }
        $did = $deliverable['id'];

        require_once("lib/Parsedown.php");
        $parsedown = new Parsedown();
        $parsedown->setSafeMode(true);
        $VIEW_DATA["parsedown"] = $parsedown;
        $VIEW_DATA['course'] = $course;
        $VIEW_DATA['block'] = $block;
        $VIEW_DATA['offering'] = $offering;
        $VIEW_DATA['title'] = "Grade Deliverable";
        $VIEW_DATA['deliv'] = $deliverable;
        $VIEW_DATA['deliverables'] = $deliverables;
        $VIEW_DATA['deliveries'] = $deliveries;
        $VIEW_DATA['attachments'] = $this->attachmentDao->forDeliverable($did);
        $VIEW_DATA['prev_id'] = $prev_id;
        $VIEW_DATA['next_id'] = $next_id;

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
        $idx = filter_input(INPUT_GET, "idx", FILTER_VALIDATE_INT);

        $offering = $this->offeringDao->getOfferingByCourse($course, $block);
        $deliverables = $this->deliverableDao->forLab($lab_id);
        $submission = $this->submissionDao->byId($submission_id);
        $deliveries = $this->deliveryDao->forSubmission($submission_id);
        $ids = array_column($this->submissionDao->idsForLab($lab_id), 'id');
        $attachments = $this->attachmentDao->forLab($lab_id);

        if ($idx === null) {
            $idx = array_search($submission_id, $ids);
        }

        $members = [];
        if ($submission['group']) {
            $members = $this->enrollmentDao->getMembers($offering['id'], $submission['group']);
        } else {
            $members[] = $this->userDao->retrieve($submission['user_id']);
        }

        require_once("lib/Parsedown.php");
        $parsedown = new Parsedown();
        $parsedown->setSafeMode(true);
        $VIEW_DATA["parsedown"] = $parsedown;
        $VIEW_DATA['course'] = $course;
        $VIEW_DATA['block'] = $block;
        $VIEW_DATA['offering'] = $offering;
        $VIEW_DATA['title'] = "Grade Submission";
        $VIEW_DATA['lab_id'] = $lab_id;
        $VIEW_DATA['members'] = $members;
        $VIEW_DATA['submission'] = $submission;
        $VIEW_DATA['ids'] = $ids;
        $VIEW_DATA['idx'] = $idx;
        $VIEW_DATA['deliverables'] = $deliverables;
        $VIEW_DATA['deliveries'] = $deliveries;
        $VIEW_DATA['attachments'] = $attachments;

        return "lab/gradeSubmission.php";
    }

    #[Get(uri: "/(\d+)/user/(\d+)$", sec: "assistant")]
    public function gradeUser() {
        global $URI_PARAMS;

        $lab_id = $URI_PARAMS[3];
        $user_id = $URI_PARAMS[4];
        $sid = $this->submissionDao->getOrCreate($lab_id, $user_id, null);

        return "Location: ../submission/$sid";
    }

    #[Get(uri: "/(\d+)/group/(.+)$", sec: "assistant")]
    public function gradeGroup() {
        global $URI_PARAMS;

        $lab_id = $URI_PARAMS[3];
        $group = $URI_PARAMS[4];
        $user_id = $_SESSION['user']['id'];
        $sid = $this->submissionDao->getOrCreate($lab_id, $user_id, $group);

        return "Location: ../submission/$sid";
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
            $submission_id = $this->submissionDao->getOrCreate(
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
