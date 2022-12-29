<?php

/**
 * @author mzijlstra 08/17/2022
 * @Repository
 */

class QuizDao {
    /**
     * @Inject("DB")
     */
    public $db;

    public function allForOffering($offering_id) {
        $stmt = $this->db->prepare(
			"SELECT q.id, q.name, q.visible, d.abbr
            FROM quiz AS q
            JOIN day AS d ON q.day_id = d.id
            JOIN offering AS o ON d.offering_id = o.id
            WHERE o.id = :offering_id" 
		);
		$stmt->execute(array("offering_id" => $offering_id));
		return $stmt->fetchAll();
    }

    public function visibleForOffering($offering_id) {
        $stmt = $this->db->prepare(
            "SELECT q.id, q.name, q.visible, d.abbr
            FROM quiz AS q
            JOIN day AS d ON q.day_id = d.id
            JOIN offering AS o ON d.offering_id = o.id
            WHERE o.id = :offering_id 
            AND q.visible = 1"
		);
		$stmt->execute(array("offering_id" => $offering_id));
		return $stmt->fetchAll();
    }

    public function add($name, $day_id, $start, $stop) {
		$stmt = $this->db->prepare(
			"INSERT INTO quiz 
			VALUES(NULL, :name, :day_id, :start, :stop, 0)"
		);
		$stmt->execute(array(
            "name" => $name,
            "day_id" => $day_id,
            "start" => $start,
            "stop" => $stop
		));
		return $this->db->lastInsertId();
    }

    public function byId($id) {
        $stmt = $this->db->prepare(
            "SELECT * FROM quiz
            WHERE id = :id");
        $stmt->execute(array("id" => $id));
        return $stmt->fetch();
    }

    public function setStatus($id, $visible) {
        $stmt = $this->db->prepare(
			"UPDATE quiz 
            SET `visible` = :visible
            WHERE id = :id"
		);
		$stmt->execute(array(
            "id" =>  $id, 
            "visible" => $visible,
        ));
    }

    public function update($id, $name, $start, $stop) {
        $stmt = $this->db->prepare(
			"UPDATE quiz 
            SET `name` = :name, `start` = :start, `stop` = :stop
            WHERE id = :id"
		);
		$stmt->execute(array(
            "id" =>  $id, 
            "name" => $name,
            "start" => $start,
            "stop" => $stop
        ));
    }

    public function delete($id) {
        $stmt = $this->db->prepare(
			"DELETE FROM quiz 
            WHERE id = :id"
		);
		$stmt->execute(array("id" =>  $id));
    }
}
?>