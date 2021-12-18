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

    public function add($day_id, $title, $date, $start, $stop, $weight) {
        $stmt = $this->db->prepare("INSERT INTO meeting VALUES(
            NULL, :day_id, :title, :date, :start, :stop, :weight)");
        $stmt->execute(["day_id" => $day_id, "title" => $title, "date" => $date, 
                        "start" => $start, "stop" => $stop, "weight" => $weight]);
        return $this->db->lastInsertId();        
    }

    public function allForOffering($offering_id) {
        $stmt = $this->db->prepare("SELECT m.id, m.title, d.abbr 
                FROM meeting AS m
                JOIN day AS d ON m.day_id = d.id 
                WHERE d.offering_id = :offering_id ");
        $stmt->execute(["offering_id" => $offering_id]);
        return $stmt->fetchAll();
    }
}