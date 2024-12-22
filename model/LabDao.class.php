<?php

/**
 * @author mzijlstra 14 Jan 2024
 */
#[Repository]
class LabDao
{
    #[Inject('DB')]
    public $db;

    #[Inject('DeliverableDao')]
    public $deliverableDao;

    public function allForOffering(int $offering_id): array
    {
        $stmt = $this->db->prepare(
            'SELECT a.id, a.name, a.visible, d.abbr, a.type, a.start, a.stop
            FROM lab AS a
            JOIN day AS d ON a.day_id = d.id
            JOIN offering AS o ON d.offering_id = o.id
            WHERE o.id = :offering_id
            AND o.active = 1'
        );
        $stmt->execute(['offering_id' => $offering_id]);

        return $stmt->fetchAll();
    }

    public function visibleForOffering(int $offering_id): array
    {
        $stmt = $this->db->prepare(
            'SELECT a.id, a.name, a.visible, d.abbr, a.start, a.stop
            FROM lab AS a
            JOIN day AS d ON a.day_id = d.id
            JOIN offering AS o ON d.offering_id = o.id
            WHERE o.id = :offering_id 
            AND a.visible = 1
            AND o.active = 1'
        );
        $stmt->execute(['offering_id' => $offering_id]);

        return $stmt->fetchAll();
    }

    public function getInstructorGradingStatus($offering_id)
    {
        $stmt = $this->db->prepare('
            SELECT a.id, count(c1.id) AS answers, count(c2.id) AS ungraded
            FROM lab AS a
            JOIN day AS d ON a.day_id = d.id
            JOIN offering AS o ON d.offering_id = o.id
            JOIN deliverable AS b ON a.id = b.lab_id
            LEFT JOIN delivery AS c1 ON b.id = c1.deliverable_id 
            LEFT JOIN delivery AS c2 ON c1.id = c2.id AND c2.points IS NULL
            WHERE o.id = :offering_id
            AND o.active = 1
            GROUP BY a.id
        ');
        $stmt->execute(['offering_id' => $offering_id]);

        return $stmt->fetchAll();
    }

    public function getStudentGradingStatus($offering_id, $user_id)
    {
        $stmt = $this->db->prepare('
            SELECT a.id, count(c1.id) AS answers, count(c2.id) AS ungraded
            FROM lab AS a
            JOIN day AS d ON a.day_id = d.id
            JOIN offering AS o ON d.offering_id = o.id
            JOIN deliverable AS b ON a.id = b.lab_id
            LEFT JOIN delivery AS c1 ON b.id = c1.deliverable_id 
            LEFT JOIN delivery AS c2 ON c1.id = c2.id AND c2.points IS NULL
            WHERE o.id = :offering_id
            AND o.active = 1
            AND c1.user_id = :user_id
            GROUP BY a.id
        ');
        $stmt->execute([
            'offering_id' => $offering_id,
            'user_id' => $user_id,
        ]);

        return $stmt->fetchAll();
    }

    public function add(string $name, int $day_id, string $start, string $stop): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO lab
            VALUES(NULL, :day_id, :name, :start, :stop, 0, 'Individual')"
        );
        $stmt->execute([
            'name' => $name,
            'day_id' => $day_id,
            'start' => $start,
            'stop' => $stop,
        ]);

        return $this->db->lastInsertId();
    }

