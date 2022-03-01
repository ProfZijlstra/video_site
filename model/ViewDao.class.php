<?php
/**
 * View DAO Class
 *
 * @author mzijlstra 06/04/2021
 * @Repository
 */
class ViewDao {

	/**
	 * @var PDO PDO database connection object
	 * @Inject("DB")
	 */
	public $db;

	/**
	 * Creates a new view in the database based on given values
	 * @param int $user_id 
	 * @param int $day_id
	 * @param string video file name
	 * @return int id of created view
	 */
	public function start($user_id, $day_id, $video, $speed) {
		$stmt = $this->db->prepare("INSERT INTO view 
			VALUES(NULL, 0, :user_id, :day_id, :video, NOW(), NULL, :speed)");
		$stmt->execute(array("user_id" => $user_id, 
			"day_id" => $day_id, "video" => $video, "speed" => $speed));
		return $this->db->lastInsertId();
	}

	/**
	 * Sets the stop timestamp for a view
	 * @param int $view_id
	 * @returns void
	 */
	public function stop($id, $speed) {
		$stmt = $this->db->prepare("UPDATE view SET `stop` = NOW(), `speed` = :speed  
			WHERE id = :id");
		$stmt->execute(array("id" => $id, "speed" => $speed));
	}

	public function get($id) {
		$stmt = $this->db->prepare("SELECT * FROM view WHERE id = :id");
		$stmt->execute(array("id" => $id));
		return $stmt->fetch();
	}

	public function pdf($user_id, $day_id, $video) {
		$stmt = $this->db->prepare("INSERT INTO view 
			VALUES(NULL, 1, :user_id, :day_id, :video, NOW(), NOW(), NULL)");
		$stmt->execute(array("user_id" => $user_id, 
			"day_id" => $day_id, "video" => $video));
	}

	public function offering($offering_id) {
		$stmt = $this->db->prepare(
			"SELECT d.abbr, d.desc, 
			COUNT(DISTINCT v.user_id) AS users, 
			COUNT(v.id) AS views, 
			FORMAT(SUM(TIME_TO_SEC(TIMEDIFF(stop, start)))/3600, 2) AS time 
			FROM view AS v 
			JOIN day AS d ON v.day_id = d.id 
			WHERE d.offering_id = :offering_id 
			GROUP BY d.id"
		);
		$stmt->execute(array("offering_id" =>  $offering_id));
		return $stmt->fetchAll();
	}

	public function offering_total($offering_id) {
		$stmt = $this->db->prepare(
			"SELECT COUNT(DISTINCT v.user_id) AS users, 
			FORMAT(COUNT(v.id), 0) AS views, 
			FORMAT(SUM(TIME_TO_SEC(TIMEDIFF(stop, start)))/3600, 2) AS time 
			FROM view as v 
			JOIN day AS d ON v.day_id = d.id 
			WHERE d.offering_id = :offering_id 
			GROUP BY d.offering_id;"
		);
        $stmt->execute(array(":offering_id" => $offering_id));
        return $stmt->fetch();
	}

	public function day_views($day_id) {
		$stmt = $this->db->prepare(
			"SELECT video, COUNT(DISTINCT user_id) AS users, 
			COUNT(id) AS views, 
			FORMAT(SUM(TIME_TO_SEC(TIMEDIFF(stop, start)))/3600, 2) AS time 
			FROM view 
			WHERE day_id = :day_id 
			GROUP BY video"
		);
		$stmt->execute(array("day_id" =>  $day_id));
		return $stmt->fetchAll();
	}

	public function day_total($day_id) {
		$stmt = $this->db->prepare(
			"SELECT COUNT(DISTINCT user_id) AS users, COUNT(id) AS views, 
			FORMAT(SUM(TIME_TO_SEC(TIMEDIFF(stop, start)))/3600, 2) AS time 
			FROM view 
			WHERE day_id = :day_id 
			GROUP BY day_id"
		);
        $stmt->execute(array(":day_id" => $day_id));
        return $stmt->fetch();
	}

	public function offering_viewers($offering_id) {
		$stmt = $this->db->prepare(
			"SELECT u.id, u.firstname, u.lastname, 
			SUM(TIME_TO_SEC(TIMEDIFF(v.stop, v.start)))/3600 as `hours`,
			SUM(CASE WHEN v.pdf = 0 
				THEN 1
				ELSE 0 END) as video,
			SUM(v.pdf) as pdf,
			SUM(CASE WHEN v.stop IS NULL THEN 1 ELSE 0 END) AS `nulls`
			from view as v 
			join user as u on v.user_id = u.id 
			join  day as d on v.day_id = d.id 
			join offering as o on d.offering_id = o.id 
			where o.id = :offering_id 
			group by u.id 
			order by `hours` desc"
		);
		$stmt->execute(array("offering_id" => $offering_id));
		return $stmt->fetchAll();
	}

	public function day_viewers($day_id) {
		$stmt = $this->db->prepare(
			"SELECT u.id, u.firstname, u.lastname, 
			SUM(TIME_TO_SEC(TIMEDIFF(v.stop, v.start)))/3600 as `hours`,
			SUM(CASE WHEN v.pdf = 0 
				THEN 1
				ELSE 0 END) as video,
			SUM(v.pdf) as pdf,
			SUM(CASE WHEN v.stop IS NULL THEN 1 ELSE 0 END) AS `nulls`
			from view as v 
			join user as u on v.user_id = u.id 
			where v.day_id = :day_id 
			group by u.id 
			order by hours desc"
		);
		$stmt->execute(array("day_id" => $day_id));
		return $stmt->fetchAll();
	}

	public function video_viewers($day_id, $video) {
		$stmt = $this->db->prepare(
			"SELECT u.id, u.firstname, u.lastname, 
			SUM(TIME_TO_SEC(TIMEDIFF(v.stop, v.start)))/3600 as `hours`,
			SUM(CASE WHEN v.pdf = 0 
				THEN 1
				ELSE 0 END) as video,
			SUM(v.pdf) as pdf,
			SUM(CASE WHEN v.stop IS NULL THEN 1 ELSE 0 END) AS `nulls`
			from view as v 
			join user as u on v.user_id = u.id 
			where v.day_id = :day_id 
			and v.video = :video 
			group by u.id 
			order by hours desc"
		);
		$stmt->execute(array("day_id" => $day_id, "video" => $video));
		return $stmt->fetchAll();
	}

	public function person_views($offering_id, $user_id) {
		$stmt = $this->db->prepare(
			"SELECT d.abbr as abbr, v.video as video,
			SUM(TIME_TO_SEC(TIMEDIFF(v.stop, v.start)))/3600 as `hours`,
			SUM(CASE WHEN v.pdf = 0 
				THEN 1
				ELSE 0 END) as video_views,
			SUM(v.pdf) as pdf,
			SUM(CASE WHEN v.stop IS NULL THEN 1 ELSE 0 END) AS `nulls`
			from view as v 
			join user as u on v.user_id = u.id 
			join day as d on v.day_id = d.id
			where u.id = :user_id
			and d.offering_id = :offering_id
			group by v.video 
			order by d.id, v.video
		");
		$stmt->execute(array("user_id" => $user_id, "offering_id" => $offering_id));
		return $stmt->fetchAll();

	}
}

