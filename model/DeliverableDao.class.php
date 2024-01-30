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
}
