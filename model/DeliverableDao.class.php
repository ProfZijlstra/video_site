<?php

/**
 * @author mzijlstra 14 Jan 2024
 */

#[Repository]
class DeliverableDao
{
    #[Inject('DB')]
    public $db;

    public function forLab($lab_id)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM deliverable
            WHERE lab_id = :lab_id
            ORDER BY seq"
        );
        $stmt->execute(array("lab_id" => $lab_id));
        return $stmt->fetchAll();
    }

    public function add($lab_id, $type, $seq)
    {
        print_r($lab_id);
        print_r($type);
        print_r($seq);

        $stmt = $this->db->prepare(
            "INSERT INTO deliverable
            VALUES(NULL, :lab_id, :type, :seq, '', 0, 0, 0)"
        );
        $stmt->execute(array(
            "lab_id" => $lab_id,
            "type" => $type,
            "seq" => $seq
        ));
        return $this->db->lastInsertId();
    }

    public function byId($id)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM deliverable
            WHERE id = :id"
        );
        $stmt->execute(array("id" => $id));
        return $stmt->fetch();
    }
}
