<?php

/**
 * Lab Controller Class
 * @author mzijlstra 01/08/2024
 */

#[Controller(path: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/lab")]
class LabAdminCtrl
{
    #[Inject('OverviewHlpr')]
    public $overviewHlpr;

    #[Inject('LabDao')]
    public $labDao;

    #[Inject('DeliverableDao')]
    public $deliverableDao;

    #[Inject('OfferingDao')]
    public $offeringDao;

    #[Inject('DayDao')]
    public $dayDao;

    #[Get(uri: "$", sec: "observer")]
    public function courseOverview()
    {
        // We're building on top of  overview -- run it first
        // this populates $VIEW_DATA with the overview related data
        $this->overviewHlpr->overview();

        global $VIEW_DATA;

        // get all labs for this offering
        $oid = $VIEW_DATA["offering_id"];
        if (
            $_SESSION['user']['isAdmin'] ||
            $_SESSION['user']['isFaculty']
        ) {
            $labs = $this->labDao->allForOffering($oid);
        } else {
            $labs = $this->labDao->visibleForOffering($oid);
        }

        // integrate the labs data into the days data
        foreach ($VIEW_DATA['days'] as $day) {
            $day['labs'] = array();
        }
        foreach ($labs as $lab) {
            $VIEW_DATA['days'][$lab['abbr']]['labs'][] = $lab;
        }

        $VIEW_DATA['title'] = 'Labs';
        $VIEW_DATA["isRemembered"] = $_SESSION['user']['isRemembered'];
        return "lab/overview.php";
    }

    #[Post(uri: "$", sec: "instructor")]
    public function addLab()
    {
        $day_id = filter_input(INPUT_POST, "day_id", FILTER_SANITIZE_NUMBER_INT);
        $name = filter_input(INPUT_POST, "name");
        $startdate = filter_input(INPUT_POST, "startdate");
        $stopdate = filter_input(INPUT_POST, "stopdate");
        $starttime = filter_input(INPUT_POST, "starttime");
        $stoptime = filter_input(INPUT_POST, "stoptime");

        $start = "{$startdate} {$starttime}";
        $stop = "{$stopdate} {$stoptime}";
        $id = $this->labDao->add($name, $day_id, $start, $stop);

        return "Location: lab/{$id}/edit"; // edit lab view
    }

    #[Get(uri: "/(\d+)/edit$", sec: "instructor")]
    public function editLab()
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $course_num = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $lab_id = $URI_PARAMS[3];

        $offering = $this->offeringDao->getOfferingByCourse($course_num, $block);
        $days = $this->dayDao->getDays($offering['id']);

        $VIEW_DATA['days'] = $days;
        $VIEW_DATA['course'] = $course_num;
        $VIEW_DATA['block'] = $block;
        $VIEW_DATA['lab'] = $this->labDao->byId($lab_id);
        $VIEW_DATA['deliverables'] = $this->deliverableDao->forLab($lab_id);
        $VIEW_DATA['title'] = "Edit Lab";

        return "lab/edit.php";
    }

    /**
     * Expects AJAX
     */
    #[Post(uri: "/(\d+)$", sec: "instructor")]
    public function updateLab()
    {
        global $URI_PARAMS;

        $id = $URI_PARAMS[3];
        $visible = filter_input(INPUT_POST, "visible", FILTER_SANITIZE_NUMBER_INT);
        $name = filter_input(INPUT_POST, "name");
        $day_id = filter_input(INPUT_POST, "day_id", FILTER_SANITIZE_NUMBER_INT);
        $startdate = filter_input(INPUT_POST, "startdate");
        $stopdate = filter_input(INPUT_POST, "stopdate");
        $starttime = filter_input(INPUT_POST, "starttime");
        $stoptime = filter_input(INPUT_POST, "stoptime");
        $points = filter_input(INPUT_POST, "points", FILTER_SANITIZE_NUMBER_INT);
        $type = filter_input(INPUT_POST, "type");
        $hasMarkDown = filter_input(INPUT_POST, "hasMarkDown", FILTER_SANITIZE_NUMBER_INT);
        $desc = filter_input(INPUT_POST, "desc");


        $visible = $visible ? 1 : 0;
        $hasMarkDown = $hasMarkDown ? 1 : 0;
        $start = "{$startdate} {$starttime}";
        $stop = "{$stopdate} {$stoptime}";

        $this->labDao->update($id, $visible, $name, $day_id, $start, $stop, $points, $type, $hasMarkDown, $desc);
    }

    #[Post(uri: "/(\d+)/del$", sec: "instructor")]
    public function deleteLab()
    {
        global $URI_PARAMS;
        $id = $URI_PARAMS[3];
        // TODO fail if lab has submissions 
        // TODO delete deliverables
        // TODO delete attachments
        $this->labDao->delete($id);
        return "Location: ../../lab";
    }
}
