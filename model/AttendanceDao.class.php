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
            :middleMissing, 0, 0)");
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
        $stmt = $this->db->prepare("SELECT a.id, a.teamsName, u.studentID,
                    a.arriveLate, a.middleMissing, a.leaveEarly, a.inClass,
                    a.notEnrolled, a.absent, a.excused, a.meeting_id,
                    MIN(d.start) as `start`, MAX(d.stop) as `stop`
                FROM attendance AS a
                LEFT JOIN attendance_data AS d ON a.meeting_id = d.meeting_id 
                    AND a.teamsName = d.teamsName
                LEFT JOIN user AS u on a.teamsName = u.teamsName
                WHERE a.meeting_id = :meeting_id
                GROUP BY a.teamsName
                ORDER BY a.id DESC");
        $stmt->execute(["meeting_id" => $meeting_id]);
        return $stmt->fetchAll();        
    }

    public function unexcusedAbsentForMeeting($meeting_id) {
        $stmt = $this->db->prepare("SELECT u.email, u.knownAs, 
                    m.title, m.start, m.stop
                FROM attendance AS a
                JOIN meeting AS m ON a.meeting_id = m.id
                JOIN user AS u on a.teamsName = u.teamsName
                WHERE a.meeting_id = :meeting_id
                AND a.absent = 1
                AND a.excused = 0");
        $stmt->execute(["meeting_id" => $meeting_id]);
        return $stmt->fetchAll();                
    }

    public function unexcusedTardyForMeeting($meeting_id) {
        $stmt = $this->db->prepare("SELECT u.email, u.knownAs, 
                    m.title, m.start, m.stop,
                    a.arriveLate, a.leaveEarly, a.middleMissing,
                    MIN(d.start) as `arrive`, MAX(d.stop) as `left`
                FROM attendance AS a
                JOIN meeting AS m ON a.meeting_id = m.id
                JOIN attendance_data AS d ON a.meeting_id = d.meeting_id 
                    AND a.teamsName = d.teamsName
                JOIN user AS u on a.teamsName = u.teamsName
                WHERE a.meeting_id = :meeting_id
                AND (a.arriveLate = 1 OR a.leaveEarly = 1 OR a.middleMissing = 1)
                AND a.excused = 0
                GROUP BY a.teamsName");
        $stmt->execute(["meeting_id" => $meeting_id]);
        return $stmt->fetchAll();                
    }


    public function update($data) {      
        $stmt = $this->db->prepare("UPDATE attendance SET 
            arriveLate = :late,
            leaveEarly = :left, 
            middleMissing = :mid, 
            inClass = :phys,
            excused = :excu
            WHERE id = :id");
        $stmt->execute($data);
    }

    public function markAbsent($id, $absent) {
        $stmt = $this->db->prepare("UPDATE attendance SET 
            `absent` = :absent 
            WHERE id = :id");
        $stmt->execute(["id" => $id, "absent" => $absent]);
    }

    public function deleteForMeeting($meeting_id) {
        $stmt = $this->db->prepare("DELETE FROM attendance
                WHERE meeting_id = :meeting_id ");
        $stmt->execute(["meeting_id" => $meeting_id]);
    }
}