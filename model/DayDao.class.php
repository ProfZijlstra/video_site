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
			WHERE offering_id = :offering_id ORDER BY abbr");
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

	public function update($day_id, $desc) {
		$stmt = $this->db->prepare(
			"UPDATE day SET `desc` = :desc WHERE id = :day_id");
		$stmt->execute(array("desc" => $desc, "day_id" => $day_id));
	}

	public function cloneDays($offering_id, $new_offering) {
		// get old days from the DB
		$stmt = $this->db->prepare("SELECT * FROM day
		WHERE offering_id = :offering_id");
		$stmt->execute(array("offering_id" => $offering_id));
		$days = $stmt->fetchAll();

		// clone days
		$stmt = $this->db->prepare(
			"INSERT INTO day
			VALUES(NULL, :offering_id, :abbr, :desc)"
		);
		foreach ($days as $day) {
			$stmt->execute(array("offering_id" => $new_offering, 
				"abbr" => $day["abbr"], "desc" => $day["desc"]));
		}		
	}

	public function get($day_id) {
		$stmt = $this->db->prepare("SELECT *
			FROM day 
			WHERE id = :day_id");
		$stmt->execute(["day_id" => $day_id]);
		return $stmt->fetch();
	}
}

