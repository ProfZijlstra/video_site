<?php

/**
 * Lab Controller Class
 * @author mzijlstra 01/08/2024
 */

#[Controller(path: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/lab")]
class LabCtrl
{
    #[Inject('OverviewHlpr')]
    public $overviewHlpr;

    #[Get(uri: "$", sec: "observer")]
    public function courseOverview()
    {
        // We're building on top of  overview -- run it first
        // this populates $VIEW_DATA with the overview related data
        $this->overviewHlpr->overview();

        global $VIEW_DATA;

        // get all quizzes for this offering
        $VIEW_DATA['title'] = 'Labs';
        $VIEW_DATA["isRemembered"] = $_SESSION['user']['isRemembered'];
        return "lab/overview.php";
    }
}
