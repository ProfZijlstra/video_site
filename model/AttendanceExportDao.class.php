<?php 
/**
 * Attendance_Export Dao Class
 * @author mzijlstra 2022-03-13
 * @Repository
 */
class AttendanceExportDao {
   	/**
	 * @var PDO PDO database connection object
	 * @Inject("DB")
	 */
	public $db;

    public function clear($session_id) {
        $stmt = $this->db->prepare("DELETE FROM attendance_export 
            WHERE session_id = :session_id");
        $stmt->execute(["session_id" => $session_id]);
    }

    public function create($session_id, $exports) {
        $stmt = $this->db->prepare(
            "INSERT INTO attendance_export 
            VALUES(NULL, :studentID, :status, :inClass, :comment, :session_id)"); 
        foreach ($exports as $export) {
            $stmt->execute([
                "studentID" => $export["studentID"],
                "status" => $export["status"],
                "inClass" => $export["inClass"] ? 1 : 0,
                "comment" => $export["comment"],
                "session_id" => $session_id
            ]);
        }
    }

    public function forSession($session_id) {
        $stmt = $this->db->prepare(
            "SELECT e.id, e.studentID, e.status, e.inClass, e.comment,
            u.firstname, u.lastname, u.knownAs
            FROM attendance_export AS e 
            JOIN user AS u ON e.studentID = u.studentID
            WHERE e.session_id = :session_id
            ORDER BY u.lastname"
        );
        $stmt->execute(["session_id" => $session_id]);
        return $stmt->fetchAll();
    }

    public function update($data) {      
        $stmt = $this->db->prepare("UPDATE attendance_export SET 
            inClass = :inClass,
            comment = :comment
            WHERE id = :id");
        $stmt->execute($data);
    }

    public function getPhysicalAttendance($offering_id, $week) {
		$stmt = $this->db->prepare(
			"SELECT u.id, u.knownAs, u.studentID, 
			u.firstname, u.lastname, u.email,
			SUM(ex.inClass) AS inClass
            FROM `day` d 
			JOIN `session` s ON s.day_id = d.id
			JOIN attendance_export ex ON ex.session_id = s.id
			JOIN user u ON ex.studentID = u.studentID
            WHERE d.offering_id = :offering_id
			AND d.abbr LIKE :week
			GROUP BY ex.studentID
			ORDER BY inClass");
		$stmt->execute(["offering_id" => $offering_id, "week" => "$week%"]);
		return $stmt->fetchAll();		
	}

    public function internationalPhysicalBelow($offering_id, $week, $min) {
		$stmt = $this->db->prepare(
			"SELECT u.id, u.knownAs, u.studentID, 
			u.firstname, u.lastname, u.email,
			SUM(ex.inClass) AS inClass
            FROM `day` d 
			JOIN `session` s ON s.day_id = d.id
			JOIN attendance_export ex ON ex.session_id = s.id
			JOIN user u ON ex.studentID = u.studentID
            WHERE d.offering_id = :offering_id
			AND d.abbr LIKE :week
            AND u.studentID > 600000
			GROUP BY ex.studentID
            HAVING inClass < :min
			ORDER BY inClass");
		$stmt->execute(["offering_id" => $offering_id, 
                        "week" => "$week%", 
                        "min" => $min]);
		return $stmt->fetchAll();		

    }
}