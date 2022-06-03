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
                "SELECT id, firstname, lastname, password, type 
                FROM user 
                WHERE email = :email 
                AND active = TRUE ");
        $find->execute(array("email" => $email));
        return $find->fetch();
    }

    /**
     * Updates the last login / access time for the given user 
     * @param int $id user id
     */
    public function updateAccessed($id) {
        $upd = $this->db->prepare(
                "UPDATE user SET accessed = NOW() 
                    WHERE id = :uid");
        $upd->execute(array("uid" => $id));
    }
    
    /**
     * Get all user data
     * @return array of arrays of user data
     */
    public function all() {
        // maybe add parameters for constraints and order by
        $stmt = $this->db->prepare("SELECT * FROM user");
        $stmt->execute();
        return $stmt->fetchAll();        
    }

    public function faculty() {
        $stmt = $this->db->prepare(
            "SELECT * FROM user 
            WHERE `type` = 'admin' 
            AND active = 1");
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
     * @param string $knownAs
     * @param string $email
     * @param string $studentID
     * @param string $teamsName
     * @param string $hash password hash
     * @param string $type user type
     * @param int $active
     * @return int id of created row
     */
    public function insert($first, $last, $knownAs, $email, $studentID, 
                                $teamsName, $hash, $type, $active) {
        $stmt = $this->db->prepare("INSERT INTO user values 
                (NULL, :first, :last, :knownAs, :email, :studentID, :teamsName, 
                :pass, :type, NOW(), NOW(), :active, 0, NULL)");
        $stmt->execute(array(
            "first" => $first, "last" => $last, "email" => $email, 
            "pass" => $hash, "type" => $type, "active" => $active,
            "studentID" => $studentID, "knownAs" => $knownAs, "teamsName" =>
            $teamsName
        ));
        return $this->db->lastInsertId();
    }

    /**
     * Updates a user row for given id with given values
     * @param int $uid user id
     * @param string $first
     * @param string $last
     * @param string $knownAs
     * @param string $email
     * @param string $studentID
     * @param string $teamsName
     * @param string $type user type
     * @param int $active
     * @param string $pass password hash
     */
    public function update($uid, $first, $last, $knownAs, $email, $studentID, 
                            $teamsName, $type, $active, $pass) {
        $stmt = $this->db->prepare("UPDATE user SET 
                firstname = :first, lastname = :last, knownAs = :knownAs, 
                email = :email, studentID = :studentID, teamsName = :teamsName, 
                type = :type, active = :active WHERE id = :uid"
        );
        $stmt->execute(array(
            "first" => $first, "last" => $last, "knownAs" => $knownAs, 
            "email" => $email, "studentID" => $studentID, 
            "teamsName" => $teamsName,
            "type" => $type, "active" => $active, "uid" => $uid
        ));

        if ($pass) {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $reset = $this->db->prepare("UPDATE user SET password = :pass 
                                            WHERE id = :uid");
            $reset->execute(array("pass" => $hash, "uid" => $uid));
        }
    }

    public function getUserId($email) {
        $stmt = $this->db->prepare("SELECT * FROM user WHERE email = :email");
        $stmt->execute(array("email" => $email));
        if ($stmt->rowCount() == 0) {
            return null;
        } else {
            $row = $stmt->fetch();
            return $row["id"];
        }
    }

    public function byTeamsName($teamsName) {
        $stmt = $this->db->prepare(
            "SELECT * FROM user WHERE teamsName = :teamsName");
        $stmt->execute(array("teamsName" => $teamsName));
        if ($stmt->rowCount() == 0) {
            return null;
        } else {
            $row = $stmt->fetch();
            return $row["id"];
        }
    }

    public function setBadge($studentID, $badge) {
        $stmt = $this->db->prepare("UPDATE user SET badge = :badge 
                                    WHERE studentID = :studentID");
        $stmt->execute(array("studentID" => $studentID, "badge" => $badge));
    }
}
