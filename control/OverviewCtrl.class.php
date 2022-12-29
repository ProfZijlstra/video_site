<?php

/**
 * OverviewCtr class -- wanted it to be a trait or super class, but that doesn't
 * play nice with the annotations system
 * 
 * @author mzijlstra 12/27/2022
 * 
 * @Controller
 */
class OverviewCtrl {
	/**
	 * @Inject("OfferingDao")
	 */
	public $offeringDao;
	/**
	 * @Inject("DayDao")
	 */
	public $dayDao;


    public function overview() {
        global $URI_PARAMS;
		global $VIEW_DATA;

		$course_num = $URI_PARAMS[1];
		$block = $URI_PARAMS[2];

		$offering_detail = $this->offeringDao->getOfferingByCourse($course_num, $block);
		$days_info = $this->dayDao->getDays($offering_detail['id']);

		// Make days associative array for calendar
		$days = array();
		foreach ($days_info as $day) {
			$days[$day["abbr"]] = $day;
		}

		$VIEW_DATA["course"] = strtoupper($course_num);
		$VIEW_DATA["block"] = $offering_detail['block'];
		$VIEW_DATA["offering"] = $offering_detail;
		$VIEW_DATA["offering_id"] = $offering_detail["id"]; // for header.php
		$VIEW_DATA["start"] = strtotime($offering_detail['start']);
		$VIEW_DATA["days"] = $days;
		$VIEW_DATA["now"] = time();
	}

}

?>