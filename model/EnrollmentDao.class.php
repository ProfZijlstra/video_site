<?php

/**
 * Enrollment Dao Class
 *
 * @author mzijlstra 06/06/2021
 * @Repository
 */
class EnrollmentDao {
	/**
	 * @var PDO PDO database connection object
	 * @Inject("DB")
	 */
	public $db;

	/**
	 * Gets Enrollment for a given offering
	 * @param int offering_id 
	 * @return array of offering data
	 */
	public function getEnrollmentForOffering($offering_id) {
		$stmt = $this->db->prepare("SELECT u.id, u.firstname, u.lastname 
            FROM enrollment e JOIN user u ON e.user_id = u.id 
            WHERE offering_id = :offering_id");
		$stmt->execute(array("offering_id" => $offering_id));
		return $stmt->fetchAll();
	}

	/**
	 * Returns the latest / last enrollment for a given user
	 * @param int user_id
	 * @return single enrollment record (latest for user)
	 */
	public function getEnrollmentForStudent($user_id) {
		$stmt = $this->db->prepare("SELECT * FROM enrollment 
			WHERE user_id = :user_id ORDER BY id DESC LIMIT 1");
		$stmt->execute(array("user_id" =>  $user_id));
		return $stmt->fetch();
	}
}


