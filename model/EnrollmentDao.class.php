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
			u.firstname, u.lastname, u.email, u.teamsName, e.auth
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

	public function getInstructorsForOfferings($offering_ids) {
		$inject = implode(",", $offering_ids);
		$stmt = $this->db->prepare(
			"SELECT e.offering_id, u.knownAs, u.lastname 
			FROM enrollment AS e 
			JOIN user AS u ON e.user_id = u.id
			WHERE e.auth = 'instructor'
			AND e.offering_id IN ({$inject})");
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public function deleteStudentEnrollment($offering_id) {
		$stmt = $this->db->prepare(
			"DELETE FROM enrollment 
				WHERE offering_id = :offering_id
				AND auth = 'student'");
		$stmt->execute(["offering_id" => $offering_id]);
	}

	public function enroll($user_id, $offering_id, $auth) {
		$stmt = $this->db->prepare("INSERT INTO enrollment 
				VALUES(NULL, :user_id, :offering_id, :auth)");
		$stmt->execute([
			"user_id" => $user_id, 
			"offering_id" => $offering_id,
			"auth" => $auth
		]);
	}

	public function unenroll($user_id, $offering_id) {
		$stmt = $this->db->prepare("DELETE FROM enrollment 
				WHERE user_id = :user_id 
				AND offering_id = :offering_id");
		$stmt->execute(["user_id" => $user_id, "offering_id" => $offering_id]);		
	}

	public function update($user_id, $offering_id, $auth) {
		$stmt = $this->db->prepare("UPDATE enrollment 
				SET auth = :auth
				WHERE user_id = :user_id 
				AND offering_id = :offering_id");
		$stmt->execute([
			"auth" => $auth,
			"user_id" => $user_id, 
			"offering_id" => $offering_id
		]);		
	}

	public function checkEnrollmentAuth($user_id, $course, $block) {
		$stmt = $this->db->prepare(
			"SELECT e.auth FROM enrollment AS e 
			JOIN offering AS o ON e.offering_id = o.id
			WHERE e.user_id = :user_id
			AND o.course_number = :course
			AND o.block = :block");
		$stmt->execute(array(
			"user_id" =>  $user_id,
			"course" => $course,
			"block" => $block
		));
		return $stmt->fetch();

	}
}


