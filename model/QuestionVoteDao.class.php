<?php
/**
 * QuestionVote DAO Class
 *
 * @author mzijlstra 09/25/2021
 * @Repository
 * 
 */
class QuestionVoteDao {
    /**
	 * @var PDO PDO database connection object
	 * @Inject("DB")
	 */
	public $db;

    public function add($question_id, $user_id, $vote) {
		$stmt = $this->db->prepare("INSERT INTO question_vote 
			VALUES(NULL, :question_id, :user_id, :vote)");
		$stmt->execute(array("question_id" => $question_id, "user_id" => $user_id, 
			"vote" => $vote));
		return $this->db->lastInsertId();
	}

    public function update($id, $user_id, $vote) {
        $stmt = $this->db->prepare(
			"UPDATE question_vote 
            SET vote = :vote
            WHERE id = :id AND user_id = :user_id"
		);
		$stmt->execute(array("id" =>  $id, "vote" => $vote, "user_id" => $user_id));
    }
}
