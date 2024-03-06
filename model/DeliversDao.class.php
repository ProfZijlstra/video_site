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
}
