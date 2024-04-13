<?php

/**
 * @author mzijlstra 14 Jan 2024
 */

#[Repository]
class SubmissionDao
{
    #[Inject('DB')]
    public $db;

    public function create($lab_id, $user_id, $group)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO submission 
                VALUES (NULL, :lab_id, :user_id, :group)"
        );
        $stmt->execute([
            "user_id" => $user_id,
            "lab_id" => $lab_id,
            "group" => $group
        ]);
        return $this->db->lastInsertId();
    }

    public function forUser($user_id, $lab_id)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM submission 
                WHERE user_id = :user_id 
                AND lab_id = :lab_id"
        );
        $stmt->execute([
            "user_id" => $user_id,
            "lab_id" => $lab_id
        ]);
        return $stmt->fetch();
    }

    public function forGroup($group, $lab_id)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM submission 
                WHERE `group` = :group 
                AND lab_id = :lab_id"
        );
        $stmt->execute([
            "group" => $group,
            "lab_id" => $lab_id
        ]);
        return $stmt->fetch();
    }

    public function forLab($lab_id)
    {
        $stmt = $this->db->prepare(
            "SELECT s.id, s.lab_id, s.user_id, s.group,
                SUM(d.points) AS points, COUNT(d.id) AS delivs,
                MIN(d.created) AS start, MAX(d.updated) AS stop
                FROM submission AS s
                JOIN delivery AS d ON s.id = d.submission_id
                WHERE s.lab_id = :lab_id
                GROUP BY s.id"
        );
        $stmt->execute(["lab_id" => $lab_id]);
        return $stmt->fetchAll();
    }

    public function idsForLab($lab_id)
    {
        $stmt = $this->db->prepare(
            "SELECT id FROM submission 
                WHERE lab_id = :lab_id"
        );
        $stmt->execute(["lab_id" => $lab_id]);
        return $stmt->fetchAll();
    }

    public function byId($id)
    {
        $stmt = $this->db->prepare(
            "SELECT s.id, s.lab_id, s.user_id, s.group,
                MIN(d.created) AS start, MAX(d.updated) AS stop
                FROM submission AS s
                JOIN delivery AS d ON s.id = d.submission_id
                WHERE s.id = :id"
        );
        $stmt->execute(["id" => $id]);
        return $stmt->fetch();
    }
}
