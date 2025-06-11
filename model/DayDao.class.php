<?php

/**
 * Day Dao Class
 *
 * @author mzijlstra 06/06/2021
 */

#[Repository]
class DayDao
{
	#[Inject('DB')]
	public $db;

	/**
	 * Gets days for a given offering
	 * @param int offering_id 
	 * @return array of offering data
	 */
	public function getDays($offering_id)
	{
		$stmt = $this->db->prepare("SELECT * FROM day
			WHERE offering_id = :offering_id ORDER BY abbr");
		$stmt->execute(array("offering_id" => $offering_id));
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getDayId($course, $block, $day)
	{
		$stmt = $this->db->prepare("SELECT d.id 
			from day as d 
			JOIN offering as o on d.offering_id = o.id 
			WHERE o.course_number = :course 
			AND o.block = :block 
			AND o.active = 1
			AND d.abbr = :day ");
		$stmt->execute(
			array("course" => $course, "block" => $block, "day" => $day)
		);
		return $stmt->fetch();
	}

	public function update($day_id, $desc)
	{
		$stmt = $this->db->prepare(
			"UPDATE day SET `desc` = :desc WHERE id = :day_id"
		);
		$stmt->execute(array("desc" => $desc, "day_id" => $day_id));
	}

	public function cloneDays($offering_id, $new_offering)
	{
		// get old days from the DB
		$stmt = $this->db->prepare("SELECT * FROM day
		WHERE offering_id = :offering_id");
		$stmt->execute(array("offering_id" => $offering_id));
		$days = $stmt->fetchAll(PDO::FETCH_ASSOC);

		// clone days
		$stmt = $this->db->prepare(
			"INSERT INTO day
			VALUES(NULL, :offering_id, :abbr, :desc)"
		);
		foreach ($days as $day) {
			$stmt->execute(array(
				"offering_id" => $new_offering,
				"abbr" => $day["abbr"], "desc" => $day["desc"]
			));
		}
	}

	public function get($day_id)
	{
		$stmt = $this->db->prepare("SELECT *
			FROM day 
			WHERE id = :day_id");
		$stmt->execute(["day_id" => $day_id]);
		return $stmt->fetch();
	}

	public function create($offering_id, $lessonsPerRow, $lessonRows)
	{
		$stmt = $this->db->prepare(
			"INSERT INTO day
			VALUES(NULL, :offering_id, :abbr, 'TODO')"
		);

		for ($week = 1; $week <= $lessonRows; $week++) {
			for ($day = 1; $day <= $lessonsPerRow; $day++) {
				$stmt->execute(array(
					"offering_id" => $offering_id, "abbr" => "W{$week}D{$day}"
				));
			}
		}
	}

	public function getDate($day_id)
	{
		$stmt = $this->db->prepare(
			"SELECT d.abbr, o.start, 
			o.daysPerLesson, o.lessonsPerPart, o.lessonParts
			FROM day d
			JOIN offering o ON d.offering_id = o.id
			WHERE d.id = :day_id
			AND o.active = 1"
		);
		$stmt->execute(["day_id" => $day_id]);
		$result = $stmt->fetch();
		$abbr = $result['abbr'];
		$week = $abbr[1] - 1;
		$day = $abbr[3] - 1;
		$daysPerLesson = $result["daysPerLesson"];
		$lessonsPerPart = $result["lessonsPerPart"];
		$add = $daysPerLesson * $day + $lessonsPerPart * $daysPerLesson * $week;

		$tz = new DateTimeZone(TIMEZONE);
		$date = new DateTimeImmutable($result['start'], $tz);
		return $date->add(new DateInterval("P{$add}D"));
	}
}
