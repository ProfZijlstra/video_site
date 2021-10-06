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
	 * @returns offering record
	 */
	public function getOfferingById($id) {
		$stmt = $this->db->prepare("SELECT * FROM offering
			WHERE id = :id");
		$stmt->execute(array("id" => $id));
		return $stmt->fetch();
	}

	public function getLatest() {
		$stmt = $this->db->prepare(
			"SELECT * 
			FROM offering AS o
			JOIN course AS c ON o.course_number = c.number
			ORDER BY o.block DESC
			LIMIT 1
			");
		$stmt->execute(array("id" => $id));
		return $stmt->fetch();
	}
}
