<?php

/**
 * @author mzijlstra 08/17/2022
 */
#[Repository]
class QuestionDao
{
    #[Inject('DB')]
    public $db;

    public function add($quiz_id, $type, $text, $modelAnswer, $points, $seq)
    {
        $stmt = $this->db->prepare(
            'INSERT INTO question 
			VALUES(NULL, :quiz_id, :text, :modelAnswer, :points, :seq, :type,
                    1, 0)'
        );
        $stmt->execute([
            'quiz_id' => $quiz_id,
            'type' => $type,
            'text' => $text,
            'modelAnswer' => $modelAnswer,
            'points' => $points,
            'seq' => $seq,
        ]);

        return $this->db->lastInsertId();
    }

    public function forQuiz($quiz_id)
    {
        $stmt = $this->db->prepare('
            SELECT q.*, COUNT(a.id) AS answers, COUNT(a2.id) AS ungraded,
            AVG(a.points) AS avgPoints
            FROM question AS q
            LEFT JOIN answer AS a ON q.id = a.question_id
            LEFT JOIN answer AS a2 ON a.id = a2.id AND a2.points IS NULL
            WHERE q.quiz_id = :quiz_id
            GROUP BY q.id
            ORDER BY seq
        ');
        $stmt->execute(['quiz_id' => $quiz_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get($id)
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM question
            WHERE id = :id '
        );
        $stmt->execute(['id' => $id]);

        return $stmt->fetch();
    }

    public function update(
        $id,
        $text,
        $modelAnswer,
        $points,
        $hasMarkDown,
        $mdlAnsHasMD
    ) {
        $stmt = $this->db->prepare(
            'UPDATE question 
            SET `text` = :text, 
                modelAnswer = :modelAnswer, 
                points = :points,
                hasMarkDown = :hasMarkDown,
                mdlAnsHasMD = :mdlAnsHasMD
            WHERE id = :id '
        );
        $stmt->execute([
            'id' => $id,
            'text' => $text,
            'modelAnswer' => $modelAnswer,
            'points' => $points,
            'hasMarkDown' => $hasMarkDown,
            'mdlAnsHasMD' => $mdlAnsHasMD,
        ]);
    }

    public function updateModelAnswer($id, $modelAnswer, $mdlAnsHasMD)
    {
        $stmt = $this->db->prepare(
            'UPDATE question 
            SET `modelAnswer` = :modelAnswer,
                mdlAnsHasMd = :mdlAnsHasMd
            WHERE id = :id '
        );
        $stmt->execute([
            'id' => $id,
            'modelAnswer' => $modelAnswer,
            'mdlAnsHasMd' => $mdlAnsHasMD,
        ]);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM question 
            WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
        $question = $stmt->fetch();

        $stmt = $this->db->prepare(
            'DELETE FROM question
            WHERE id = :id '
        );
        $stmt->execute(['id' => $id]);

        $stmt = $this->db->prepare(
            'UPDATE question SET seq = seq - 1
            WHERE quiz_id = :quiz_id 
            AND seq > :seq'
        );
        $stmt->execute([
            'seq' => $question['seq'],
            'quiz_id' => $question['id'],
        ]);
    }

    /**
     * Clones all questions for a quiz, adding them to the new quiz
     */
    public function clone($quiz_id, $new_quiz_id)
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM question 
            WHERE quiz_id = :quiz_id'
        );
        $stmt->execute(['quiz_id' => $quiz_id]);
        $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $this->db->prepare(
            'INSERT INTO question 
			VALUES(NULL, :quiz_id, :text, :modelAnswer, :points, :seq, :type,
                    :hasMarkDown, :mdlAnsHasMD)'
        );
        foreach ($questions as $question) {
            $stmt->execute([
                'quiz_id' => $new_quiz_id,
                'text' => $question['text'],
                'modelAnswer' => $question['modelAnswer'],
                'points' => $question['points'],
                'seq' => $question['seq'],
                'type' => $question['type'],
                'hasMarkDown' => $question['hasMarkDown'],
                'mdlAnsHasMD' => $question['mdlAnsHasMD'],
            ]);
        }
    }
}
