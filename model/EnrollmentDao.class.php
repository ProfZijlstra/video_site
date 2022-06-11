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
		$stmt = $this->db->prepare("SELECT u.id, u.knownAs, u.studentID, 
			u.firstname, u.lastname, u.email, u.teamsName
            FROM enrollment e JOIN user u ON e.user_id = u.id 
            WHERE offering_id = :offering_id
			ORDER BY u.firstname");
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

	public function deleteEnrollment($offering_id) {
		$stmt = $this->db->prepare(
			"DELETE FROM enrollment 
				WHERE offering_id = :offering_id");
		$stmt->execute(["offering_id" => $offering_id]);
	}

	public function enroll($user_id, $offering_id) {
		$stmt = $this->db->prepare("INSERT INTO enrollment 
				VALUES(NULL, :user_id, :offering_id)");
		$stmt->execute(["user_id" => $user_id, "offering_id" => $offering_id]);
	}

	public function unenroll($user_id, $offering_id) {
		$stmt = $this->db->prepare("DELETE FROM enrollment 
				WHERE user_id = :user_id 
				AND offering_id = :offering_id");
		$stmt->execute(["user_id" => $user_id, "offering_id" => $offering_id]);		
	}
}


