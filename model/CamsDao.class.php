<?php

/**
 * CAMS Dao Class
 * 
 * @author mzijlstra 2023-06-19
 * @Repository
 */
class CamsDao {
    /**
     * @var PDO PDO database connection object
     * @Inject("DB")
     */
    public $db;

    public function get($id) {
        $stmt = $this->db->prepare(
            "SELECT * FROM `CAMS` WHERE offering_id = :id ");
        $stmt->execute(["id" => $id]);
        return $stmt->fetch();        
    }

    public function saveOrUpdate($id, $username, $course_id, $AM_id, $PM_id, $SAT_id) {
        $stmt = $this->db->prepare(
            "DELETE FROM CAMS WHERE offering_id = :id");
        $stmt->execute(["id" => $id]);
        $stmt = $this->db->prepare(
            "INSERT INTO CAMS VALUES 
            (:id, :username, :course_id, :AM_id, :PM_id, :SAT_id)"); 
        $stmt->execute([
            "id" => $id,
            "username" => $username,
            "course_id" => $course_id,
            "AM_id" => $AM_id,
            "PM_id" => $PM_id,
            "SAT_id" => $SAT_id,
        ]);  
    }


}
