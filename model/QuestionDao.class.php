<?php
/**
 * Question DAO Class
 *
 * @author mzijlstra 09/24/2021
 * @Repository
 * 
 */
class QuestionDao {
    /**
	 * @var PDO PDO database connection object
	 * @Inject("DB")
	 */
	public $db;

    public function getAllFor($video) {
        $stmt = $this->db->prepare(
			"SELECT q.id, q.question, q.user_id, u.firstname, u.lastname, q.created, q.edited 
            FROM question q JOIN user u ON q.user_id = u.id
            WHERE video = :video"
		);
		$stmt->execute(array("video" =>  $video));
		return $stmt->fetchAll();
    }

    public function get($id) {
        $stmt = $this->db->prepare(
			"SELECT *
            FROM question 
            WHERE id = :id"
		);
		$stmt->execute(array("id" =>  $id));
		return $stmt->fetch();
    }

	public function add($question, $user_id, $video) {
		$stmt = $this->db->prepare("INSERT INTO question 
			VALUES(NULL, :question, :user_id, :video, NOW(), NULL)");
		$stmt->execute(array("question" => $question, "user_id" => $user_id, 
			"video" => $video));
		return $this->db->lastInsertId();
	}

    public function del($id) {
        $stmt = $this->db->prepare(
			"DELETE
            FROM question 
            WHERE id = :id"
		);
		$stmt->execute(array("id" =>  $id));
    }

    public function update($id, $text) {
        $stmt = $this->db->prepare(
			"UPDATE question 
            SET question = :question, edited = NOW()
            WHERE id = :id"
		);
		$stmt->execute(array("id" =>  $id, "question" => $text));

    }
}
