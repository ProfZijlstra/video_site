<?php

/**
 * User DAO Class
 *
 * @author mzijlstra 11/14/2014
 */
#[Repository]
class UserDao
{
    #[Inject('DB')]
    public $db;

    /**
     * Gets user details based on given email address
     *
     * @param  string  $email
     * @return array user data
     */
    public function checkLogin($email)
    {
        $find = $this->db->prepare(
            'SELECT id, firstname, lastname, email, password, isFaculty, isAdmin 
                FROM user 
                WHERE email = :email 
                AND active = TRUE '
        );
        $find->execute(['email' => $email]);

        return $find->fetch();
    }

    /**
     * Updates the last login / access time for the given user
     *
     * @param  int  $id  user id
     */
    public function updateAccessed($id)
    {
        $upd = $this->db->prepare(
            'UPDATE user SET accessed = NOW() 
                    WHERE id = :uid'
        );
        $upd->execute(['uid' => $id]);
    }

    /**
     * Get all user data
     *
     * @return array of arrays of user data
     */
    public function all()
    {
        // maybe add parameters for constraints and order by
        $stmt = $this->db->prepare('SELECT * FROM user ORDER BY accessed DESC');
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all users with isFaculty = 1
     *
     * @return array of arrays of user data
     */
    public function faculty()
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM user 
            WHERE `isFaculty` = 1 
            AND active = 1'
        );
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Gets user data based on id
     *
     * @param  int  $id  user id
     * @return array of user data
     */
    public function retrieve($id)
    {
        $stmt = $this->db->prepare('SELECT * FROM user WHERE id = :id');
        $stmt->execute([':id' => $id]);

        return $stmt->fetch();
    }

    /**
     * Creates a new user in the database based on given values
     */
    public function insert(
        $first,
        $last,
        $knownAs,
        $email,
        $studentID,
        $teamsName,
        $hash,
        $active,
        $isAdmin,
        $isFaculty
    ) {
        $stmt = $this->db->prepare('INSERT INTO user values 
                (NULL, :first, :last, :knownAs, :email, :studentID, :teamsName, 
                :pass, NOW(), NOW(), :active, NULL, :isAdmin, :isFaculty)');
        $stmt->execute([
            'first' => $first,
            'last' => $last,
            'knownAs' => $knownAs,
            'email' => $email,
            'studentID' => $studentID,
            'teamsName' => $teamsName,
            'pass' => $hash,
            'active' => $active,
            'isAdmin' => $isAdmin,
            'isFaculty' => $isFaculty,
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Updates a user row for given id with given values
     *
     * @param  int  $uid  user id
     * @param  string  $first
     * @param  string  $last
     * @param  string  $knownAs
     * @param  string  $email
     * @param  string  $studentID
     * @param  string  $teamsName
     * @param  int  $active
     * @param  int  $isAdmin
     * @param  int  $isFaculty
     */
    public function update(
        $uid,
        $first,
        $last,
        $knownAs,
        $email,
        $studentID,
        $teamsName,
        $active,
        $isAdmin,
        $isFaculty
    ) {
        $stmt = $this->db->prepare(
            'UPDATE user SET 
                firstname = :first, lastname = :last, knownAs = :knownAs, 
                email = :email, studentID = :studentID, teamsName = :teamsName, 
                active = :active, isAdmin = :isAdmin, isFaculty = :isFaculty 
                WHERE id = :uid'
        );
        $stmt->execute([
            'first' => $first,
            'last' => $last,
            'knownAs' => $knownAs,
            'email' => $email,
            'studentID' => $studentID,
            'teamsName' => $teamsName,
            'active' => $active,
            'isFaculty' => $isFaculty,
            'isAdmin' => $isAdmin,
            'uid' => $uid,
        ]);
    }

    public function updatePass($id, $hash)
    {
        $reset = $this->db->prepare('UPDATE user SET password = :pass 
                                        WHERE id = :uid');
        $reset->execute(['pass' => $hash, 'uid' => $id]);
    }

    public function updatePassByEmail($email, $hash)
    {
        $reset = $this->db->prepare('UPDATE user SET password = :pass 
                                        WHERE email = :email');
        $reset->execute(['pass' => $hash, 'email' => $email]);
    }

    public function getUserId($email)
    {
        $stmt = $this->db->prepare('SELECT * FROM user WHERE email = :email');
        $stmt->execute(['email' => $email]);
        if ($stmt->rowCount() == 0) {
            return null;
        } else {
            $row = $stmt->fetch();

            return $row['id'];
        }
    }

    public function getUserIdByStudentId($studentID)
    {
        $stmt = $this->db->prepare('SELECT * FROM user WHERE studentID = :studentID');
        $stmt->execute(['studentID' => $studentID]);
        if ($stmt->rowCount() == 0) {
            return null;
        } else {
            $row = $stmt->fetch();

            return $row['id'];
        }
    }

    public function byTeamsName($teamsName)
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM user WHERE teamsName = :teamsName'
        );
        $stmt->execute(['teamsName' => $teamsName]);
        if ($stmt->rowCount() == 0) {
            return null;
        } else {
            $row = $stmt->fetch();

            return $row['id'];
        }
    }

    public function setBadge($studentID, $badge)
    {
        $stmt = $this->db->prepare('UPDATE user SET badge = :badge 
                                    WHERE studentID = :studentID');
        $stmt->execute(['studentID' => $studentID, 'badge' => $badge]);
    }

    public function idsForQuiz($quiz_id)
    {
        $stmt = $this->db->prepare(
            'SELECT DISTINCT a.user_id 
            FROM answer AS a
            JOIN question AS q ON a.question_id = q.id
            WHERE q.quiz_id = :quiz_id'
        );
        $stmt->execute(['quiz_id' => $quiz_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateKnownAs($uid, $knownAs)
    {
        $stmt = $this->db->prepare(
            'UPDATE user SET knownAs = :knownAs WHERE id = :uid'
        );
        $stmt->execute(['knownAs' => $knownAs, 'uid' => $uid]);
    }
}
