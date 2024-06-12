<?php

/**
 * @author mzijlstra 2024-06-09
 */

#[Repository]
class ZipUlCheckDao
{
    #[Inject('DB')]
    public $db;

    public function forDeliverable($deliverable_id) {
        $stmt = $this->db->prepare("
            SELECT * 
            FROM zip_ul_check
            WHERE deliverable_id = :deliverable_id
        ");
        $stmt->execute(array("deliverable_id" => $deliverable_id));
        return $stmt->fetchAll();
    }

    function add($deliverable_id, $type, $file, $byte) 
    {
        $stmt = $this->db->prepare("
            INSERT INTO zip_ul_check 
            VALUES (NULL, :deliverable_id, :type, :file, :byte)
        ");
        $stmt->execute(array(
            "deliverable_id" => $deliverable_id,
            "type" => $type,
            "file" => $file,
            "byte" => $byte
        ));
        return $this->db->lastInsertId();
    }

    function delete($id) 
    {
        $stmt = $this->db->prepare("
            DELETE FROM zip_ul_check
            WHERE id = :id
        ");
        $stmt->execute(array("id" => $id));
    }
}