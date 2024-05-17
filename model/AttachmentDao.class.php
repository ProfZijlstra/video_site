<?php

/**
 * @author mzijlstra 2024-02-17
 */

#[Repository]
class AttachmentDao
{
    #[Inject('DB')]
    public $db;

    public function add($type, $lab_id, $file, $name)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO attachment
            VALUES(NULL, :type, :lab_id, :file, :name)"
        );
        $stmt->execute(array(
            "type" => $type,
            "lab_id" => $lab_id,
            "file" => $file,
            "name" => $name,
        ));
        return $this->db->lastInsertId();
    }

    public function byId($id)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM attachment
            WHERE id = :id"
        );
        $stmt->execute(array("id" => $id));
        return $stmt->fetch();
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

    public function forDeliverable($deliverable_id)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM attachment
            WHERE deliverable_id = :deliverable_id"
        );
        $stmt->execute(array("deliverable_id" => $deliverable_id));
        return $stmt->fetchAll();
    }

    public function forOffering($offering_id, $type)
    {
        $stmt = $this->db->prepare(
            "SELECT a.* FROM attachment a
            JOIN lab l ON a.lab_id = l.id
            JOIN day d ON l.day_id = d.id
            WHERE d.offering_id = :offering_id
            AND a.type = :type"
        );
        $stmt->execute(array(
            "offering_id" => $offering_id,
            "type" => $type
        ));
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
}
