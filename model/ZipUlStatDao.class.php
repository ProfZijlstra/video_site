<?php

/**
 * @author mzijlstra 2024-06-14
 */
#[Repository]
class ZipUlStatDao
{
    #[Inject('DB')]
    public $db;

    public function add($delivery_id, $timestamp, $type, $file, $cmt = null): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO zip_ul_stat 
            VALUES (NULL, :delivery_id, :timestamp, :type, :file, :cmt)
        ');
        $stmt->execute([
            'delivery_id' => $delivery_id,
            'timestamp' => $timestamp,
            'type' => $type,
            'file' => $file,
            'cmt' => $cmt,
        ]);

        return $this->db->lastInsertId();
    }

    public function forDeliverable($deliverable_id): array|bool
    {
        $stmt = $this->db->prepare('
            SELECT s.* FROM zip_ul_stat s
            JOIN delivery d ON s.delivery_id = d.id
            WHERE d.deliverable_id = :id
            ');
        $stmt->execute(['id' => $deliverable_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function forSubmission($submission_id): array|bool
    {
        $stmt = $this->db->prepare('
            SELECT z.* FROM zip_ul_stat z
            JOIN delivery d ON z.delivery_id = d.id
            WHERE d.submission_id = :id
            ');
        $stmt->execute(['id' => $submission_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

