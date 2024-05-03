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
            "SELECT a.id, a.name, a.visible, d.abbr 
            FROM lab AS a
            JOIN day AS d ON a.day_id = d.id
            JOIN offering AS o ON d.offering_id = o.id
            WHERE o.id = :offering_id
            AND o.active = 1"
        );
        $stmt->execute(array("offering_id" => $offering_id));
        return $stmt->fetchAll();
    }

    public function visibleForOffering(int $offering_id): array
    {
        $stmt = $this->db->prepare(
            "SELECT a.id, a.name, a.visible, d.abbr
            FROM lab AS a
            JOIN day AS d ON a.day_id = d.id
            JOIN offering AS o ON d.offering_id = o.id
            WHERE o.id = :offering_id 
            AND a.visible = 1
            AND o.active = 1"
        );
        $stmt->execute(array("offering_id" => $offering_id));
        return $stmt->fetchAll();
    }

    public function add(string $name, int $day_id, string $start, string $stop): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO lab
            VALUES(NULL, :day_id, :name, '', 0, :start, :stop, 0, 'Individual')"
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
            "SELECT * FROM lab
            WHERE id = :id"
        );
        $stmt->execute(array("id" => $id));
        return $stmt->fetch();
    }

    public function update($id, $visible, $name, $day_id, $start, $stop, $type, $hasMarkDown, $desc)
    {
        $stmt = $this->db->prepare(
            "UPDATE lab 
            SET visible = :visible, `name` = :name, 
            day_id = :day_id, 
            `start` = :start, 
            `stop` = :stop, 
            type = :type, 
            hasMarkDown = :hasMarkDown, 
            `desc` = :desc
            WHERE id = :id"
        );
        $stmt->execute(array(
            "id" =>  $id,
            "visible" => $visible,
            "name" => $name,
            "day_id" => $day_id,
            "start" => $start,
            "stop" => $stop,
            "type" => $type,
            "hasMarkDown" => $hasMarkDown,
            "desc" => $desc
        ));
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare(
            "DELETE FROM lab
            WHERE id = :id"
        );
        $stmt->execute(array("id" => $id));
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
        $daysDiff = $earlier->diff($later)->format("%r%a");
        $interval = new DateInterval("P{$daysDiff}D");

        // create a lookup table for abbr to new day id
        $stmt = $this->db->prepare(
            "SELECT id, abbr FROM day
            WHERE offering_id = :offering_id"
        );
        $stmt->execute(array("offering_id" => $new_offering_id));
        $rows = $stmt->fetchAll();

        $days = [];
        foreach ($rows as $row) {
            $days[$row['abbr']] = $row['id'];
        }

        // get all the old labs
        $stmt = $this->db->prepare(
            "SELECT l.id, l.name, l.desc, l.hasMarkDown, l.start, l.stop, 
            l.visible, l.type, l.points, d.abbr 
            FROM lab AS l
            JOIN `day` AS d on l.day_id = d.id
            WHERE d.offering_id = :offering_id"
        );
        $stmt->execute(array("offering_id" => $offering_id));
        $labs = $stmt->fetchAll();

        // create a clone for each on the same day in the new offering
        $stmt = $this->db->prepare(
            "INSERT INTO lab 
            VALUES(NULL, :day_id, :name, :desc, :hasMD, 
                    :start, :stop, :visible, :type, :points)"
        );
        foreach ($labs as $lab) {
            // move start date by date difference between offerings
            $start = new DateTime($lab['start']);
            $stop = new DateTime($lab['stop']);
            $start->add($interval);
            $stop->add($interval);

            $stmt->execute(array(
                "day_id" => $days[$lab['abbr']],
                "name" => $lab['name'],
                "desc" => $lab['desc'],
                "hasMD" => $lab['hasMarkDown'] ? 1 : 0,
                "start" => $start->format("Y-m-d H:i:s"),
                "stop" => $stop->format("Y-m-d H:i:s"),
                "visible" => $lab['visible'] ? 1 : 0,
                "type" => $lab['type'],
                "points" => $lab['points']
            ));
            $new_lab_id = $this->db->lastInsertId();

            // also clone the deliverables for each lab
            $this->deliverableDao->clone($lab['id'], $new_lab_id);
        }
    }
}
