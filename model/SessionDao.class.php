<?php

/**
 * Session Dao Class
 * @author mzijlstra 2021-11-29
 * @Repository
 */
class SessionDao
{
    /**
     * @var PDO PDO database connection object
     * @Inject("DB")
     */
    public $db;

    public function allForOffering($offering_id)
    {
        $stmt = $this->db->prepare(
            "SELECT s.id, s.type, s.exported, s.day_id, d.abbr 
            FROM `session` AS s
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
            FROM `session` AS s
            JOIN `day` AS d ON s.day_id = d.id 
            WHERE s.id = :session_id "
        );
        $stmt->execute(["session_id" => $session_id]);
        return $stmt->fetch();
    }
}
