<?php

/**
 * @author mzijlstra 08/21/2022
 */
#[Repository]
class AnswerDao
{
    #[Inject('DB')]
    public $db;

    public function byId($id)
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM answer WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);

        return $stmt->fetch();
    }

    public function forQuiz($quiz_id)
    {
        $stmt = $this->db->prepare(
            'SELECT a.id
            FROM answer AS a
            JOIN question AS q ON a.question_id = q.id
            WHERE q.quiz_id = :quiz_id'
        );
        $stmt->execute(['quiz_id' => $quiz_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function add($text, $question_id, $user_id, $hasMarkDown)
    {
        $stmt = $this->db->prepare(
            'SELECT id FROM answer 
            WHERE question_id = :question_id
            AND user_id = :user_id
        ');
        $stmt->execute([
            'question_id' => $question_id,
            'user_id' => $user_id,
        ]);
        $row = $stmt->fetch();
        if ($row) {
            return $row['id'];
        }

        $stmt = $this->db->prepare(
            'INSERT INTO answer 
			VALUES(NULL, :text, :question_id, :user_id, 
            NOW(), NULL, NULL, NULL, :hasMarkDown, 0)'
        );
        $stmt->execute([
            'text' => $text,
            'question_id' => $question_id,
            'user_id' => $user_id,
            'hasMarkDown' => $hasMarkDown,
        ]);

        return $this->db->lastInsertId();
    }

    public function update($id, $text, $user_id, $hasMarkDown)
    {
        $stmt = $this->db->prepare(
            'UPDATE answer 
            SET `text` = :text,
            `hasMarkDown` = :hasMarkDown, 
            `updated` = NOW()
            WHERE id = :id 
            AND user_id = :user_id '
        );
        $stmt->execute([
            'id' => $id,
            'text' => $text,
            'user_id' => $user_id,
            'hasMarkDown' => $hasMarkDown,
        ]);
    }

    public function forQuestion($question_id)
    {
        $stmt = $this->db->prepare(
            'SELECT a.id, a.text, a.user_id, a.points, a.comment, a.cmntHasMD,
                u.knownAs, u.lastname,
                a.created, a.updated,
                a.hasMarkDown
            FROM answer AS a
            JOIN user AS u ON a.user_id = u.id
            WHERE a.question_id = :question_id
            ORDER BY a.text, a.created '
        );
        $stmt->execute(['question_id' => $question_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function grade($answer_ids, $points, $comment, $cmntHasMD)
    {
        $stmt = $this->db->prepare(
            "UPDATE answer 
            SET `points` = :points, `comment` = :comment, cmntHasMD = :cmntHasMD
            WHERE id IN ({$answer_ids})"
        );
        $stmt->execute([
            'points' => $points,
            'comment' => $comment,
            'cmntHasMD' => $cmntHasMD,
        ]);
    }

    public function gradeUnanswered($user_id, $question_id, $comment, $points, $cmntHasMD)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO answer 
			VALUES(NULL, '', :question_id, :user_id, NOW(), NULL, 
                :points, :comment, 0, :cmntHasMD)"
        );
        $stmt->execute([
            'question_id' => $question_id,
            'user_id' => $user_id,
            'comment' => $comment,
            'points' => $points,
            'cmntHasMD' => $cmntHasMD,
        ]);

        return $this->db->lastInsertId();
    }

    public function forUser($user_id, $quiz_id)
    {
        $stmt = $this->db->prepare(
            'SELECT a.*
            FROM answer AS a
            JOIN question AS q on a.question_id = q.id
            WHERE q.quiz_id = :quiz_id
            AND a.user_id = :user_id
            ORDER BY q.seq '
        );
        $stmt->execute(['user_id' => $user_id, 'quiz_id' => $quiz_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = [];
        foreach ($rows as $row) {
            $result[$row['question_id']] = $row;
        }

        return $result;
    }

    public function overview($quiz_id)
    {
        $stmt = $this->db->prepare(
            'SELECT u.id, u.knownAs, u.firstname, u.lastname, 
                count(a.id) AS answers, 
                sum(a.points) AS points,
                count(a2.id) AS ungraded
            FROM answer AS a 
            JOIN user AS u ON a.user_id = u.id
            JOIN question AS q ON a.question_id = q.id
                AND q.quiz_id = :quiz_id 
            LEFT JOIN answer AS a2 ON a.id = a2.id 
                AND a2.points IS NULL
            GROUP BY a.user_id '
        );
        $stmt->execute([
            'quiz_id' => $quiz_id,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare(
            'DELETE FROM answer WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
    }

    /**
     * The functions below are all to retrieve data for the quiz statistics pages
     */
    public function offeringPossible($offering_id): array
    {
        $stmt = $this->db->prepare(
            'SELECT d.abbr,
                SUM(quest.points) AS points
            FROM question AS quest
            JOIN quiz AS q ON quest.quiz_id = q.id
            JOIN day AS d ON q.day_id = d.id
            WHERE d.offering_id = :offering_id
            GROUP BY d.id'
        );
        $stmt->execute([
            'offering_id' => $offering_id,
        ]);

        $data = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $data[$row['abbr']] = $row;
        }

        return $data;
    }

    public function offeringAverages($offering_id): array
    {
        $stmt = $this->db->prepare(
            'SELECT d.abbr,
                COUNT(DISTINCT ans.user_id) AS users,
                SUM(ans.points) / COUNT(DISTINCT ans.user_id) AS points
            FROM answer AS ans
            JOIN question AS quest ON ans.question_id = quest.id
            JOIN quiz AS q ON quest.quiz_id = q.id
            JOIN day AS d ON q.day_id = d.id
            WHERE d.offering_id = :offering_id
            GROUP BY d.id'
        );
        $stmt->execute([
            'offering_id' => $offering_id,
        ]);

        $data = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $data[$row['abbr']] = $row;
        }

        return $data;
    }

    public function offeringPerson($offering_id, $user_id)
    {
        $stmt = $this->db->prepare(
            'SELECT d.abbr,
                SUM(ans.points) AS points
            FROM answer AS ans
            JOIN question AS quest ON ans.question_id = quest.id
            JOIN quiz AS q ON quest.quiz_id = q.id
            JOIN day AS d ON q.day_id = d.id
            WHERE d.offering_id = :offering_id
            AND ans.user_id = :user_id
            GROUP BY d.id'
        );
        $stmt->execute([
            'offering_id' => $offering_id,
            'user_id' => $user_id,
        ]);

        $data = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $data[$row['abbr']] = $row;
        }

        return $data;
    }

    public function offeringUsers($offering_id)
    {
        $stmt = $this->db->prepare(
            "SELECT ans.user_id, SUM(ans.points) AS points
            FROM answer AS ans
            JOIN question AS quest ON ans.question_id = quest.id
            JOIN quiz AS q ON quest.quiz_id = q.id
            JOIN day AS d ON q.day_id = d.id
            JOIN enrollment AS e ON ans.user_id = e.user_id
            WHERE (e.auth = 'student' OR e.auth = 'assistant')
            AND e.offering_id = :offering_id
            AND d.offering_id = :offering_id
            GROUP BY ans.user_id
            ORDER BY points DESC"
        );
        $stmt->execute(['offering_id' => $offering_id]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // create an associative array with user_id to points
        $userPoints = [];
        foreach ($users as $user) {
            $userPoints[$user['user_id']] = $user['points'];
        }

        return $userPoints;
    }

    public function dayUsers($offering_id, $day): array
    {
        $stmt = $this->db->prepare(
            "SELECT ans.user_id, SUM(ans.points) AS points
            FROM answer AS ans
            JOIN question AS quest ON ans.question_id = quest.id
            JOIN quiz AS q ON quest.quiz_id = q.id
            JOIN day AS d ON q.day_id = d.id
            JOIN enrollment AS e ON ans.user_id = e.user_id
            WHERE (e.auth = 'student' OR e.auth = 'assistant')
            AND e.offering_id = :offering_id
            AND d.offering_id = :offering_id
            AND d.abbr = :day
            GROUP BY ans.user_id
            ORDER BY points DESC"
        );
        $stmt->execute(['offering_id' => $offering_id, 'day' => $day]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // create an associative array with user_id to points
        $userPoints = [];
        foreach ($users as $user) {
            $userPoints[$user['user_id']] = $user['points'];
        }

        return $userPoints;
    }
}
