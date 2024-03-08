<?php

/**
 * @author mzijlstra 14 Jan 2024
 */

#[Repository]
class DeliversDao
{
    #[Inject('DB')]
    public $db;

    public function forSubmission($submission_id)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM delivers 
                WHERE submission_id = :submission_id"
        );
        $stmt->execute([
            "submission_id" => $submission_id
        ]);
        $rows = $stmt->fetchAll();
        $result = [];
        foreach ($rows as $row) {
            $result[$row['deliverable_id']] = $row;
        }
        return $result;
    }

    public function byId($id)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM delivers 
                WHERE id = :id"
        );
        $stmt->execute([
            "id" => $id
        ]);
        return $stmt->fetch();
    }

    public function createTxt(
        $submission_id,
        $deliverable_id,
        $user_id,
        $completion,
        $duration,
        $text,
        $hasMarkDown,
        $stuComment,
        $stuCmntHasMD
    ) {
        $stmt = $this->db->prepare(
            "INSERT INTO delivers VALUES (
                NULL, :deliverable_id, :submission_id, :user_id,
                NOW(), NOW(), 
                :completion, :duration, 
                :text, :hasMarkDown, NULL, 
                :stuComment, :stuCmntHasMD, 
                NULL, NULL, NULL)"
        );
        $stmt->execute([
            "submission_id" => $submission_id,
            "deliverable_id" => $deliverable_id,
            "user_id" => $user_id,
            "duration" => $duration,
            "completion" => $completion,
            "text" => $text,
            "hasMarkDown" => $hasMarkDown,
            "stuComment" => $stuComment,
            "stuCmntHasMD" => $stuCmntHasMD
        ]);
        return $this->db->lastInsertId();
    }

    public function updateTxt(
        $id,
        $user_id,
        $completion,
        $duration,
        $text,
        $hasMarkDown,
        $stuComment,
        $stuCmntHasMD
    ) {
        $stmt = $this->db->prepare(
            "UPDATE delivers 
                SET updated = NOW(),
                user_id = :user_id,
                completion = :completion, 
                duration = :duration, 
                text = :text, 
                hasMarkDown = :hasMarkDown,
                stuComment = :stuComment,
                stuCmntHasMD = :stuCmntHasMD
                WHERE id = :id"
        );
        $stmt->execute([
            "user_id" => $user_id,
            "completion" => $completion,
            "duration" => $duration,
            "text" => $text,
            "hasMarkDown" => $hasMarkDown,
            "id" => $id,
            "stuComment" => $stuComment,
            "stuCmntHasMD" => $stuCmntHasMD
        ]);
    }
}
