<?php

/**
 * @author mzijlstra 2024-02-17
 */
#[Repository]
class AttachmentDao
{
    #[Inject('DB')]
    public $db;

    public function add($type, $deliverable_id, $file, $name)
    {
        $stmt = $this->db->prepare(
            'INSERT INTO attachment
            VALUES(NULL, :type, :file, :name, :deliverable_id)'
        );
        $stmt->execute([
            'type' => $type,
            'file' => $file,
            'name' => $name,
            'deliverable_id' => $deliverable_id,
        ]);

        return $this->db->lastInsertId();
    }

    public function byId($id)
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM attachment
            WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);

        return $stmt->fetch();
    }

    public function forLab($lab_id)
    {
        $stmt = $this->db->prepare(
            'SELECT a.* FROM attachment a
            JOIN deliverable d ON a.deliverable_id = d.id
            WHERE d.lab_id = :lab_id'
        );
        $stmt->execute(['lab_id' => $lab_id]);

        return $stmt->fetchAll();
    }

    public function forDeliverable($deliverable_id)
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM attachment
            WHERE deliverable_id = :deliverable_id'
        );
        $stmt->execute(['deliverable_id' => $deliverable_id]);

        return $stmt->fetchAll();
    }

    public function forOffering($offering_id, $type)
    {
        $stmt = $this->db->prepare(
            'SELECT a.* FROM attachment a
            JOIN deliverable d on a.deliverable_id = d.id
            JOIN lab l ON d.lab_id = l.id
            JOIN day dy ON l.day_id = dy.id
            WHERE dy.offering_id = :offering_id
            AND a.type = :type'
        );
        $stmt->execute([
            'offering_id' => $offering_id,
            'type' => $type,
        ]);

        return $stmt->fetchAll();
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare(
            'DELETE FROM attachment
            WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
    }
}
