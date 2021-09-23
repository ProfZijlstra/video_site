<?php

/**
 * Day Dao Class
 *
 * @author mzijlstra 06/06/2021
 * @Repository
 */
class DayDao {
	/**
	 * @var PDO PDO database connection object
	 * @Inject("DB")
	 */
	public $db;

	/**
	 * Gets days for a given offering
	 * @param int offering_id 
	 * @return array of offering data
	 */
	public function getDays($offering_id) {
		$stmt = $this->db->prepare("SELECT * FROM day
			WHERE offering_id = :offering_id");
		$stmt->execute(array("offering_id" => $offering_id));
		return $stmt->fetchAll();
	}

	public function getDayId($course, $block, $day) {
		$stmt = $this->db->prepare("SELECT d.id 
			from day as d 
			JOIN offering as o on d.offering_id = o.id 
			where o.course_number = :course 
			and o.block = :block 
			and d.abbr = :day ");
		$stmt->execute(
			array("course" => $course, "block" => $block, "day" => $day)
		);
		return $stmt->fetch();		
	}
}

