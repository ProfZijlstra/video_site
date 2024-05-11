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
        $stmt = $this->db->prepare("
            SELECT d.*, COUNT(a.id) AS answers, COUNT(b.id) as ungraded
            FROM deliverable AS d 
            LEFT JOIN delivery AS a ON d.id = a.deliverable_id
            LEFT JOIN delivery AS b ON a.id = b.id AND b.points IS NULL
            WHERE d.lab_id = :lab_id
            GROUP BY d.id
            ORDER BY seq
        ");
        $stmt->execute(array("lab_id" => $lab_id));
        return $stmt->fetchAll();
    }

    public function add($lab_id, $type, $seq)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO deliverable
            VALUES(NULL, :lab_id, :type, :seq, '', 0, 0, NULL, NULL)"
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

    public function delete($id, $lab_id)
    {
        $stmt = $this->db->prepare(
            "DELETE FROM deliverable
            WHERE id = :id AND lab_id = :lab_id"
        );
        $stmt->execute(array(
            "id" => $id,
            "lab_id" => $lab_id
        ));
    }

    public function deleteAllForLab($lab_id)
    {
        $stmt = $this->db->prepare(
            "DELETE FROM deliverable
            WHERE lab_id = :lab_id"
        );
        $stmt->execute(array(
            "lab_id" => $lab_id
        ));
    }

    public function update($id, $lab_id, $points, $desc, $hasMarkDown)
    {
        $stmt = $this->db->prepare(
            "UPDATE deliverable
            SET points = :points, `desc` = :desc, hasMarkDown = :hasMarkDown
            WHERE id = :id AND lab_id = :lab_id"
        );
        $stmt->execute(array(
            "id" => $id,
            "lab_id" => $lab_id,
            "points" => $points,
            "desc" => $desc,
            "hasMarkDown" => $hasMarkDown
        ));
    }

    public function setAnswerRelease($id, $lab_id, $release)
    {
        $stmt = $this->db->prepare(
            "UPDATE deliverable
            SET ansRelease = :release
            WHERE id = :id AND lab_id = :lab_id"
        );
        $stmt->execute(array(
            "id" => $id,
            "lab_id" => $lab_id,
            "release" => $release
        ));
    }

    public function setZipAttachment($id, $lab_id, $attachment_id)
    {
        $stmt = $this->db->prepare(
            "UPDATE deliverable
            SET zipAttachment_id = :attachment_id
            WHERE id = :id AND lab_id = :lab_id"
        );
        $stmt->execute(array(
            "id" => $id,
            "lab_id" => $lab_id,
            "attachment_id" => $attachment_id
        ));
    }

    public function clone($lab_id, $new_lab_id)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM deliverable
            WHERE lab_id = :lab_id"
        );
        $stmt->execute(array("lab_id" =>  $lab_id));
        $deliverables = $stmt->fetchAll();

        $stmt = $this->db->prepare(
            "INSERT INTO deliverable 
			VALUES(NULL, :lab_id, :type, :seq, :desc, :hasMarkDown, :points, NULL, NULL)"
        );
        foreach ($deliverables as $deliverable) {
            $stmt->execute(array(
                "lab_id" => $new_lab_id,
                "type" => $deliverable['type'],
                "seq" => $deliverable['seq'],
                "desc" => $deliverable['desc'],
                "hasMarkDown" => $deliverable['hasMarkDown'],
                "points" => $deliverable['points']
            ));
        }
    }
}
