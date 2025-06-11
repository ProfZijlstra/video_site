<?php

/**
 * @author mzijlstra 05/23/2024
 */

#[Repository]
class ZipDlActionDao
{
    #[Inject('DB')]
    public $db;

    public function add($attachment_id, $type, $file, $byte)
    {
        $stmt = $this->db->prepare("
            INSERT INTO zip_dl_action 
            VALUES (NULL, :attachment_id, :type, :file, :byte)
        ");
        $stmt->execute(array(
            "attachment_id" => $attachment_id,
            "type" => $type,
            "file" => $file,
            "byte" => $byte
        ));
    }

    public function forAttachment($attachment_id)
    {
        $stmt = $this->db->prepare("
            SELECT * 
            FROM zip_dl_action 
            WHERE attachment_id = :attachment_id
        ");
        $stmt->execute(array("attachment_id" => $attachment_id));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("
            DELETE FROM zip_dl_action 
            WHERE id = :id
        ");
        $stmt->execute(array("id" => $id));
    }

    public function deleteForAttachment($attachment_id) {
        $stmt = $this->db->prepare("
            DELETE FROM zip_dl_action 
            WHERE attachment_id = :attachment_id
        ");
        $stmt->execute(array("attachment_id" => $attachment_id));
    }
}
