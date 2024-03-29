<?php

/**
 * Attendance Data Dao Class
 * @author mzijlstra 2021-11-29
 */

#[Repository]
class AttendanceImportDao
{
    #[Inject('DB')]
    public $db;

    public function add($meeting_id, $name, $start, $stop)
    {
        $stmt = $this->db->prepare("INSERT INTO attendance_import 
            VALUES(NULL, :meeting_id, :name, :start, :stop)");
        $stmt->execute([
            "meeting_id" => $meeting_id, "name" => $name,
            "start" => $start, "stop" => $stop
        ]);
    }

    public function forMeeting($meeting_id)
    {
        $stmt = $this->db->prepare("SELECT * FROM attendance_import 
            WHERE meeting_id = :meeting_id ");
        $stmt->execute(["meeting_id" => $meeting_id]);
        return $stmt->fetchAll();
    }

    public function uniqueUsersForMeeting($meeting_id)
    {
        $stmt = $this->db->prepare("SELECT teamsName, MIN(start) as `start`, 
                        MAX(stop) as `stop` 
                    FROM attendance_import WHERE meeting_id = :meeting_id 
                    GROUP BY teamsName");
        $stmt->execute(["meeting_id" => $meeting_id]);
        return $stmt->fetchAll();
    }

    public function multiEntryForMeeting($meeting_id)
    {
        $stmt = $this->db->prepare("SELECT teamsName, COUNT(id) as `count`, 
                SUM(`stop` - `start`) as `duration` 
                FROM attendance_import 
                WHERE meeting_id = :meeting_id 
                GROUP BY teamsName
                HAVING `count` > 1");
        $stmt->execute(["meeting_id" => $meeting_id]);
        return $stmt->fetchAll();
    }

    public function deleteForMeeting($meeting_id)
    {
        $stmt = $this->db->prepare("DELETE FROM attendance_import 
                WHERE meeting_id = :meeting_id ");
        $stmt->execute(["meeting_id" => $meeting_id]);
    }
}

