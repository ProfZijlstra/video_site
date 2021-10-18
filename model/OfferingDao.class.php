<?php

/**
 * Offering Dao Class
 *
 * @author mzijlstra 06/06/2021
 * @Repository
 */
class OfferingDao {
	/**
	 * @var PDO PDO database connection object
	 * @Inject("DB")
	 */
	public $db;

	/**
	 * Gets Offering based on course number string and block string
	 * @param string $course_number like "cs472"
	 * @param string $block like "2021-07"
	 * @return array of offering data
	 */
	public function getOfferingByCourse($course_number, $block) {
		$stmt = $this->db->prepare("SELECT * FROM offering
			WHERE course_number = :course_number AND block = :block");
		$stmt->execute(array("course_number" => $course_number, "block" => $block));
		return $stmt->fetch();
	}

	/**
	 * Gets Offering based on id
	 * @param int id of offering
	 * @return offering record
	 */
	public function getOfferingById($id) {
		$stmt = $this->db->prepare("SELECT * FROM offering
			WHERE id = :id");
		$stmt->execute(array("id" => $id));
		return $stmt->fetch();
	}

	/**
	 * Gets the single latest offering
	 * @return offering record
	 */
	public function getLatest() {
		$stmt = $this->db->prepare(
			"SELECT * 
			FROM offering AS o
			JOIN course AS c ON o.course_number = c.number
			ORDER BY o.block DESC
			LIMIT 1
			");
		$stmt->execute();
		return $stmt->fetch();
	}

	/**
	 * Gets the latest offering for a specific course
	 * @param $course_num string like "cs472"
	 * @return offering record
	 */
	public function getLatestForcourse($course_num) {
		$stmt = $this->db->prepare(
			"SELECT * 
			FROM offering AS o
			JOIN course AS c ON o.course_number = c.number
			WHERE o.course_number = :course_number
			ORDER BY o.block DESC
			LIMIT 1
			");
		$stmt->execute(array("course_number" => $course_num));
		return $stmt->fetch();
	}

	/**
	 * Gets all offerings in the database
	 * @return array of offering records
	 */
	public function all() {
		$stmt = $this->db->prepare("SELECT * FROM offering");
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public function allForCourse($course_num) {
		$stmt = $this->db->prepare("SELECT * FROM offering 
		WHERE course_number = :course_num ORDER BY `block`");
		$stmt->execute(["course_num" => $course_num]);
		return $stmt->fetchAll();
	}

	/**
	 * Gets the latest offering for each course in the db
	 * @return array of offering records
	 */
	public function allLatest() {
		$stmt = $this->db->prepare(
			"SELECT MAX(id) AS id, course_number, MAX(`block`) AS `block`, 
			MAX(`start`) AS start, MAX(`stop`) as `stop` 
			FROM offering
			GROUP BY course_number
		");
		$stmt->execute();
		return $stmt->fetchAll();
	}

	/**
	 * Creates an offering in the DB
	 */
	public function create($course_number, $block, $start, $stop) {
		$stmt = $this->db->prepare(
			"INSERT INTO offering 
			VALUES(NULL, :course_number, :block, :start, :stop)"
		);
		$stmt->execute(array("course_number" => $course_number, "block" => $block, 
			"start" => $start, "stop" => $stop));
		return $this->db->lastInsertId();
	}
}
