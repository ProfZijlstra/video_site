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
            :middleMissing, :inClass, :excused, :start, :stop)");
        foreach ($report as $teamsName => $attend) {
            $stmt->execute([
                "meeting_id" => $meeting_id, 
                "teamsName" => $teamsName, 
                "notEnrolled" => $attend["notEnrolled"], 
                "absent" => $attend["absent"], 
                "arriveLate" => $attend["arriveLate"], 
                "leaveEarly" => $attend["leaveEarly"], 
                "middleMissing" => $attend["middleMissing"],
                "inClass" => 0,
                "excused" => $attend["excused"],
                "start" => $attend["start"],
                "stop" => $attend["stop"],
            ]);
        }
    }

    public function forMeeting($meeting_id) {
        $stmt = $this->db->prepare("SELECT a.id, a.teamsName, u.studentID,
                    a.arriveLate, a.middleMissing, a.leaveEarly, a.inClass,
                    a.notEnrolled, a.absent, a.excused, a.meeting_id,
                    a.start, a.stop, u.badge 
                FROM attendance AS a
                LEFT JOIN user AS u on a.teamsName = u.teamsName
                WHERE a.meeting_id = :meeting_id
                ORDER BY a.id DESC");
        $stmt->execute(["meeting_id" => $meeting_id]);
        return $stmt->fetchAll();        
    }

    public function unexcusedAbsentForMeeting($meeting_id) {
        $stmt = $this->db->prepare("SELECT u.email, u.knownAs, 
                    m.title, m.start, m.stop, a.teamsName
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
                    m.title, m.start, m.stop, a.teamsName,
                    a.arriveLate, a.leaveEarly, a.middleMissing,
                    a.start as `arrive`, a.stop as `left`
                FROM attendance AS a
                JOIN meeting AS m ON a.meeting_id = m.id
                JOIN user AS u on a.teamsName = u.teamsName
                WHERE a.meeting_id = :meeting_id
                AND (a.arriveLate = 1 OR a.leaveEarly = 1 OR a.middleMissing = 1)
                AND a.excused = 0");
        $stmt->execute(["meeting_id" => $meeting_id]);
        return $stmt->fetchAll();                
    }


    public function update($data) {      
        $stmt = $this->db->prepare("UPDATE attendance SET 
            arriveLate = :late,
            leaveEarly = :left, 
            middleMissing = :mid, 
            inClass = :phys,
            excused = :excu, 
            `start` = :start,
            `stop` = :stop
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

    public function getExportData($session_id) {
        $stmt = $this->db->prepare(
            "SELECT u.studentID, 
            SUM(a.absent) AS `absent`, 
            SUM(a.arriveLate) AS late, 
            SUM(a.leaveEarly) AS leaveEarly, 
            SUM(a.middleMissing) AS middleMissing, 
            SUM(a.inClass) AS inClass, 
            SUM(a.excused) AS excused,
            SEC_TO_TIME(SUM(case when TIME_TO_SEC(TIMEDIFF(a.start, m.start)) > 0 
                then TIME_TO_SEC(TIMEDIFF(a.start, m.start)) 
                else 0 end)) AS minsLate,
            SEC_TO_TIME(SUM(case when TIME_TO_SEC(TIMEDIFF(m.stop, a.stop)) > 0 
                then TIME_TO_SEC(TIMEDIFF(m.stop, a.stop)) 
                else 0 end)) AS minsLeave
            FROM attendance AS a
            JOIN meeting AS m ON a.meeting_id = m.id
            JOIN user AS u ON a.teamsName = u.teamsName
            WHERE m.session_id = :session_id
            GROUP BY u.id");
        $stmt->execute(["session_id" => $session_id]);
        return $stmt->fetchAll();                        
    }

    public function professionalism($offering_id) {
        $stmt = $this->db->prepare(
            "SELECT u.studentID, u.knownAs, u.lastname,
            SUM(a.absent) AS `absent`, 
            SUM(a.arriveLate) AS late, 
            SUM(a.leaveEarly) AS leaveEarly, 
            SUM(a.middleMissing) AS middleMissing, 
            SUM(a.inClass) AS inClass, 
            SEC_TO_TIME(SUM(case when a.arriveLate > 0 
                then TIME_TO_SEC(TIMEDIFF(a.start, m.start)) 
                else 0 end)) AS minsLate,
            SEC_TO_TIME(SUM(case when a.leaveEarly > 0 
                then TIME_TO_SEC(TIMEDIFF(m.stop, a.stop)) 
                else 0 end)) AS minsLeave
            FROM attendance AS a
            JOIN meeting AS m ON a.meeting_id = m.id
            JOIN `class_session` AS s ON m.session_id = s.id
            JOIN `day` AS d ON s.day_id = d.id
            JOIN user AS u ON a.teamsName = u.teamsName
            JOIN enrollment AS e ON e.user_id = u.id
            WHERE d.offering_id = :offering_id
            AND e.offering_id = :offering_id
            AND a.excused = 0
            GROUP BY u.id");
        $stmt->execute(["offering_id" => $offering_id]);
        return $stmt->fetchAll();                        
    }
}