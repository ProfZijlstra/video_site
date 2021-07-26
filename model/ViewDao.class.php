<?php
/**
 * View DAO Class
 *
 * @author mzijlstra 06/04/2021
 * @Repository
 */
class ViewDao {

	/**
	 * @var PDO PDO database connection object
	 * @Inject("DB")
	 */
	public $db;

	/**
	 * Creates a new view in the database based on given values
	 * @param int $user_id 
	 * @param int $day_id
	 * @param string video file name
	 * @return int id of created view
	 */
	public function start($user_id, $day_id, $video) {
		$stmt = $this->db->prepare("INSERT INTO view 
			VALUES(NULL, :user_id, :day_id, :video, NOW(), NULL)");
		$stmt->execute(array("user_id" => $user_id, 
			"day_id" => $day_id, "video" => $video));
		return $this->db->lastInsertId();
	}

	/**
	 * Sets the stop timestamp for a view
	 * @param int $view_id
	 * @returns void
	 */
	public function stop($id) {
		$stmt = $this->db->prepare("UPDATE view SET `stop` = NOW() 
			WHERE id = :id");
		return $stmt->execute(array("id" => $id));
	}
}

