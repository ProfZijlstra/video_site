<?php

/**
 * ReplyVote DAO Class
 *
 * @author mzijlstra 09/26/2021
 */

#[Repository]
class ReplyVoteDao
{
    #[Inject('DB')]
    public $db;

    public function add($reply_id, $user_id, $vote)
    {
        $stmt = $this->db->prepare("INSERT INTO reply_vote
			VALUES(NULL, :reply_id, :user_id, :vote)");
        $stmt->execute(array("reply_id" => $reply_id, "user_id" => $user_id, "vote" => $vote));
        return $this->db->lastInsertId();
    }

    public function update($id, $user_id, $vote)
    {
        $stmt = $this->db->prepare(
            "UPDATE reply_vote
            SET vote = :vote
            WHERE id = :id AND user_id = :user_id"
        );
        $stmt->execute(array("id" => $id, "vote" => $vote, "user_id" => $user_id));
    }
}
