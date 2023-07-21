<?php

/**
 * Session Dao Class
 * 
 * Unfortunately the word Session already has a meaning in web development
 * When it's used here it means the day-part (AM or PM) of an offering
 * 
 * @author mzijlstra 2021-11-29
 * @Repository
 */
class ClassSessionDao
{
    /**
     * @var PDO PDO database connection object
     * @Inject("DB")
     */
    public $db;

    public function calcStatus($session_id) {
        $stmt = $this->db->prepare(
            "SELECT m.session_id AS id, 
            COUNT(m.id) AS meetings,
            MIN(m.start) AS `start`,
            MAX(m.stop) AS `stop`
            FROM meeting AS m
            WHERE m.session_id = :session_id
            GROUP BY m.session_id");
        $stmt->execute(["session_id" => $session_id]);
        return $stmt->fetch();        
    }

    public function setStatus($stats) {
        $stmt = $this->db->prepare(
            "UPDATE class_session SET 
            `status` = :status,
            `start` = :start,
            `stop` = :stop,
            `generated` = :meetings 
            WHERE id = :id");
        $stmt->execute(["status" => $stats["status"],
                        "start" => $stats["start"],
                        "stop" => $stats["stop"],
                        "meetings" => $stats["meetings"], 
                        "id" => $stats["id"]]);
    }

    public function allForOffering($offering_id)
    {
        $stmt = $this->db->prepare(
            "SELECT s.id, s.type, s.generated, s.day_id, d.abbr, s.status 
            FROM `class_session` AS s
            JOIN `day` AS d ON s.day_id = d.id 
            WHERE d.offering_id = :offering_id "
        );
        $stmt->execute(["offering_id" => $offering_id]);
        return $stmt->fetchAll();
    }

    public function getOfferingId($session_id)
    {
        $stmt = $this->db->prepare(
            "SELECT d.offering_id 
            FROM `class_session` AS s
            JOIN `day` AS d ON s.day_id = d.id 
            WHERE s.id = :session_id "
        );
        $stmt->execute(["session_id" => $session_id]);
        $row = $stmt->fetch();
        return $row["offering_id"];
    }

    public function createForOffering($offering_id) {
        $stmt = $this->db->prepare(
            "SELECT * FROM day 
            WHERE offering_id = :offering_id");
        $stmt->execute(["offering_id" => $offering_id]);
        $days = $stmt->fetchAll();

        $stmt = $this->db->prepare(
            "INSERT INTO `class_session` 
            VALUES(NULL, :day_id, :type, NULL, NULL, NULL, NULL)");
        foreach ($days as $day) {
            $stmt->execute(["day_id" => $day["id"], "type" => "AM"]);
            $stmt->execute(["day_id" => $day["id"], "type" => "PM"]);
        }
    }

    public function getSession($course, $offering, $day, $stype) {
        $stmt = $this->db->prepare(
            "SELECT s.id, s.day_id, s.type, s.status, s.start, s.stop, s.generated 
            FROM offering AS o
            JOIN day AS d ON o.id = d.offering_id
            JOIN class_session AS s ON d.id = s.day_id  
            WHERE o.course_number = :course
            AND o.block = :offering
            AND o.active = 1
            AND d.abbr = :day
            AND s.type = :stype");
        $stmt->execute(["course" => $course, "offering" => $offering, 
                        "day" => $day, "stype" => $stype]);
        return $stmt->fetch();
    }

    public function getSessionById($id) {
        $stmt = $this->db->prepare(
            "SELECT * FROM `class_session` WHERE id = :id ");
        $stmt->execute(["id" => $id]);
        return $stmt->fetch();
        
    }
}
