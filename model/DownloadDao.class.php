<?php

/**
 * @author mzijlstra 2024-03-18
 */

#[Repository]
class DownloadDao
{
    #[Inject('DB')]
    public $db;

    public function add($attachment_id, $user_id, $group)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO download
            VALUES(NULL, :attachment_id, :user_id, :group, NOW())"
        );
        $stmt->execute(array(
            "attachment_id" => $attachment_id,
            "user_id" => $user_id,
            "group" => $group
        ));
        return $this->db->lastInsertId();
    }
}
