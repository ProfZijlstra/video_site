<?php

/**
 * CommentVote DAO Class
 *
 * @author mzijlstra 09/25/2021
 */

#[Repository]
class CommentVoteDao
{
    #[Inject('DB')]
    public $db;

    public function add($comment_id, $user_id, $vote)
    {
        $stmt = $this->db->prepare("INSERT INTO comment_vote 
			VALUES(NULL, :comment_id, :user_id, :vote)");
        $stmt->execute(array(
            "comment_id" => $comment_id, "user_id" => $user_id,
            "vote" => $vote
        ));
        return $this->db->lastInsertId();
    }

    public function update($id, $user_id, $vote)
    {
        $stmt = $this->db->prepare(
            "UPDATE comment_vote 
            SET vote = :vote
            WHERE id = :id AND user_id = :user_id"
        );
        $stmt->execute(array("id" =>  $id, "vote" => $vote, "user_id" => $user_id));
    }
}
