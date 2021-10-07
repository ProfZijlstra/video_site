<?php

/**
 * Course Dao Class
 *
 * @author mzijlstra 06/06/2021
 * @Repository
 */
class CourseDao {
	/**
	 * @var PDO PDO database connection object
	 * @Inject("DB")
	 */
	public $db;

	/**
	 * Gets course based on course number
	 * @param string $number like "cs472"
	 * @return array of course data
	 */
	public function getCourse($number) {
		$stmt = $this->db->prepare("SELECT * FROM course 
			WHERE number = :number");
		$stmt->execute(array("number" => $number));
		return $stmt->fetch();
	}

	public function all() {
		$stmt = $this->db->prepare("SELECT * FROM course ORDER BY `number`");
		$stmt->execute();
		return $stmt->fetchAll();
	}
}
