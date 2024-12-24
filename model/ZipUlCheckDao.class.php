<?php

/**
 * @author mzijlstra 2024-06-09
 */
#[Repository]
class ZipUlCheckDao
{
    #[Inject('DB')]
    public $db;

    public function forDeliverable($deliverable_id): array|bool
    {
        $stmt = $this->db->prepare('
            SELECT * 
            FROM zip_ul_check
            WHERE deliverable_id = :deliverable_id
        ');
        $stmt->execute(['deliverable_id' => $deliverable_id]);

        return $stmt->fetchAll();
    }

    public function add($deliverable_id, $type, $file, $byte, $block): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO zip_ul_check 
            VALUES (NULL, :deliverable_id, :type, :file, :byte, :block)
        ');
        $stmt->execute([
            'deliverable_id' => $deliverable_id,
            'type' => $type,
            'file' => $file,
            'byte' => $byte,
            'block' => $block,
        ]);

        return $this->db->lastInsertId();
    }

    public function delete($id): void
    {
        $stmt = $this->db->prepare('
            DELETE FROM zip_ul_check
            WHERE id = :id
        ');
        $stmt->execute(['id' => $id]);
    }
}

