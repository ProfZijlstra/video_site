<?php

/**
 * @author mzijlstra 08/17/2022
 */

#[Repository]
class QuizDao
{
    #[Inject('DB')]
    public $db;

    #[Inject('QuestionDao')]
    public $questionDao;


    public function allForOffering($offering_id)
    {
        $stmt = $this->db->prepare(
            "SELECT q.id, q.name, q.visible, d.abbr
            FROM quiz AS q
            JOIN day AS d ON q.day_id = d.id
            JOIN offering AS o ON d.offering_id = o.id
            WHERE o.id = :offering_id
            AND o.active = 1"
        );
        $stmt->execute(array("offering_id" => $offering_id));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function visibleForOffering($offering_id)
    {
        $stmt = $this->db->prepare(
            "SELECT q.id, q.name, q.visible, d.abbr
            FROM quiz AS q
            JOIN day AS d ON q.day_id = d.id
            JOIN offering AS o ON d.offering_id = o.id
            WHERE o.id = :offering_id 
            AND q.visible = 1
            AND o.active = 1"
        );
        $stmt->execute(array("offering_id" => $offering_id));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function getInstructorGradingStatus($offering_id)
    {
        $stmt = $this->db->prepare("
            SELECT q.id, count(a1.id) AS answers, count(a2.id) AS ungraded
            FROM quiz AS q
            JOIN day AS d ON q.day_id = d.id
            JOIN offering AS o ON d.offering_id = o.id
            JOIN question qu ON q.id = qu.quiz_id
            LEFT JOIN answer a1 ON qu.id = a1.question_id
            LEFT JOIN answer a2 ON a1.id = a2.id AND a2.points IS NULL
            WHERE o.id = :offering_id
            AND o.active = 1
            GROUP BY q.id
            ");
        $stmt->execute(array(
            "offering_id" => $offering_id,
        ));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function getStudentGradingStatus($offering_id, $user_id)
    {
        $stmt = $this->db->prepare("
            SELECT q.id, count(a1.id) AS answers, count(a2.id) AS ungraded
            FROM quiz AS q
            JOIN day AS d ON q.day_id = d.id
            JOIN offering AS o ON d.offering_id = o.id
            JOIN question qu ON q.id = qu.quiz_id
            LEFT JOIN answer a1 ON qu.id = a1.question_id
            LEFT JOIN answer a2 ON a1.id = a2.id AND a2.points IS NULL
            WHERE o.id = :offering_id
            AND o.active = 1
            AND a1.user_id = :user_id
            GROUP BY q.id
            ");
        $stmt->execute(array(
            "offering_id" => $offering_id,
            "user_id" => $user_id
        ));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function add($name, $day_id, $start, $stop)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO quiz 
			VALUES(NULL, :name, :day_id, :start, :stop, 0)"
        );
        $stmt->execute(array(
            "name" => $name,
            "day_id" => $day_id,
            "start" => $start,
            "stop" => $stop
        ));
        return $this->db->lastInsertId();
    }

    public function byId($id)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM quiz
            WHERE id = :id"
        );
        $stmt->execute(array("id" => $id));
        return $stmt->fetch();
    }

    public function setStatus($id, $visible)
    {
        $stmt = $this->db->prepare(
            "UPDATE quiz 
            SET `visible` = :visible
            WHERE id = :id"
        );
        $stmt->execute(array(
            "id" =>  $id,
            "visible" => $visible,
        ));
    }

    public function update($id, $day_id, $name, $start, $stop)
    {
        $stmt = $this->db->prepare(
            "UPDATE quiz 
            SET day_id = :day_id, `name` = :name, `start` = :start, `stop` = :stop
            WHERE id = :id"
        );
        $stmt->execute(array(
            "id" =>  $id,
            "day_id" => $day_id,
            "name" => $name,
            "start" => $start,
            "stop" => $stop
        ));
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare(
            "DELETE FROM quiz 
            WHERE id = :id"
        );
        $stmt->execute(array("id" =>  $id));
    }

    /**
     * Clones all quizzes for an offering (which is being cloned)
     */
    public function clone($offering_id, $new_offering_id)
    {
        // find difference in days between the two offerings
        $inject = "{$offering_id}, {$new_offering_id}";
        $stmt = $this->db->prepare(
            "SELECT id, `start` FROM offering
            WHERE id IN ({$inject})
            ORDER BY `start`"
        );
        $stmt->execute();
        $dates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $earlier = new DateTime(substr($dates[0]['start'], 0, 10));
        $later = new DateTime(substr($dates[1]['start'], 0, 10));
        $daysDiff = $earlier->diff($later)->format("%r%a");
        $interval = new DateInterval("P{$daysDiff}D");

        // create a lookup table for abbr to new day id
        $stmt = $this->db->prepare(
            "SELECT id, abbr FROM day
            WHERE offering_id = :offering_id"
        );
        $stmt->execute(array("offering_id" => $new_offering_id));
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $days = [];
        foreach ($rows as $row) {
            $days[$row['abbr']] = $row['id'];
        }

        // get all the old quizzes
        $stmt = $this->db->prepare(
            "SELECT q.id, q.name, q.start, q.stop, d.abbr FROM quiz AS q
            JOIN `day` AS d on q.day_id = d.id
            WHERE d.offering_id = :offering_id"
        );
        $stmt->execute(array("offering_id" => $offering_id));
        $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // create a clone for each on the same day in the new offering
        $stmt = $this->db->prepare(
            "INSERT INTO quiz 
			VALUES(NULL, :name, :day_id, :start, :stop, :visible)"
        );
        foreach ($quizzes as $quiz) {
            // move start date by date difference between offerings
            $start = new DateTime($quiz['start']);
            $stop = new DateTime($quiz['stop']);
            $start->add($interval);
            $stop->add($interval);

            $stmt->execute(array(
                "name" => $quiz['name'],
                "day_id" => $days[$quiz['abbr']],
                "start" => $start->format("Y-m-d H:i:s"),
                "stop" => $stop->format("Y-m-d H:i:s"),
                "visible" => $quiz['visible'] ? 1 : 0,
            ));
            $new_quiz_id = $this->db->lastInsertId();

            // also clone the questions for each quiz
            $this->questionDao->clone($quiz['id'], $new_quiz_id);
        }
    }

    public function getQuizTotalsForEnrolled($quiz_id, $offering_id)
    {
        $stmt = $this->db->prepare(
            "SELECT e.user_id, ifnull(sum(a.points), 0) AS points
            FROM enrollment AS e 
            JOIN offering AS o ON e.offering_id = o.id
            JOIN `day` AS d ON o.id = d.offering_id
            JOIN quiz AS q ON d.id = q.day_id 
                AND q.id = :quiz_id
            JOIN question AS qu ON q.id = qu.quiz_id
            LEFT JOIN answer AS a ON qu.id = a.question_id
                AND a.user_id = e.user_id
            WHERE e.offering_id = :offering_id
            GROUP BY e.user_id "
        );
        $stmt->execute(array(
            "quiz_id" => $quiz_id,
            "offering_id" => $offering_id
        ));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
