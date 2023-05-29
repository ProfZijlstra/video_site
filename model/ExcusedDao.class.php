<?php 
/**
 * Excused Dao Class
 * @author mzijlstra 2023-05-26
 * @Repository
 */
class ExcusedDao {
   	/**
	 * @var PDO PDO database connection object
	 * @Inject("DB")
	 */
	public $db;

    public function add($session_id, $teamsName) {
        $stmt = $this->db->prepare("INSERT INTO excused VALUES(
            NULL, :session_id, :teamsName)");
        $stmt->execute([
            "session_id" => $session_id,
            "teamsName" => $teamsName
        ]);
        return $this->db->lastInsertId();        
    }

    public function allForOffering($offering_id) {
        $stmt = $this->db->prepare(
            "SELECT e.id, e.class_session_id, e.teamsName 
            FROM excused AS e
            JOIN `class_session` AS s ON e.class_session_id  = s.id
            JOIN `day` AS d ON s.day_id = d.id
            WHERE d.offering_id = :offering_id");
        $stmt->execute(["offering_id" => $offering_id]);
        return $stmt->fetchAll();
    }

    public function forClassSession($session_id) {
        $stmt = $this->db->prepare(
            "SELECT e.id, e.class_session_id, e.teamsName 
            FROM excused AS e
            WHERE e.class_session_id = :session_id");
        $stmt->execute(["session_id" => $session_id]);
        return $stmt->fetchAll();
    }

    public function delete($session_id, $teamsName) {
        $stmt = $this->db->prepare(
            "DELETE FROM excused 
            WHERE class_session_id = :id 
            AND teamsName = :teamsName"
        );
        $stmt->execute(["id" => $session_id, "teamsName" => $teamsName]);
    }
}
