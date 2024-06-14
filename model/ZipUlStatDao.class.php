<?php

/**
 * @author mzijlstra 2024-06-14
 */

#[Repository]
class ZipUlStatDao
{
    #[Inject('DB')]
    public $db;

    function add($delivery_id, $timestamp, $type, $file) 
    {
        $stmt = $this->db->prepare("
            INSERT INTO zip_ul_stat 
            VALUES (NULL, :delivery_id, :timestamp, :type, :file)
        ");
        $stmt->execute(array(
            "delivery_id" => $delivery_id,
            "timestamp" => $timestamp,
            "type" => $type,
            "file" => $file
        ));
        return $this->db->lastInsertId();
    }
}