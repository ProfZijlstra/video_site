<?php
/**
 * Comment DAO Class
 *
 * @author mzijlstra 09/24/2021
 * @Repository
 * 
 */
class CommentDao {
    /**
	 * @var PDO PDO database connection object
	 * @Inject("DB")
	 */
	public $db;

    public function getAllFor($video, $user_id) {
        $stmt = $this->db->prepare(
			"SELECT q.id, q.text, q.user_id, q.created, q.edited, 
			u.knownAs, u.lastname, v.id AS vote_id, v.vote AS vote, 
			SUM(v2.vote) AS vote_total
            FROM comment q 
			JOIN user u ON q.user_id = u.id
			LEFT JOIN comment_vote v ON q.id = v.comment_id AND v.user_id = :user_id 
			LEFT JOIN comment_vote v2 ON q.id = v2.comment_id
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
            FROM comment 
            WHERE id = :id"
		);
		$stmt->execute(array("id" =>  $id));
		return $stmt->fetch();
    }

	public function getUserEmail($id) {
		$stmt = $this->db->prepare(
			"SELECT u.email
            FROM comment AS q 
			JOIN user AS u ON q.user_id = u.id
            WHERE q.id = :id"
		);
		$stmt->execute(array("id" =>  $id));
		$result = $stmt->fetch();
		return $result["email"];
	}

	public function add($comment, $user_id, $video) {
		$stmt = $this->db->prepare("INSERT INTO comment 
			VALUES(NULL, :comment, :user_id, :video, NOW(), NULL)");
		$stmt->execute(array("comment" => $comment, "user_id" => $user_id, 
			"video" => $video));
		return $this->db->lastInsertId();
	}

    public function del($id) {
		$stmt = $this->db->prepare(
			"DELETE
            FROM comment_vote 
            WHERE comment_id = :id"
		);
		$stmt->execute(array("id" =>  $id));
		// deleting the reply votes related to this comment takes more work 
		$stmt = $this->db->prepare("SELECT id FROM reply WHERE comment_id = :qid");
		$stmt->execute(array("qid" => $id));
		$rids_data = $stmt->fetchAll();
		if ($rids_data) {
			$rids = array();
			foreach ($rids_data as $row) {
				$rids[] = $row['id'];
			}
			$inject = implode(",", $rids);
			$stmt = $this->db->prepare(
				"DELETE
				FROM reply_vote 
				WHERE reply_id IN (${inject})"
			);
			$stmt->execute();	
		}

		$stmt = $this->db->prepare(
			"DELETE
            FROM reply 
            WHERE comment_id = :id"
		);
		$stmt->execute(array("id" =>  $id));
        $stmt = $this->db->prepare(
			"DELETE
            FROM comment 
            WHERE id = :id"
		);
		$stmt->execute(array("id" =>  $id));
    }

    public function update($id, $text) {
        $stmt = $this->db->prepare(
			"UPDATE comment 
            SET `text` = :comment, edited = NOW()
            WHERE id = :id"
		);
		$stmt->execute(array("id" =>  $id, "comment" => $text));
    }
}
