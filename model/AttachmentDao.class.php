<?php

/**
 * @author mzijlstra 2024-02-17
 */

#[Repository]
class AttachmentDao
{
    #[Inject('DB')]
    public $db;

    public function add($lab_id, $file, $name)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO attachment
            VALUES(NULL, :lab_id, :file, :name)"
        );
        $stmt->execute(array(
            "lab_id" => $lab_id,
            "file" => $file,
            "name" => $name,
        ));
        return $this->db->lastInsertId();
    }

    public function forLab($lab_id)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM attachment
            WHERE lab_id = :lab_id"
        );
        $stmt->execute(array("lab_id" => $lab_id));
        return $stmt->fetchAll();
    }

    public function delete($id, $lab_id)
    {
        $stmt = $this->db->prepare(
            "DELETE FROM attachment
            WHERE id = :id
            AND lab_id = :lab_id"
        );
        $stmt->execute(array(
            "id" => $id,
            "lab_id" => $lab_id
        ));
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM attachment
            WHERE id = :id"
        );
        $stmt->execute(array("id" => $id));
        return $stmt->fetch();
    }
}