<?php

/**
 * Comment DAO Class
 *
 * @author mzijlstra 09/24/2021
 */
#[Repository]
class CommentDao
{
    #[Inject('DB')]
    public $db;

    public function getAllForDay($day_id, $user_id)
    {
        $stmt = $this->db->prepare(
            'SELECT q.id, q.text, q.user_id, q.created, q.edited, q.vid_pdf,
                        u.knownAs, u.lastname, v.id AS vote_id, v.vote AS vote, 
                        SUM(v2.vote) AS vote_total
            FROM comment q 
                        JOIN user u ON q.user_id = u.id
                        LEFT JOIN comment_vote v ON q.id = v.comment_id AND v.user_id = :user_id 
                        LEFT JOIN comment_vote v2 ON q.id = v2.comment_id
            WHERE q.day_id = :day_id
                        GROUP BY q.id
                        ORDER BY vote_total DESC'
        );
        $stmt->execute(['day_id' => $day_id, 'user_id' => $user_id]);
        $result = $stmt->fetchAll();
        $out = [];
        foreach ($result as $row) {
            if (! isset($out[$row['vid_pdf']])) {
                $out[$row['vid_pdf']] = [];
            }
            $out[$row['vid_pdf']][] = $row;
        }

        return $out;
    }

    public function get($id)
    {
        $stmt = $this->db->prepare(
            'SELECT *
            FROM comment 
            WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);

        return $stmt->fetch();
    }

    public function getUserEmail($id)
    {
        $stmt = $this->db->prepare(
            'SELECT u.teamsName, u.email
            FROM comment AS q 
                        JOIN user AS u ON q.user_id = u.id
            WHERE q.id = :id'
        );
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();

        return [$result['email'], $result['teamsName']];
    }

    public function add($comment, $user_id, $day_id, $vid_pdf)
    {
        $stmt = $this->db->prepare('INSERT INTO comment
                        VALUES(NULL, :comment, :user_id, :day_id, :vid_pdf, NOW(), NULL)');
        $stmt->execute([
            'comment' => $comment, 'user_id' => $user_id, 'day_id' => $day_id,
            'vid_pdf' => $vid_pdf,
        ]);

        return $this->db->lastInsertId();
    }

    public function del($id)
    {
        $stmt = $this->db->prepare(
            'DELETE
            FROM comment_vote 
            WHERE comment_id = :id'
        );
        $stmt->execute(['id' => $id]);
        // deleting the reply votes related to this comment takes more work
        $stmt = $this->db->prepare('SELECT id FROM reply WHERE comment_id = :qid');
        $stmt->execute(['qid' => $id]);
        $rids_data = $stmt->fetchAll();
        if ($rids_data) {
            $rids = [];
            foreach ($rids_data as $row) {
                $rids[] = $row['id'];
            }
            $inject = implode(',', $rids);
            $stmt = $this->db->prepare(
                "DELETE
                                FROM reply_vote 
                                WHERE reply_id IN ({$inject})"
            );
            $stmt->execute();
        }

        $stmt = $this->db->prepare(
            'DELETE
            FROM reply 
            WHERE comment_id = :id'
        );
        $stmt->execute(['id' => $id]);
        $stmt = $this->db->prepare(
            'DELETE
            FROM comment 
            WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
    }

    public function update($id, $text)
    {
        $stmt = $this->db->prepare(
            'UPDATE comment
            SET `text` = :comment, edited = NOW()
            WHERE id = :id'
        );
        $stmt->execute(['id' => $id, 'comment' => $text]);
    }

    /**
     * DANGER!
     * This clone method also updates the vote, reply and reply_vote tables!
     */
    public function clone($offering_id, $new_offering_id)
    {
        $commentStmt = $this->db->prepare(
            'SELECT c.id, c.text, c.user_id, c.day_id, c.vid_pdf, c.created,
                c.edited, d.abbr
            FROM comment c
            JOIN day d ON c.day_id = d.id
            WHERE d.offering_id = :offering_id
            ORDER BY c.day_id'
        );
        $commentStmt->execute(['offering_id' => $offering_id]);
        $commentResult = $commentStmt->fetchAll();

        $dayStmt = $this->db->prepare(
            'SELECT id FROM day
            WHERE offering_id = :offering_id
            AND abbr = :abbr'
        );
        $cloneCommentStmt = $this->db->prepare(
            'INSERT INTO comment
            VALUES(NULL, :comment, :user_id, :day_id, :vid_pdf, :created, :edited)');
        $getVoteStmt = $this->db->prepare(
            'SELECT * FROM comment_vote
            WHERE comment_id = :comment_id'
        );
        $cloneVoteStmt = $this->db->prepare(
            'INSERT INTO comment_vote
            VALUES(NULL, :comment_id, :user_id, :vote)'
        );
        $getReplyStmt = $this->db->prepare(
            'SELECT * from reply
            WHERE comment_id = :comment_id'
        );
        $cloneReplyStmt = $this->db->prepare(
            'INSERT INTO reply
            VALUES(NULL, :text, :user_id, :comment_id, :created, :edited)'
        );
        $getRVoteStmt = $this->db->prepare(
            'SELECT * from reply_vote
            WHERE reply_id = :reply_id'
        );
        $cloneRVoteStmt = $this->db->prepare(
            'INSERT INTO reply_vote
            VALUE(NULL, :reply_id, :user_id, :vote)'
        );

        foreach ($commentResult as $comment) {
            $dayStmt->execute(
                [
                    'offering_id' => $new_offering_id,
                    'abbr' => $comment['abbr'],
                ]
            );
            $dayResult = $dayStmt->fetch();
            $new_day_id = $dayResult['id'];

            // clone comment
            $cloneCommentStmt->execute(
                [
                    'comment' => $comment['text'],
                    'user_id' => $comment['user_id'],
                    'day_id' => $new_day_id,
                    'vid_pdf' => $comment['vid_pdf'],
                    'created' => $comment['created'],
                    'edited' => $comment['edited'],
                ]
            );
            $new_comment_id = $this->db->lastInsertId();

            // clone all votes
            $getVoteStmt->execute(['comment_id' => $comment['id']]);
            $votes = $getVoteStmt->fetchAll();
            foreach ($votes as $vote) {
                $cloneVoteStmt->execute(
                    [
                        'comment_id' => $new_comment_id,
                        'user_id' => $vote['user_id'],
                        'vote' => $vote['vote'],
                    ]
                );
            }

            // clone all replies
            $getReplyStmt->execute(['comment_id' => $comment['id']]);
            $replies = $getReplyStmt->fetchAll();
            foreach ($replies as $reply) {
                $cloneReplyStmt->execute(
                    [
                        'text' => $reply['text'],
                        'user_id' => $reply['user_id'],
                        'comment_id' => $new_comment_id,
                        'created' => $reply['created'],
                        'edited' => $reply['edited'],
                    ]
                );
                $new_reply_id = $this->db->lastInsertId();

                // clone all reply votes
                $getRVoteStmt->execute(['reply_id' => $reply['id']]);
                $rvotes = $getRVoteStmt->fetchAll();
                foreach ($rvotes as $rvote) {
                    $cloneRVoteStmt->execute(
                        [
                            'reply_id' => $new_reply_id,
                            'user_id' => $rvote['user_id'],
                            'vote' => $rvote['vote'],
                        ]
                    );
                } // end foreach rvote
            } // end foreach reply
        } // end foreach comment
    }
}
