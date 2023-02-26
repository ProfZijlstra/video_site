<?php

/**
 * @author mzijlstra 08/17/2022
 * @Repository
 */

class QuestionDao {
    /**
     * @Inject("DB")
     */
    public $db;

    public function add($quiz_id, $type, $text, $model_answer, $points, $seq) {
        $stmt = $this->db->prepare(
			"INSERT INTO question 
			VALUES(NULL, :quiz_id, :text, :model_answer, :points, :seq, :type)"
		);
		$stmt->execute(array(
            "quiz_id" => $quiz_id,
            "type" => $type,
            "text" => $text,
            "model_answer" => $model_answer,
            "points" => $points,
            "seq" => $seq
		));
		return $this->db->lastInsertId();
    }

    public function forQuiz($quiz_id) {
        $stmt = $this->db->prepare(
            "SELECT * FROM question
            WHERE quiz_id = :quiz_id
            ORDER BY seq");
        $stmt->execute(array("quiz_id" => $quiz_id));
        return $stmt->fetchAll();
    }

    public function get($id) {
        $stmt = $this->db->prepare(
            "SELECT * FROM question
            WHERE id = :id ");
        $stmt->execute(array("id" => $id));
        return $stmt->fetch();
    }

    public function update($id, $text, $model_answer, $points) {
        $stmt = $this->db->prepare(
			"UPDATE question 
            SET `text` = :text, modelAnswer = :model_answer, points = :points
            WHERE id = :id "
		);
		$stmt->execute(array(
            "id" =>  $id, 
            "text" => $text, 
            "model_answer" => $model_answer,
            "points" => $points
        ));
    }

    public function updateModelAnswer($id, $model_answer) {
        $stmt = $this->db->prepare(
			"UPDATE question 
            SET `modelAnswer` = :model_answer
            WHERE id = :id "
		);
		$stmt->execute(array(
            "id" =>  $id, 
            "model_answer" => $model_answer,
        ));
    }

    public function delete($id) {
        $stmt = $this->db->prepare(
            "SELECT * FROM question 
            WHERE id = :id");
		$stmt->execute(array( "id" =>  $id ));
        $question = $stmt->fetch();

        $stmt = $this->db->prepare(
			"DELETE FROM question
            WHERE id = :id "
		);
		$stmt->execute(array( "id" =>  $id ));

        $stmt = $this->db->prepare(
            "UPDATE question SET seq = seq - 1
            WHERE quiz_id = :quiz_id 
            AND seq > :seq");
        $stmt->execute([
            "seq" => $question['seq'], 
            "quiz_id" => $question['id']]);
    }

    /**
     * Clones all questions for a quiz, adding them to the new quiz
     */
    public function clone($quiz_id, $new_quiz_id) {
        $stmt = $this->db->prepare(
            "SELECT * FROM question 
            WHERE quiz_id = :quiz_id");
		$stmt->execute(array( "quiz_id" =>  $quiz_id ));
        $questions = $stmt->fetchAll();

        $stmt = $this->db->prepare(
			"INSERT INTO question 
			VALUES(NULL, :quiz_id, :text, :model_answer, :points, :seq, :type)"
		);
        foreach ($questions as $question) {
            $stmt->execute(array(
                "quiz_id" => $new_quiz_id,
                "text" => $question['text'],
                "model_answer" => $question['model_answer'],
                "points" => $question['points'],
                "seq" => $question['seq'],
                "type" => $question['type']
            ));    
        }
    }
}
?>