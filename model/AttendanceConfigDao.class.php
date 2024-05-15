<?php

/**
 * @author mzijlstra 14 May 2024
 */

#[Repository]
class AttendanceConfigDao
{
    #[Inject('DB')]
    public $db;

    public function byId($offering_id)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM attendance_config WHERE offering_id = :offering_id"
        );
        $stmt->execute(array("offering_id" => $offering_id));
        return $stmt->fetch();
    }

    public function saveOrUpdate(
        $offering_id,
        $AM_start,
        $AM_stop,
        $PM_start,
        $PM_stop,
        $inClass
    ) {
        $stmt = $this->db->prepare(
            "INSERT INTO attendance_config 
            VALUES(:offering_id, :AM_start, :AM_stop, :PM_start, :PM_stop, :inClass)
            ON DUPLICATE KEY UPDATE 
            inClass = :inClass, 
            AM_start = :AM_start, 
            AM_stop = :AM_stop, 
            PM_start = :PM_start, 
            PM_stop = :PM_stop"
        );
        $stmt->execute(array(
            "offering_id" => $offering_id,
            "inClass" => $inClass,
            "AM_start" => $AM_start,
            "AM_stop" => $AM_stop,
            "PM_start" => $PM_start,
            "PM_stop" => $PM_stop
        ));
    }
}
