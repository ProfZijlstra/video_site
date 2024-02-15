<?php

/**
 * @author mzijlstra 14 Jan 2024
 */

#[Repository]
class LabDao
{
    #[Inject('DB')]
    public $db;

    public function allForOffering(int $offering_id): array
    {
        $stmt = $this->db->prepare(
            "SELECT a.id, a.name, a.visible, d.abbr 
            FROM lab AS a
            JOIN day AS d ON a.day_id = d.id
            JOIN offering AS o ON d.offering_id = o.id
            WHERE o.id = :offering_id
            AND o.active = 1"
        );
        $stmt->execute(array("offering_id" => $offering_id));
        return $stmt->fetchAll();
    }

    public function visibleForOffering(int $offering_id): array
    {
        $stmt = $this->db->prepare(
            "SELECT a.id, a.name, a.visible, d.abbr
            FROM lab AS a
            JOIN day AS d ON a.day_id = d.id
            JOIN offering AS o ON d.offering_id = o.id
            WHERE o.id = :offering_id 
            AND a.visible = 1
            AND o.active = 1"
        );
        $stmt->execute(array("offering_id" => $offering_id));
        return $stmt->fetchAll();
    }

    public function add(string $name, int $day_id, string $start, string $stop): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO lab
            VALUES(NULL, :day_id, :name, '', 0, :start, :stop, 0, 'Individual', 10)"
        );
        $stmt->execute(array(
            "name" => $name,
            "day_id" => $day_id,
            "start" => $start,
            "stop" => $stop
        ));
        return $this->db->lastInsertId();
    }

    public function byId($id)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM lab
            WHERE id = :id"
        );
        $stmt->execute(array("id" => $id));
        return $stmt->fetch();
    }

    public function update($id, $visible, $name, $day_id, $start, $stop, $points, $type, $hasMarkDown, $desc)
    {
        $stmt = $this->db->prepare(
            "UPDATE lab 
            SET visible = :visible, `name` = :name, 
            day_id = :day_id, 
            `start` = :start, 
            `stop` = :stop, 
            points = :points, 
            type = :type, 
            hasMarkDown = :hasMarkDown, 
            `desc` = :desc
            WHERE id = :id"
        );
        $stmt->execute(array(
            "id" =>  $id,
            "visible" => $visible,
            "name" => $name,
            "day_id" => $day_id,
            "start" => $start,
            "stop" => $stop,
            "points" => $points,
            "type" => $type,
            "hasMarkDown" => $hasMarkDown,
            "desc" => $desc
        ));
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare(
            "DELETE FROM lab
            WHERE id = :id"
        );
        $stmt->execute(array("id" => $id));
    }
}
