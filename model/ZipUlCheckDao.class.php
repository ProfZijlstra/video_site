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
}