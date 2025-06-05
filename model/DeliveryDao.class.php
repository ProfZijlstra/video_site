<?php

/**
 * @author mzijlstra 14 Jan 2024
 */

#[Repository]
class DeliveryDao
{
    #[Inject('DB')]
    public $db;

    public function forSubmission($submission_id) : array|bool
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM delivery 
                WHERE submission_id = :submission_id"
        );
        $stmt->execute([
            "submission_id" => $submission_id
        ]);
        $rows = $stmt->fetchAll();
        $result = [];
        foreach ($rows as $row) {
            $result[$row['deliverable_id']] = $row;
        }
        return $result;
    }

    public function forDeliverable($deliverable_id) : array|bool
    {
        $stmt = $this->db->prepare(
            "SELECT d.id, d.created, d.updated, d.completion, d.duration, 
                d.text, d.hasMarkDown, d.file, d.name, 
                d.stuComment, d.stuCmntHasMD, 
                d.points, d.gradeComment, d.gradeCmntHasMD, 
                u.knownAs, u.lastname, s.group, s.id as submission_id
            FROM delivery AS d
                JOIN submission AS s ON d.submission_id = s.id
                LEFT JOIN user AS u ON d.user_id = u.id
                WHERE deliverable_id = :deliverable_id
            ORDER BY d.completion DESC, d.duration DESC"
        );
        $stmt->execute([
            "deliverable_id" => $deliverable_id
        ]);
        return $stmt->fetchAll();
    }

    public function byId($id) : array|bool
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM delivery 
                WHERE id = :id"
        );
        $stmt->execute([
            "id" => $id
        ]);
        return $stmt->fetch();
    }

    public function createTxt(
        $submission_id,
        $deliverable_id,
        $user_id,
        $completion,
        $duration,
        $text,
        $hasMarkDown,
        $stuComment,
        $stuCmntHasMD
    ) : int {
        // to prevent duplicate entries, check if it's already been made
        $stmt = $this->db->prepare(
            "SELECT * FROM delivery 
                WHERE submission_id = :submission_id 
                AND deliverable_id = :deliverable_id 
                AND user_id = :user_id"
        );
        $stmt->execute([
            "submission_id" => $submission_id,
            "deliverable_id" => $deliverable_id,
            "user_id" => $user_id
        ]);
        // update if exists
        $row = $stmt->fetch();
        if ($row) {
            $this->updateTxt(
                $row['id'],
                $user_id,
                $completion,
                $duration,
                $text,
                $hasMarkDown,
                $stuComment,
                $stuCmntHasMD
            );
            return -1;
        }
        $stmt = $this->db->prepare(
            "INSERT INTO delivery VALUES (
                NULL, :deliverable_id, :submission_id, :user_id,
                NOW(), NOW(), 
                :completion, :duration, 
                :text, :hasMarkDown, 
                NULL, NULL, 
                :stuComment, :stuCmntHasMD, 
                NULL, NULL, NULL)"
        );
        $stmt->execute([
            "submission_id" => $submission_id,
            "deliverable_id" => $deliverable_id,
            "user_id" => $user_id,
            "duration" => $duration,
            "completion" => $completion,
            "text" => $text,
            "hasMarkDown" => $hasMarkDown,
            "stuComment" => $stuComment,
            "stuCmntHasMD" => $stuCmntHasMD
        ]);
        return $this->db->lastInsertId();
    }

    public function updateTxt(
        $id,
        $user_id,
        $completion,
        $duration,
        $text,
        $hasMarkDown,
        $stuComment,
        $stuCmntHasMD
    ) : void {
        $stmt = $this->db->prepare(
            "UPDATE delivery 
                SET updated = NOW(),
                user_id = :user_id,
                completion = :completion, 
                duration = :duration, 
                text = :text, 
                hasMarkDown = :hasMarkDown,
                stuComment = :stuComment,
                stuCmntHasMD = :stuCmntHasMD
                WHERE id = :id"
        );
        $stmt->execute([
            "user_id" => $user_id,
            "completion" => $completion,
            "duration" => $duration,
            "text" => $text,
            "hasMarkDown" => $hasMarkDown,
            "id" => $id,
            "stuComment" => $stuComment,
            "stuCmntHasMD" => $stuCmntHasMD
        ]);
    }

    public function createUrl(
        $submission_id,
        $deliverable_id,
        $user_id,
        $completion,
        $duration,
        $url,
        $stuComment,
        $stuCmntHasMD
    ) : int {
        $stmt = $this->db->prepare(
            "SELECT * FROM delivery 
                WHERE submission_id = :submission_id 
                AND deliverable_id = :deliverable_id 
                AND user_id = :user_id"
        );
        $stmt->execute([
            "submission_id" => $submission_id,
            "deliverable_id" => $deliverable_id,
            "user_id" => $user_id
        ]);
        // update if exists
        $row = $stmt->fetch();
        if ($row) {
            $this->updateUrl(
                $row['id'],
                $user_id,
                $completion,
                $duration,
                $url,
                $stuComment,
                $stuCmntHasMD
            );
            return -1;
        }
        $stmt = $this->db->prepare(
            "INSERT INTO delivery VALUES (
                NULL, :deliverable_id, :submission_id, :user_id,
                NOW(), NOW(), 
                :completion, :duration, 
                :url, NULL, 
                NULL, NULL, 
                :stuComment, :stuCmntHasMD, 
                NULL, NULL, NULL)"
        );
        $stmt->execute([
            "submission_id" => $submission_id,
            "deliverable_id" => $deliverable_id,
            "user_id" => $user_id,
            "duration" => $duration,
            "completion" => $completion,
            "url" => $url,
            "stuComment" => $stuComment,
            "stuCmntHasMD" => $stuCmntHasMD
        ]);
        return $this->db->lastInsertId();
    }

    public function updateUrl(
        $id,
        $user_id,
        $completion,
        $duration,
        $url,
        $stuComment,
        $stuCmntHasMD
    ) :void {
        $stmt = $this->db->prepare(
            "UPDATE delivery 
                SET updated = NOW(),
                user_id = :user_id,
                completion = :completion, 
                duration = :duration, 
                text = :url, 
                stuComment = :stuComment,
                stuCmntHasMD = :stuCmntHasMD
                WHERE id = :id"
        );
        $stmt->execute([
            "user_id" => $user_id,
            "completion" => $completion,
            "duration" => $duration,
            "url" => $url,
            "id" => $id,
            "stuComment" => $stuComment,
            "stuCmntHasMD" => $stuCmntHasMD
        ]);
    }

    public function createFile(
        $submission_id,
        $deliverable_id,
        $user_id,
        $completion,
        $duration,
        $text,
        $file,
        $name,
        $stuComment,
        $stuCmntHasMD
    ) :int {
        // to prevent duplicate entries, check if it's already been made
        $stmt = $this->db->prepare(
            "SELECT * FROM delivery 
                WHERE submission_id = :submission_id 
                AND deliverable_id = :deliverable_id 
                AND user_id = :user_id"
        );
        $stmt->execute([
            "submission_id" => $submission_id,
            "deliverable_id" => $deliverable_id,
            "user_id" => $user_id
        ]);
        // update if exists
        $row = $stmt->fetch();
        if ($row) {
            $this->updateFile(
                $row['id'],
                $user_id,
                $completion,
                $duration,
                $text,
                $file,
                $name,
                $stuComment,
                $stuCmntHasMD
            );
            return -1;
        }
        $stmt = $this->db->prepare(
            "INSERT INTO delivery VALUES (
                NULL, :deliverable_id, :submission_id, :user_id,
                NOW(), NOW(), 
                :completion, :duration, 
                :text, 0, 
                :file, :name, 
                :stuComment, :stuCmntHasMD, 
                NULL, NULL, NULL)"
        );
        $stmt->execute([
            "submission_id" => $submission_id,
            "deliverable_id" => $deliverable_id,
            "user_id" => $user_id,
            "duration" => $duration,
            "completion" => $completion,
            "text" => $text,
            "file" => $file,
            "name" => $name,
            "stuComment" => $stuComment,
            "stuCmntHasMD" => $stuCmntHasMD
        ]);
        return $this->db->lastInsertId();
    }

    public function createFileStats(
        $submission_id, 
        $deliverable_id, 
        $user_id, 
        $completion, 
        $duration, 
        $stuComment, 
        $stuCmntHasMD) :int 
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM delivery 
                WHERE submission_id = :submission_id 
                AND deliverable_id = :deliverable_id 
                AND user_id = :user_id"
        );
        $stmt->execute([
            "submission_id" => $submission_id,
            "deliverable_id" => $deliverable_id,
            "user_id" => $user_id
        ]);
        // update if exists
        $row = $stmt->fetch();
        if ($row) {
            $this->updateFileStats(
                $row['id'],
                $completion,
                $duration,
                $stuComment,
                $stuCmntHasMD
            );
            return -1;
        }
        $stmt = $this->db->prepare(
            "INSERT INTO delivery VALUES (
                NULL, :deliverable_id, :submission_id, :user_id,
                NOW(), NOW(), 
                :completion, :duration, 
                NULL, 0, 
                NULL, NULL, 
                :stuComment, :stuCmntHasMD, 
                NULL, NULL, NULL)"
        );
        $stmt->execute([
            "submission_id" => $submission_id,
            "deliverable_id" => $deliverable_id,
            "user_id" => $user_id,
            "duration" => $duration,
            "completion" => $completion,
            "stuComment" => $stuComment,
            "stuCmntHasMD" => $stuCmntHasMD
        ]);
        return $this->db->lastInsertId();
    }

    public function updateFileStats(
        $id,
        $completion,
        $duration,
        $stuComment,
        $stuCmntHasMD
    ) : void {
        $stmt = $this->db->prepare(
            "UPDATE delivery 
                SET updated = NOW(),
                completion = :completion, 
                duration = :duration, 
                stuComment = :stuComment,
                stuCmntHasMD = :stuCmntHasMD
                WHERE id = :id"
        );
        $stmt->execute([
            "completion" => $completion,
            "duration" => $duration,
            "id" => $id,
            "stuComment" => $stuComment,
            "stuCmntHasMD" => $stuCmntHasMD
        ]);
    }

    public function updateFile(
        $id,
        $user_id,
        $completion,
        $duration,
        $text,
        $file,
        $name,
        $stuComment,
        $stuCmntHasMD
    ) : void {
        $stmt = $this->db->prepare(
            "UPDATE delivery 
                SET updated = NOW(),
                user_id = :user_id,
                completion = :completion, 
                duration = :duration, 
                `text` = :text,
                `file` = :file, 
                `name` = :name,
                stuComment = :stuComment,
                stuCmntHasMD = :stuCmntHasMD
                WHERE id = :id"
        );
        $stmt->execute([
            "user_id" => $user_id,
            "completion" => $completion,
            "duration" => $duration,
            "text" => $text,
            "file" => $file,
            "name" => $name,
            "id" => $id,
            "stuComment" => $stuComment,
            "stuCmntHasMD" => $stuCmntHasMD
        ]);
    }

    public function createPicture(
        $deliverable_id,
        $submission_id,
        $user_id,
        $file,
        $name,
    ) : int {
        $stmt = $this->db->prepare(
            "SELECT * FROM delivery 
                WHERE submission_id = :submission_id 
                AND deliverable_id = :deliverable_id 
                AND user_id = :user_id"
        );
        $stmt->execute([
            "submission_id" => $submission_id,
            "deliverable_id" => $deliverable_id,
            "user_id" => $user_id
        ]);
        // update if exists
        $row = $stmt->fetch();
        if ($row) {
            $this->updatePicture(
                $row['id'],
                $file,
                $name
            );
            return -1;
        }
        $stmt = $this->db->prepare(
            "INSERT INTO delivery VALUES (
                NULL, :deliverable_id, :submission_id, :user_id,
                NOW(), NOW(), 
                0, '00:00:00', 
                '', 0, 
                :file, :name, 
                '', 0, 
                NULL, NULL, NULL)"
        );
        $stmt->execute([
            "deliverable_id" => $deliverable_id,
            "submission_id" => $submission_id,
            "user_id" => $user_id,
            "file" => $file,
            "name" => $name,
        ]);
        return $this->db->lastInsertId();
    }

    public function updatePicture($id, $file, $name) : void
    {
        $stmt = $this->db->prepare(
            "UPDATE delivery 
                SET updated = NOW(),
                `file` = :file, 
                `name` = :name
                WHERE id = :id"
        );
        $stmt->execute([
            "file" => $file,
            "name" => $name,
            "id" => $id
        ]);
    }

    public function grade($id, $points, $comment, $hasMarkDown) : void
    {
        $stmt = $this->db->prepare(
            "UPDATE delivery 
                SET points = :points, 
                gradeComment = :comment,
                gradeCmntHasMD = :hasMarkDown
                WHERE id = :id"
        );
        $stmt->execute([
            "points" => $points,
            "comment" => $comment,
            "hasMarkDown" => $hasMarkDown,
            "id" => $id
        ]);
    }


    public function createGrade(
        $submission_id,
        $deliverable_id,
        $points,
        $comment,
        $hasMarkDown
    ) : int {
        $stmt = $this->db->prepare(
            "SELECT * FROM delivery 
                WHERE submission_id = :submission_id 
                AND deliverable_id = :deliverable_id "
        );
        $stmt->execute([
            "submission_id" => $submission_id,
            "deliverable_id" => $deliverable_id,
        ]);
        // update if exists
        $row = $stmt->fetch();
        if ($row) {
            $this->grade(
                $row['id'],
                $points,
                $comment,
                $hasMarkDown
            );
            return -1;
        }
        $stmt = $this->db->prepare(
            "INSERT INTO delivery VALUES (
                NULL, :deliverable_id, :submission_id, NULL,
                NOW(), NOW(), 
                0, '00:00:00',
                NULL, 0, 
                NULL, NULL,
                NULL, NULL,
                :points, :comment, :hasMarkDown)"
        );
        $stmt->execute([
            "submission_id" => $submission_id,
            "deliverable_id" => $deliverable_id,
            "points" => $points,
            "comment" => $comment,
            "hasMarkDown" => $hasMarkDown
        ]);
        return $this->db->lastInsertId();
    }

    function delete($id) : void
    {
        $stmt = $this->db->prepare(
            'UPDATE delivery 
            SET file = null, text = null, name = null
            WHERE id = :id'
        );
        $stmt->execute([
            "id" => $id
        ]);
    }

    /**
    * The functions below are all to retrieve data for the lab statistics pages
    */
    function offeringPossible($offering_id) : array
    {
        $stmt = $this->db->prepare(
            "SELECT d.abbr,
                SUM(del.points) AS points
            FROM deliverable AS del
            JOIN lab AS l ON del.lab_id = l.id
            JOIN day AS d ON l.day_id = d.id
            WHERE d.offering_id = :offering_id
            GROUP BY d.id"
        );
        $stmt->execute([
            "offering_id" => $offering_id
        ]);

        $data = [];
        foreach ($stmt->fetchAll() as $row) {
            $data[$row['abbr']] = $row;
        }

        return $data;
    }

    function offeringAverages($offering_id) : array
    {
        $stmt = $this->db->prepare(
            "SELECT d.abbr,
                COUNT(DISTINCT s.id) AS users,
                SUM(del.points) / COUNT(DISTINCT s.id) AS points
            FROM delivery AS del
            JOIN submission AS s ON del.submission_id = s.id
            JOIN lab AS l ON s.lab_id = l.id
            JOIN day AS d ON l.day_id = d.id
            WHERE d.offering_id = :offering_id
            GROUP BY d.id"
        );
        $stmt->execute([
            "offering_id" => $offering_id
        ]);

        $data = [];
        foreach ($stmt->fetchAll() as $row) {
            $data[$row['abbr']] = $row;
        }

        return $data;
    }

    function offeringPerson($offering_id, $user_id)
    {
        // get indivual points per day for a user
        $stmt = $this->db->prepare(
            "SELECT d.abbr,
                SUM(del.points) AS points
            FROM delivery AS del
            JOIN submission AS s ON del.submission_id = s.id
            JOIN lab AS l ON s.lab_id = l.id
            JOIN day AS d ON l.day_id = d.id
            WHERE d.offering_id = :offering_id
            AND s.user_id = :user_id
            AND l.type = 'individual'
            GROUP BY d.id"
        );
        $stmt->execute([
            "offering_id" => $offering_id,
            "user_id" => $user_id
        ]);

        $data = [];
        foreach ($stmt->fetchAll() as $row) {
            $data[$row['abbr']] = $row;
        }

        // get the person's group
        $stmt = $this->db->prepare(
            "SELECT e.group 
            FROM enrollment AS e
            WHERE e.offering_id = :offering_id
            AND e.user_id = :user_id"
        );
        $stmt->execute([
            "offering_id" => $offering_id,
            "user_id" => $user_id
        ]);
        $group = $stmt->fetchColumn();

        if (!$group) {
            return $data; // no group found, return only individual points
        }

        // get group points per day for user
        $stmt = $this->db->prepare(
            "SELECT d.abbr,
                SUM(del.points) AS points
            FROM delivery AS del
            JOIN submission AS s ON del.submission_id = s.id
            JOIN lab AS l ON s.lab_id = l.id
            JOIN day AS d ON l.day_id = d.id
            WHERE d.offering_id = :offering_id
            AND s.group = :group
            AND l.type = 'group'
            GROUP BY d.id"
        );
        $stmt->execute([
            "offering_id" => $offering_id,
            "group" => $group
        ]);

        // merge individual and group points
        foreach ($stmt->fetchAll() as $row) {
            if (isset($data[$row['abbr']])) {
                $data[$row['abbr']]['points'] += $row['points'];
            } else {
                $data[$row['abbr']] = $row; // add new entry for group points
            }
        }

        return $data;
    }

    function offeringUsers($offering_id) 
    {
        // get all the students
        $stmt = $this->db->prepare(
            "SELECT e.user_id, e.group 
            FROM enrollment AS e
            WHERE (e.auth = 'student' OR e.auth = 'assistant')
            AND e.offering_id = :offering_id"
        );
        $stmt->execute(['offering_id' => $offering_id]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // create an associative array with user_id to points
        $userPoints = [];
        foreach ($users as $user) {
            $userPoints[$user['user_id']] = 0;
        }

        // get all deliveries along with user_id, group_id, lab_type
        $stmt = $this->db->prepare(
            "SELECT s.user_id, s.group, del.points, l.type
            FROM delivery AS del
            JOIN submission AS s ON del.submission_id = s.id
            JOIN lab AS l ON s.lab_id = l.id
            JOIN day AS d ON l.day_id = d.id
            WHERE d.offering_id = :offering_id"
        );
        $stmt->execute(['offering_id' => $offering_id]);
        $deliveries = $stmt->fetchAll();

        // if lab_type is group then apply to all users in group
        // else only apply add the points to the user
        foreach ($deliveries as $delivery) {
            if ($delivery['type'] == 'group') {
                // add points to all users in the group
                foreach ($users as $user) {
                    if ($user['group'] == $delivery['group']) {
                        $userPoints[$user['user_id']] += $delivery['points'];
                    }
                }
            } else {
                // add points to the user
                $userPoints[$delivery['user_id']] += $delivery['points'];
            }
        }
        arsort($userPoints);

        return $userPoints;
    }

    function dayUsers($offering_id, $day) : array
    {
        // get all the students
        $stmt = $this->db->prepare(
            "SELECT e.user_id, e.group
            FROM enrollment AS e
            WHERE (e.auth = 'student' OR e.auth = 'assistant')
            AND e.offering_id = :offering_id"
        );
        $stmt->execute(['offering_id' => $offering_id]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // create an associative array with user_id to points
        $userPoints = [];
        foreach ($users as $user) {
            $userPoints[$user['user_id']] = 0;
        }

        // get all deliveries along with user_id, group_id, lab_type
        $stmt = $this->db->prepare(
            "SELECT s.user_id, s.group, del.points, l.type
            FROM delivery AS del
            JOIN submission AS s ON del.submission_id = s.id
            JOIN lab AS l ON s.lab_id = l.id
            JOIN day AS d ON l.day_id = d.id
            WHERE d.offering_id = :offering_id
            AND d.abbr = :day_abbr"
        );
        $stmt->execute(['offering_id' => $offering_id, 'day_abbr' => $day]);
        $deliveries = $stmt->fetchAll();

        // if lab_type is group then apply to all users in group
        // else only apply add the points to the user
        foreach ($deliveries as $delivery) {
            if ($delivery['type'] == 'group') {
                // add points to all users in the group
                foreach ($users as $user) {
                    if ($user['group'] == $delivery['group']) {
                        $userPoints[$user['user_id']] += $delivery['points'];
                    }
                }
            } else {
                // add points to the user
                $userPoints[$delivery['user_id']] += $delivery['points'];
            }
        }
        arsort($userPoints);

        return $userPoints;
    }
}
