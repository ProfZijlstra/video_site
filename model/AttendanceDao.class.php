<?php 
/**
 * Attendance Dao Class
 * @author mzijlstra 2021-11-29
 * @Repository
 */
class AttendanceDao {
   	/**
	 * @var PDO PDO database connection object
	 * @Inject("DB")
	 */
	public $db;

    public function remove($meeting_id) {
        $stmt = $this->db->prepare("DELETE FROM attendance 
            WHERE meeting_id = :meeting_id");
        $stmt->execute(["meeting_id" => $meeting_id]);
    }

    public function addReport($meeting_id, $report) {
        $stmt = $this->db->prepare("INSERT INTO attendance VALUES(NULL, :meeting_id, 
            :teamsName, :notEnrolled, :absent, :arriveLate, :leaveEarly, 
            :middleMissing, 0)");
        foreach ($report as $teamsName => $attend) {
            $stmt->execute([
                "meeting_id" => $meeting_id, 
                "teamsName" => $teamsName, 
                "notEnrolled" => $attend["notEnrolled"], 
                "absent" => $attend["absent"], 
                "arriveLate" => $attend["arriveLate"], 
                "leaveEarly" => $attend["leaveEarly"], 
                "middleMissing" => $attend["middleMissing"]
            ]);
        }
    }

    public function forMeeting($meeting_id) {
        $stmt = $this->db->prepare("SELECT *
                FROM attendance
                WHERE meeting_id = :meeting_id");
        $stmt->execute(["meeting_id" => $meeting_id]);
        return $stmt->fetchAll();        
    }

    public function update($data) {      
        var_dump($data);
        $stmt = $this->db->prepare("UPDATE attendance SET 
            arriveLate = :late,
            leaveEarly = :left, 
            middleMissing = :mid, 
            inClass = :phys
            WHERE id = :id");
        $stmt->execute($data);
    }
}