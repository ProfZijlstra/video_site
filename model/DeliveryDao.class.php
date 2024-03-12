<?php

/**
 * @author mzijlstra 14 Jan 2024
 */

#[Repository]
class DeliveryDao
{
    #[Inject('DB')]
    public $db;

    public function forSubmission($submission_id)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM delivery 
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
            "SELECT * FROM delivery 
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
            "INSERT INTO delivery VALUES (
                NULL, :deliverable_id, :submission_id, :user_id,
                NOW(), NOW(), 
                :completion, :duration, 
                :text, :hasMarkDown, 
                NULL, NULL, 
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
            "UPDATE delivery 
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

    public function createUrl(
        $submission_id,
        $deliverable_id,
        $user_id,
        $completion,
        $duration,
        $url,
        $stuComment,
        $stuCmntHasMD
    ) {
        $stmt = $this->db->prepare(
            "INSERT INTO delivery VALUES (
                NULL, :deliverable_id, :submission_id, :user_id,
                NOW(), NOW(), 
                :completion, :duration, 
                :url, NULL, 
                NULL, NULL, 
                :stuComment, :stuCmntHasMD, 
                NULL, NULL, NULL)"
        );
        $stmt->execute([
            "submission_id" => $submission_id,
            "deliverable_id" => $deliverable_id,
            "user_id" => $user_id,
            "duration" => $duration,
            "completion" => $completion,
            "url" => $url,
            "stuComment" => $stuComment,
            "stuCmntHasMD" => $stuCmntHasMD
        ]);
        return $this->db->lastInsertId();
    }

    public function updateUrl(
        $id,
        $user_id,
        $completion,
        $duration,
        $url,
        $stuComment,
        $stuCmntHasMD
    ) {
        $stmt = $this->db->prepare(
            "UPDATE delivery 
                SET updated = NOW(),
                user_id = :user_id,
                completion = :completion, 
                duration = :duration, 
                text = :url, 
                stuComment = :stuComment,
                stuCmntHasMD = :stuCmntHasMD
                WHERE id = :id"
        );
        $stmt->execute([
            "user_id" => $user_id,
            "completion" => $completion,
            "duration" => $duration,
            "url" => $url,
            "id" => $id,
            "stuComment" => $stuComment,
            "stuCmntHasMD" => $stuCmntHasMD
        ]);
    }

    public function createFile(
        $submission_id,
        $deliverable_id,
        $user_id,
        $completion,
        $duration,
        $file,
        $name,
        $stuComment,
        $stuCmntHasMD
    ) {
        $stmt = $this->db->prepare(
            "INSERT INTO delivery VALUES (
                NULL, :deliverable_id, :submission_id, :user_id,
                NOW(), NOW(), 
                :completion, :duration, 
                NULL, NULL, 
                :file, :name, 
                :stuComment, :stuCmntHasMD, 
                NULL, NULL, NULL)"
        );
        $stmt->execute([
            "submission_id" => $submission_id,
            "deliverable_id" => $deliverable_id,
            "user_id" => $user_id,
            "duration" => $duration,
            "completion" => $completion,
            "file" => $file,
            "name" => $name,
            "stuComment" => $stuComment,
            "stuCmntHasMD" => $stuCmntHasMD
        ]);
        return $this->db->lastInsertId();
    }

    public function updateFileStats(
        $id,
        $completion,
        $duration,
        $stuComment,
        $stuCmntHasMD
    ) {
        $stmt = $this->db->prepare(
            "UPDATE delivery 
                SET updated = NOW(),
                completion = :completion, 
                duration = :duration, 
                stuComment = :stuComment,
                stuCmntHasMD = :stuCmntHasMD
                WHERE id = :id"
        );
        $stmt->execute([
            "completion" => $completion,
            "duration" => $duration,
            "id" => $id,
            "stuComment" => $stuComment,
            "stuCmntHasMD" => $stuCmntHasMD
        ]);
    }

    public function updateFile(
        $id,
        $user_id,
        $completion,
        $duration,
        $file,
        $name,
        $stuComment,
        $stuCmntHasMD
    ) {
        $stmt = $this->db->prepare(
            "UPDATE delivery 
                SET updated = NOW(),
                user_id = :user_id,
                completion = :completion, 
                duration = :duration, 
                `file` = :file, 
                `name` = :name,
                stuComment = :stuComment,
                stuCmntHasMD = :stuCmntHasMD
                WHERE id = :id"
        );
        $stmt->execute([
            "user_id" => $user_id,
            "completion" => $completion,
            "duration" => $duration,
            "file" => $file,
            "name" => $name,
            "id" => $id,
            "stuComment" => $stuComment,
            "stuCmntHasMD" => $stuCmntHasMD
        ]);
    }
}
