<?php

/**
 * @author mzijlstra 08/21/2022
 * @Repository
 */

class AnswerDao {
    /**
     * @Inject("DB")
     */
    public $db;

    public function add($text, $question_id, $user_id, $hasMarkDown) {
        $stmt = $this->db->prepare(
			"INSERT INTO answer 
			VALUES(NULL, :text, :question_id, :user_id, 
            NOW(), NULL, NULL, NULL, :hasMarkDown)"
		);
		$stmt->execute(array(
            "text" => $text,
            "question_id" => $question_id,
            "user_id" => $user_id,
            "hasMarkDown" => $hasMarkDown,
		));
		return $this->db->lastInsertId();
    }

    public function update($id, $text, $user_id, $hasMarkDown) {
        $stmt = $this->db->prepare(
			"UPDATE answer 
            SET `text` = :text,
            `hasMarkDown` = :hasMarkDown, 
            `updated` = NOW()
            WHERE id = :id 
            AND user_id = :user_id "
		);
		$stmt->execute(array(
            "id" =>  $id, 
            "text" => $text, 
            "user_id" => $user_id,
            "hasMarkDown" => $hasMarkDown,
        ));
    }

    public function forQuestion($question_id) {
        $this->db->exec("SET time_zone = '" . TIMEZONE . "'");
        $stmt = $this->db->prepare(
			"SELECT a.id, a.text, a.user_id, a.points, a.comment,
                u.knownAs, u.lastname,
                a.created, a.updated,
                a.hasMarkDown
            FROM answer AS a
            JOIN user AS u ON a.user_id = u.id
            WHERE a.question_id = :question_id
            ORDER BY a.text, a.created "
		);
		$stmt->execute(array("question_id" => $question_id));
		return $stmt->fetchAll();
    }

    public function grade($answer_ids, $points, $comment) {
        $stmt = $this->db->prepare(
            "UPDATE answer 
            SET `points` = :points, `comment` = :comment
            WHERE id IN ({$answer_ids})"
        );
		$stmt->execute(array(
            "points" => $points,
            "comment" => $comment
        ));
    }

    public function gradeUnanswered($user_id, $question_id, $comment, $points) {
        $stmt = $this->db->prepare(
			"INSERT INTO answer 
			VALUES(NULL, '', :question_id, :user_id, NOW(), NULL, 
                :points, :comment, 0)"
		);
		$stmt->execute(array(
            "question_id" => $question_id,
            "user_id" => $user_id,
            "comment" => $comment,
            "points" => $points
		));
		return $this->db->lastInsertId();
    }

    public function forUser($user_id, $quiz_id) {
        $this->db->exec("SET time_zone = '" . TIMEZONE . "'");
        $stmt = $this->db->prepare(
			"SELECT a.text, a.id, a.question_id, 
                a.points, a.comment,
                a.created, a.updated,
                a.hasMarkDown
            FROM answer AS a
            JOIN question AS q on a.question_id = q.id
            WHERE q.quiz_id = :quiz_id
            AND a.user_id = :user_id
            ORDER BY q.seq "
		);
		$stmt->execute(array("user_id" => $user_id, "quiz_id" => $quiz_id));
		$rows = $stmt->fetchAll();
        $result = [];
        foreach ($rows as $row) {
            $result[$row['question_id']] = $row;
        }
        return $result;
    }

    public function overview($quiz_id) {
        $stmt = $this->db->prepare(
			"SELECT u.id, u.knownAs, u.firstname, u.lastname, 
                count(a.id) AS answers, 
                sum(a.points) AS points
            FROM answer AS a 
            JOIN user AS u ON a.user_id = u.id
            JOIN question AS q ON a.question_id = q.id
            AND q.quiz_id = :quiz_id 
            GROUP BY a.user_id "
		);
		$stmt->execute(array(
            "quiz_id" => $quiz_id
        ));
		return $stmt->fetchAll();
    }
}

?>