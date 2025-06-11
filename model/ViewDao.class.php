<?php

/**
 * View DAO Class
 *
 * @author mzijlstra 06/04/2021
 */
#[Repository]
class ViewDao
{
    #[Inject('DB')]
    public $db;

    /**
     * Creates a new view in the database based on given values
     *
     * @param  int  $user_id
     * @param  int  $day_id
     * @param string video file name
     * @return int id of created view
     */
    public function start($user_id, $day_id, $video, $speed)
    {
        $stmt = $this->db->prepare('INSERT INTO view 
                        VALUES(NULL, 0, :user_id, :day_id, :video, NOW(), NULL, :speed)');
        $stmt->execute([
            'user_id' => $user_id,
            'day_id' => $day_id, 'video' => $video, 'speed' => $speed,
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Sets the stop timestamp for a view
     *
     * @param  int  $view_id
     *
     * @returns void
     */
    public function stop($id, $speed)
    {
        $stmt = $this->db->prepare('UPDATE view SET `stop` = NOW(), `speed` = :speed  
                        WHERE id = :id');
        $stmt->execute(['id' => $id, 'speed' => $speed]);
    }

    public function get($id)
    {
        $stmt = $this->db->prepare('SELECT * FROM view WHERE id = :id');
        $stmt->execute(['id' => $id]);

        return $stmt->fetch();
    }

    public function pdf($user_id, $day_id, $video)
    {
        $stmt = $this->db->prepare('INSERT INTO view 
                        VALUES(NULL, 1, :user_id, :day_id, :video, NOW(), NOW(), NULL)');
        $stmt->execute([
            'user_id' => $user_id,
            'day_id' => $day_id, 'video' => $video,
        ]);
    }

    public function offeringAverages($offering_id)
    {
        $stmt = $this->db->prepare(
            'SELECT d.abbr, 
                        COUNT(DISTINCT v.user_id) AS users, 
                        FORMAT(SUM(TIME_TO_SEC(TIMEDIFF(stop, start)))/3600, 2) AS time 
                        FROM view AS v 
                        JOIN day AS d ON v.day_id = d.id 
                        WHERE d.offering_id = :offering_id 
                        GROUP BY d.id'
        );
        $stmt->execute(['offering_id' => $offering_id]);

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
                        FORMAT(SUM(TIME_TO_SEC(TIMEDIFF(stop, start)))/3600, 2) AS time 
                        FROM view AS v 
                        JOIN day AS d ON v.day_id = d.id 
                        WHERE d.offering_id = :offering_id 
                        AND v.user_id = :user_id 
                        GROUP BY d.id'
        );
        $stmt->execute(['offering_id' => $offering_id, 'user_id' => $user_id]);

        $data = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $data[$row['abbr']] = $row;
        }

        return $data;
    }

    public function offeringTotal($offering_id)
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(DISTINCT v.user_id) AS users, 
                        FORMAT(COUNT(v.id), 0) AS views, 
                        FORMAT(SUM(TIME_TO_SEC(TIMEDIFF(stop, start)))/3600, 2) AS time 
                        FROM view as v 
                        JOIN day AS d ON v.day_id = d.id 
                        WHERE d.offering_id = :offering_id 
                        GROUP BY d.offering_id;'
        );
        $stmt->execute([':offering_id' => $offering_id]);

        return $stmt->fetch();
    }

    public function offeringUsers($offering_id)
    {
        $stmt = $this->db->prepare(
            'SELECT v.user_id,
                CAST(SUM(TIME_TO_SEC(TIMEDIFF(stop, start)))/3600 AS DECIMAL(10,2)) AS time 
                FROM view AS v 
                JOIN day AS d ON v.day_id = d.id 
                WHERE d.offering_id = :offering_id 
                GROUP BY v.user_id
                ORDER BY time DESC'
        );
        $stmt->execute(['offering_id' => $offering_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function offeringData($offering_id)
    {
        $stmt = $this->db->prepare(
            'SELECT v.start, v.user_id, d.abbr, v.video, v.speed,
                TIMEDIFF(stop, start) AS time 
                FROM view AS v 
                JOIN day AS d ON v.day_id = d.id 
            WHERE d.offering_id = :offering_id
            ORDER BY v.id DESC'
        );
        $stmt->execute(['offering_id' => $offering_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function dayAverages($offering_id, $day_abbr)
    {
        $stmt = $this->db->prepare(
            'SELECT v.video, 
                COUNT(DISTINCT v.user_id) AS users, 
                FORMAT(SUM(TIME_TO_SEC(TIMEDIFF(stop, start)))/3600, 2) AS time 
                FROM view AS v 
                JOIN day AS d ON v.day_id = d.id 
                WHERE d.offering_id = :offering_id 
                AND d.abbr = :day_abbr
            GROUP BY v.video
            ORDER BY v.video'
        );
        $stmt->execute(['offering_id' => $offering_id, 'day_abbr' => $day_abbr]);

        $data = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $data[substr($row['video'], 0, 2)] = $row;
        }

        return $data;
    }

    public function dayPerson($offering_id, $day_abbr, $user_id)
    {
        $stmt = $this->db->prepare(
            'SELECT v.video, 
                FORMAT(SUM(TIME_TO_SEC(TIMEDIFF(stop, start)))/3600, 2) AS time 
                FROM view AS v 
                JOIN day AS d ON v.day_id = d.id 
                WHERE d.offering_id = :offering_id 
                AND d.abbr = :day_abbr
                AND v.user_id = :user_id 
                GROUP BY v.video
                ORDER BY v.video'
        );
        $stmt->execute(['offering_id' => $offering_id, 'day_abbr' => $day_abbr, 'user_id' => $user_id]);

        $data = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $data[substr($row['video'], 0, 2)] = $row;
        }

        return $data;
    }

    public function dayTotal($offering_id, $day_abbr)
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(DISTINCT user_id) AS users, 
                        COUNT(v.id) AS views, 
                        FORMAT(SUM(TIME_TO_SEC(TIMEDIFF(stop, start)))/3600, 2) AS time 
                        FROM view AS v
                        JOIN day AS d ON v.day_id = d.id 
                        WHERE d.offering_id = :offering_id
                        AND d.abbr = :day_abbr
                        GROUP BY day_id'
        );
        $stmt->execute(['offering_id' => $offering_id, 'day_abbr' => $day_abbr]);

        return $stmt->fetch();
    }

    public function dayUsers($offering_id, $day_abbr)
    {
        $stmt = $this->db->prepare(
            'SELECT v.user_id,
                CAST(SUM(TIME_TO_SEC(TIMEDIFF(stop, start)))/3600 AS DECIMAL(10,2)) AS time 
                FROM view AS v 
                JOIN day AS d ON v.day_id = d.id 
                WHERE d.offering_id = :offering_id 
                AND d.abbr = :day_abbr
                GROUP BY v.user_id
                ORDER BY time DESC'
        );
        $stmt->execute(['offering_id' => $offering_id, 'day_abbr' => $day_abbr]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function dayData($offering_id, $day_abbr)
    {
        $stmt = $this->db->prepare(
            'SELECT v.start, v.user_id, d.abbr, v.video, v.speed,
                TIMEDIFF(stop, start) AS time 
                FROM view AS v 
                JOIN day AS d ON v.day_id = d.id 
            WHERE d.offering_id = :offering_id
            AND d.abbr = :day_abbr
            ORDER BY v.id DESC'
        );
        $stmt->execute(['offering_id' => $offering_id, 'day_abbr' => $day_abbr]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