    public function byId($id)
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM lab
            WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);

        return $stmt->fetch();
    }

    public function update($id, $visible, $name, $day_id, $start, $stop, $type, $hasMarkDown, $desc)
    {
        $stmt = $this->db->prepare(
            'UPDATE lab 
            SET visible = :visible, `name` = :name, 
            day_id = :day_id, 
            `start` = :start, 
            `stop` = :stop, 
            type = :type
            WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'visible' => $visible,
            'name' => $name,
            'day_id' => $day_id,
            'start' => $start,
            'stop' => $stop,
            'type' => $type,
        ]);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare(
            'DELETE FROM lab
            WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
    }

    /**
     * Copy / Pasted from QuizDao -- factor out common parts?
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
        $dates = $stmt->fetchAll();
        $earlier = new DateTime(substr($dates[0]['start'], 0, 10));
        $later = new DateTime(substr($dates[1]['start'], 0, 10));
        $daysDiff = $earlier->diff($later)->format('%r%a');
        $interval = new DateInterval("P{$daysDiff}D");

        // create a lookup table for abbr to new day id
        $stmt = $this->db->prepare(
            'SELECT id, abbr FROM day
            WHERE offering_id = :offering_id'
        );
        $stmt->execute(['offering_id' => $new_offering_id]);
        $rows = $stmt->fetchAll();

        $days = [];
        foreach ($rows as $row) {
            $days[$row['abbr']] = $row['id'];
        }

        // get all the old labs
        $stmt = $this->db->prepare(
            'SELECT l.id, l.name, l.start, l.stop, 
            l.visible, l.type, d.abbr 
            FROM lab AS l
            JOIN `day` AS d on l.day_id = d.id
            WHERE d.offering_id = :offering_id'
        );
        $stmt->execute(['offering_id' => $offering_id]);
        $labs = $stmt->fetchAll();

        // create a clone for each on the same day in the new offering
        $stmt = $this->db->prepare(
            'INSERT INTO lab 
            VALUES(NULL, :day_id, :name, 
                    :start, :stop, :visible, :type)'
        );
        foreach ($labs as $lab) {
            // move start date by date difference between offerings
            $start = new DateTime($lab['start']);
            $stop = new DateTime($lab['stop']);
            $start->add($interval);
            $stop->add($interval);

            $stmt->execute([
                'day_id' => $days[$lab['abbr']],
                'name' => $lab['name'],
                'start' => $start->format('Y-m-d H:i:s'),
                'stop' => $stop->format('Y-m-d H:i:s'),
                'visible' => $lab['visible'] ? 1 : 0,
                'type' => $lab['type'],
            ]);
            $new_lab_id = $this->db->lastInsertId();

            // also clone the deliverables for each lab
            $this->deliverableDao->clone($lab['id'], $new_lab_id);
        }
    }

    public function getIndividualLabTotals($lab_id, $offering_id)
    {
        $stmt = $this->db->prepare(
            'SELECT e.user_id, ifnull(sum(de.points), 0) AS points
            FROM enrollment AS e 
            JOIN offering AS o ON e.offering_id = o.id
            JOIN `day` AS d ON o.id = d.offering_id
            JOIN lab AS l ON d.id = l.day_id 
                AND l.id = :lab_id
            LEFT JOIN submission AS s ON l.id = s.lab_id
                AND e.user_id = s.user_id 
            LEFT JOIN delivery AS de ON de.submission_id = s.id
            WHERE e.offering_id = :offering_id
            GROUP BY e.user_id '
        );
        $stmt->execute([
            'lab_id' => $lab_id,
            'offering_id' => $offering_id,
        ]);

        return $stmt->fetchAll();
    }

    public function getGroupLabTotals($lab_id, $offering_id)
    {
        $stmt = $this->db->prepare(
            'SELECT e.user_id, ifnull(sum(de.points), 0) AS points
            FROM enrollment AS e 
            JOIN offering AS o ON e.offering_id = o.id
            JOIN `day` AS d ON o.id = d.offering_id
            JOIN lab AS l ON d.id = l.day_id 
                AND l.id = :lab_id
            LEFT JOIN submission AS s ON l.id = s.lab_id
                AND e.group = s.group 
            LEFT JOIN delivery AS de ON de.submission_id = s.id
            WHERE e.offering_id = :offering_id
            GROUP BY e.user_id '
        );
        $stmt->execute([
            'lab_id' => $lab_id,
            'offering_id' => $offering_id,
        ]);

        return $stmt->fetchAll();
    }
}
