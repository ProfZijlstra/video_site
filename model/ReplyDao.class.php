<?php
/**
 * Reply DAO Class
 *
 * @author mzijlstra 09/26/2021
 * @Repository
 *
 */
class ReplyDao
{
    /**
     * @var PDO PDO database connection object
     * @Inject("DB")
     */
    public $db;

    public function getAllFor($qids, $user_id)
    {
        // I hate to SQL inject, but just not sure how to cleanly bind many params
        $inject = implode(",", $qids);
        $stmt = $this->db->prepare(
            "SELECT r.id, r.text, r.user_id, r.created, r.edited, r.comment_id,
			u.knownAs, u.lastname, v.id AS vote_id, v.vote AS vote,
			SUM(t.vote) AS vote_total
            FROM reply r
			JOIN user u ON r.user_id = u.id
			LEFT JOIN reply_vote v ON r.id = v.reply_id AND v.user_id = :user_id
			LEFT JOIN reply_vote t ON r.id = t.reply_id
            WHERE r.comment_id IN ({$inject})
			GROUP BY r.id
			ORDER BY vote_total DESC"
        );
        $stmt->execute(array("user_id" => $user_id));
        return $stmt->fetchAll();
    }

    public function get($id) {
        $stmt = $this->db->prepare(
			"SELECT *
            FROM reply 
            WHERE id = :id"
		);
		$stmt->execute(array("id" =>  $id));
		return $stmt->fetch();
    }

    public function add($text, $uid, $cid)
    {
        $stmt = $this->db->prepare("INSERT INTO reply
			VALUES(NULL, :answer, :user_id, :comment_id, NOW(), NULL)");
        $stmt->execute(array("answer" => $text, "user_id" => $uid, "comment_id" => $cid));
        return $this->db->lastInsertId();
    }

    public function del($id)
    {
        $stmt = $this->db->prepare(
            "DELETE
            FROM reply_vote
            WHERE reply_id = :id"
        );
        $stmt->execute(array("id" => $id));
        $stmt = $this->db->prepare(
            "DELETE
            FROM reply
            WHERE id = :id"
        );
        $stmt->execute(array("id" => $id));
    }

    public function update($id, $text) {
        $stmt = $this->db->prepare(
			"UPDATE reply 
            SET `text` = :reply, edited = NOW()
            WHERE id = :id"
		);
		$stmt->execute(array("id" =>  $id, "reply" => $text));
    }
}
