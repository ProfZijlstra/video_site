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

    public function getAllFor($video, $user_id) {
        $stmt = $this->db->prepare(
			"SELECT q.id, q.question, q.user_id, q.created, q.edited, 
			u.firstname, u.lastname, v.id AS vote_id, v.vote AS vote, 
			SUM(v2.vote) AS vote_total
            FROM question q 
			JOIN user u ON q.user_id = u.id
			LEFT JOIN question_vote v ON q.id = v.question_id AND v.user_id = :user_id 
			LEFT JOIN question_vote v2 ON q.id = v2.question_id
            WHERE q.video = :video
			GROUP BY q.id
			ORDER BY vote_total DESC"
		);
		$stmt->execute(array("video" =>  $video, "user_id" => $user_id));
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
            FROM question_vote 
            WHERE question_id = :id"
		);
		$stmt->execute(array("id" =>  $id));
		$stmt = $this->db->prepare(
			"DELETE
            FROM reply 
            WHERE question_id = :id"
		);
		$stmt->execute(array("id" =>  $id));
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
