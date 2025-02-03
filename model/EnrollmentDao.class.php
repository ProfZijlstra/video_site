<?php

/**
 * Enrollment Dao Class
 *
 * @author mzijlstra 06/06/2021
 */
#[Repository]
class EnrollmentDao
{
    #[Inject('DB')]
    public $db;

    /**
     * Gets Enrollment for a given offering
     *
     * @param int offering_id
     * @return array of offering data
     */
    public function getEnrollmentForOffering($offering_id)
    {
        $stmt = $this->db->prepare(
            'SELECT u.id, u.knownAs, u.studentID,
            u.firstname, u.lastname, u.email, u.teamsName,
            e.auth, e.group, e.id AS eid
            FROM enrollment e JOIN user u ON e.user_id = u.id
            WHERE offering_id = :offering_id
            ORDER BY u.firstname'
        );
        $stmt->execute(['offering_id' => $offering_id]);

        return $stmt->fetchAll();
    }

    /**
     * Returns the latest / last enrollment for a given user
     *
     * @param int user_id
     * @return single enrollment record (latest for user)
     */
    public function getEnrollmentForStudent($user_id)
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM enrollment 
            WHERE user_id = :user_id 
            ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute(['user_id' => $user_id]);

        return $stmt->fetch();
    }

    public function getStudentsForOffering($offering_id)
    {
        $stmt = $this->db->prepare(
            "SELECT u.id, u.knownAs, u.studentID, 
            u.firstname, u.lastname, u.email, u.teamsName
            FROM enrollment e JOIN user u ON e.user_id = u.id 
            WHERE offering_id = :offering_id
            AND e.auth = 'student' OR e.auth = 'assistant'"
        );
        $stmt->execute(['offering_id' => $offering_id]);
        $data = [];
        foreach ($stmt->fetchAll() as $row) {
            $data[$row['id']] = $row;
        }

        return $data;
    }

    public function getObserversForOffering($offering_id)
    {
        $stmt = $this->db->prepare(
            "SELECT u.id, u.knownAs, u.studentID, 
            u.firstname, u.lastname, u.email, u.teamsName
            FROM enrollment e JOIN user u ON e.user_id = u.id 
            WHERE offering_id = :offering_id
            AND e.auth = 'observer'"
        );
        $stmt->execute(['offering_id' => $offering_id]);
        $data = [];
        foreach ($stmt->fetchAll() as $row) {
            $data[$row['id']] = $row;
        }

        return $data;
    }

    public function getInstructorsForOfferings($offering_ids)
    {
        $inject = implode(',', $offering_ids);
        $stmt = $this->db->prepare(
            "SELECT e.offering_id, u.knownAs, u.lastname 
            FROM enrollment AS e 
            JOIN user AS u ON e.user_id = u.id
            WHERE e.auth = 'instructor'
            AND e.offering_id IN ({$inject})"
        );
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getTopInstructorFor($course, $block)
    {
        $stmt = $this->db->prepare(
            "SELECT u.firstname, u.lastname, u.teamsName, u.email 
            FROM enrollment AS e  
            JOIN offering AS o ON e.offering_id = o.id
            JOIN user AS u ON e.user_id = u.id
            WHERE e.auth = 'instructor'
            AND o.block = :block
            AND o.course_number = :course
            LIMIT 1"
        );
        $stmt->execute([
            'course' => $course,
            'block' => $block,
        ]);

        return $stmt->fetch();
    }

    public function deleteStudentEnrollment($offering_id)
    {
        $stmt = $this->db->prepare(
            "DELETE FROM enrollment 
            WHERE offering_id = :offering_id
            AND auth = 'student'"
        );
        $stmt->execute(['offering_id' => $offering_id]);
    }

    public function enroll($user_id, $offering_id, $auth)
    {
        $stmt = $this->db->prepare(
            'INSERT INTO enrollment 
            VALUES(NULL, :user_id, :offering_id, :auth, NULL)'
        );
        $stmt->execute([
            'user_id' => $user_id,
            'offering_id' => $offering_id,
            'auth' => $auth,
        ]);
    }

    public function unenroll($enrollment_id, $offering_id)
    {
        $stmt = $this->db->prepare(
            'DELETE FROM enrollment 
            WHERE id = :enrollment_id
            AND offering_id = :offering_id'
        );
        $stmt->execute(
            [
                'enrollment_id' => $enrollment_id,
                'offering_id' => $offering_id,
            ]
        );
    }

    public function update($user_id, $offering_id, $auth, $group)
    {
        if (! $group) {
            $group = null;
        }
        $stmt = $this->db->prepare(
            'UPDATE enrollment 
            SET auth = :auth, `group` = :group
            WHERE user_id = :user_id 
            AND offering_id = :offering_id'
        );
        $stmt->execute([
            'auth' => $auth,
            'group' => $group,
            'user_id' => $user_id,
            'offering_id' => $offering_id,
        ]);
    }

    public function checkEnrollmentAuth($user_id, $course, $block)
    {
        $select =
            'SELECT e.auth FROM enrollment AS e 
            JOIN offering AS o ON e.offering_id = o.id
            WHERE e.user_id = :user_id
            AND o.course_number = :course ';
        if ($block != 'none') {
            $select .= 'AND o.block = :block';
        }
        $stmt = $this->db->prepare($select);
        if ($block != 'none') {
            $stmt->execute([
                'user_id' => $user_id,
                'course' => $course,
                'block' => $block,
            ]);
        } else {
            $stmt->execute([
                'user_id' => $user_id,
                'course' => $course,
            ]);
        }

        return $stmt->fetch();
    }

    public function isEnrolled($user_id, $offering_id)
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM enrollment AS e 
            WHERE e.user_id = :user_id
            AND e.offering_id = :offering_id'
        );
        $stmt->execute([
            'user_id' => $user_id,
            'offering_id' => $offering_id,
        ]);

        return $stmt->fetch() != false;
    }

    public function getEnrollment($user_id, $course, $block)
    {
        $stmt = $this->db->prepare(
            'SELECT e.group, e.auth 
            FROM enrollment AS e 
            JOIN offering AS o ON e.offering_id = o.id
            WHERE e.user_id = :user_id
            AND o.course_number = :course
            AND o.block = :block'
        );
        $stmt->execute([
            'user_id' => $user_id,
            'course' => $course,
            'block' => $block,
        ]);

        return $stmt->fetch();
    }

    public function getGroup($user_id, $course, $block)
    {
        $stmt = $this->db->prepare(
            'SELECT e.group 
            FROM enrollment AS e 
            JOIN offering AS o ON e.offering_id = o.id
            WHERE e.user_id = :user_id
            AND o.course_number = :course
            AND o.block = :block'
        );
        $stmt->execute([
            'user_id' => $user_id,
            'course' => $course,
            'block' => $block,
        ]);
        $row = $stmt->fetch();
        if ($row) {
            return $row['group'];
        } else {
            return null;
        }
    }

    public function getGroups($offering_id)
    {
        $stmt = $this->db->prepare(
            'SELECT DISTINCT `group` 
            FROM enrollment 
            WHERE offering_id = :offering_id'
        );
        $stmt->execute(['offering_id' => $offering_id]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getMembers($offering_id, $group)
    {
        $stmt = $this->db->prepare(
            'SELECT u.id, u.knownAs, u.lastname 
            FROM enrollment AS e 
            JOIN user AS u ON e.user_id = u.id
            WHERE e.offering_id = :offering_id
            AND e.group = :group'
        );
        $stmt->execute([
            'offering_id' => $offering_id,
            'group' => $group,
        ]);

        return $stmt->fetchAll();
    }
}
