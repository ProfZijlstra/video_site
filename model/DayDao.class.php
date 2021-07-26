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
}

