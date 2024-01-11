<?php

/**
 * @author mzijlstra 08/17/2022
 */

#[Repository]
class QuizEventDao
{
    #[Inject('DB')]
    public $db;

    public function add($quiz_id, $user_id, $type)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO quiz_event 
			VALUES(NULL, NOW(), :type, :quiz_id, :user_id)"
        );
        $stmt->execute(array(
            "type" => $type,
            "quiz_id" => $quiz_id,
            "user_id" => $user_id,
        ));
        return $this->db->lastInsertId();
    }

    public function getStartTimes($quiz_id)
    {
        $this->db->exec("SET time_zone = '" . TIMEZONE . "'");
        $stmt = $this->db->prepare(
            "SELECT e.user_id, MIN(e.timestamp) AS `start`
            FROM quiz_event AS e 
            WHERE e.type = 'start'
            AND e.quiz_id = :quiz_id 
            GROUP BY e.user_id "
        );
        $stmt->execute(array(
            "quiz_id" => $quiz_id
        ));
        $result =  [];
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $result[$row['user_id']] = $row['start'];
        }
        return $result;
    }

    public function getStopTimes($quiz_id)
    {
        $this->db->exec("SET time_zone = '" . TIMEZONE . "'");
        $stmt = $this->db->prepare(
            "SELECT e.user_id, MAX(e.timestamp) AS `stop`
            FROM quiz_event AS e 
            WHERE e.type = 'stop'
            AND e.quiz_id = :quiz_id 
            GROUP BY e.user_id "
        );
        $stmt->execute(array(
            "quiz_id" => $quiz_id
        ));
        $result =  [];
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $result[$row['user_id']] = $row['stop'];
        }
        return $result;
    }

    public function forUser($quiz_id, $user_id)
    {
        $this->db->exec("SET time_zone = '" . TIMEZONE . "'");
        $stmt = $this->db->prepare(
            "SELECT *
            FROM quiz_event 
            WHERE quiz_id = :quiz_id
            AND user_id = :user_id "
        );
        $stmt->execute(array(
            "quiz_id" => $quiz_id,
            "user_id" => $user_id
        ));
        return $stmt->fetchAll();
    }
}

