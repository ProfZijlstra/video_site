<?php 
/**
 * Meeting Dao Class
 * @author mzijlstra 2021-11-29
 * @Repository
 */
class MeetingDao {
   	/**
	 * @var PDO PDO database connection object
	 * @Inject("DB")
	 */
	public $db;

    public function add($session_id, $title, $date, $start, $stop) {
        $stmt = $this->db->prepare("INSERT INTO meeting VALUES(
            NULL, :title, :date, :start, :stop, :session_id)");
        $stmt->execute(["title" => $title, "date" => $date, 
                        "start" => $start, "stop" => $stop, 
                        "session_id" => $session_id]);
        return $this->db->lastInsertId();        
    }

    public function allForOffering($offering_id) {
        $stmt = $this->db->prepare("SELECT m.id, m.title, d.abbr, s.type as stype
                FROM meeting AS m
                JOIN `session` AS s ON m.session_id = s.id
                JOIN `day` AS d ON s.day_id = d.id 
                WHERE d.offering_id = :offering_id ");
        $stmt->execute(["offering_id" => $offering_id]);
        return $stmt->fetchAll();
    }

    public function get($id) {
        $stmt = $this->db->prepare("SELECT * 
                FROM meeting 
                WHERE id = :id ");
        $stmt->execute(["id" => $id]);
        return $stmt->fetch();
    }

    public function update($id, $title, $date, $start, $stop) {
        $stmt = $this->db->prepare("UPDATE meeting 
                SET title = :title, `date` = :date, 
                    `start` = :start, `stop` = :stop
                WHERE id = :id ");
        $stmt->execute(["id" => $id, "title" => $title, "date" => $date, 
                        "start" => $start, "stop" => $stop]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM meeting WHERE id = :id ");
        $stmt->execute(["id" => $id]);
    }
}