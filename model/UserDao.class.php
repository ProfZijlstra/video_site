<?php

/**
 * User DAO Class
 *
 * @author mzijlstra 11/14/2014
 * @Repository
 */
class UserDao {

    /**
     *
     * @var PDO PDO database connection object   
     * @Inject("DB")
     */
    public $db;

    /**
     * Gets user details based on given email address
     * @param string $email 
     * @return array user data
     */
    public function checkLogin($email) {
        $find = $this->db->prepare(
                "SELECT id, firstname, lastname, password, type "
                . "FROM user "
                . "WHERE email = :email "
                . "AND active = TRUE ");
        $find->execute(array("email" => $email));
        return $find->fetch();
    }

    /**
     * Updates the last login / access time for the given user 
     * @param int $id user id
     */
    public function updateAccessed($id) {
        $upd = $this->db->prepare(
                "UPDATE user SET accessed = NOW() "
                . "WHERE id = :uid");
        $upd->execute(array("uid" => $id));
    }
    
    /**
     * Get all user data
     * @return array of arrays of user data
     */
    public function all() {
        // TODO add parameters for constraints and order by
        $stmt = $this->db->prepare("SELECT * FROM user");
        $stmt->execute();
        return $stmt->fetchAll();        
    }
    
    /**
     * Gets user data based on id
     * @param int $id user id
     * @return array of user data
     */
    public function retrieve($id) {
        $stmt = $this->db->prepare("SELECT * FROM user WHERE id = :id");
        $stmt->execute(array(":id" => $id));
        return $stmt->fetch();
    }

    /**
     * Creates a new user in the database based on given values
     * @param string $first
     * @param string $last
     * @param string $email
     * @param string $hash password hash
     * @param string $type user type
     * @param int $active
     * @return int id of created row
     */
    public function insert($first, $last, $email, $hash, $type, $active) {
        $stmt = $this->db->prepare("INSERT INTO user values "
                . "(NULL, :first, :last, :email, :pass, :type,"
                . " NOW(), NOW(), :active)");
        $stmt->execute(array(
            "first" => $first, "last" => $last, "email" => $email, 
            "pass" => $hash, "type" => $type, "active" => $active,
        ));
        return $this->db->lastInsertId();
    }

    /**
     * Updates a user row for given id with given values
     * @param string $first
     * @param string $last
     * @param string $email
     * @param string $type user type
     * @param int $active
     * @param int $uid user id
     * @param string $pass password hash
     */
    public function update($first, $last, $email, $type, $active, $uid, 
            $pass) {
        $stmt = $this->db->prepare("UPDATE user SET "
                . "firstname = :first, lastname = :last, "
                . "email = :email, type = :type, "
                . "active = :active WHERE id = :uid");
        $stmt->execute(array(
            "first" => $first, "last" => $last, "email" => $email, 
            "type" => $type, "active" => $active, "uid" => $uid
        ));

        if ($pass) {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $reset = $this->db->prepare("UPDATE user SET password = :pass WHERE id = :uid");
            $reset->execute(array("pass" => $hash, "uid" => $uid));
        }
    }

}
