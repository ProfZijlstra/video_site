<?php

/**
 * @author mzijlstra 2024-02-17
 */

#[Repository]
class AttachmentDao
{
    #[Inject('DB')]
    public $db;

    public function add($lab_id, $file)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO attachment
            VALUES(NULL, :lab_id, :file)"
        );
        $stmt->execute(array(
            "lab_id" => $lab_id,
            "file" => $file,
        ));
        return $this->db->lastInsertId();
    }
}
