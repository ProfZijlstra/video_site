<?php

/**
 * @author mzijlstra 14 Jan 2024
 */

#[Repository]
class SubmissionDao
{
    #[Inject('DB')]
    public $db;

    public function createForUser($user_id, $lab_id)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO submission 
                VALUES (NULL, :user_id, :lab_id, NULL)"
        );
        $stmt->execute([
            "user_id" => $user_id,
            "lab_id" => $lab_id
        ]);
        return $this->db->lastInsertId();
    }

    public function createForGroup($lab_id, $user_id, $group)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO submission 
                VALUES (NULL, :user_id, :lab_id, :group)"
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
            "SELECT * FROM submission 
                WHERE lab_id = :lab_id"
        );
        $stmt->execute(["lab_id" => $lab_id]);
        return $stmt->fetchAll();
    }

    public function byId($id)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM submission 
                WHERE id = :id"
        );
        $stmt->execute(["id" => $id]);
        return $stmt->fetch();
    }
}
