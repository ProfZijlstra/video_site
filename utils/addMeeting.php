<?php

$db = new PDO("mysql:dbname=cs472;host=localhost", "cs472dbuser", "WAP Passwd");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$text = mb_convert_encoding(file_get_contents($argv[1]), "UTF-8", "UTF-16LE");
$lines = explode("\n", $text);

function to24hour($str) {
    $parts = date_parse($str);
    return $parts["hour"] . ":" . $parts["minute"] . ":" . $parts["second"];
}

function toIsoDate($str) {
    $parts = date_parse($str);
    return $parts["year"] . "-" . $parts["month"] . "-" . $parts["day"];
}


// gather meeting data 
$title = trim(str_getcsv($lines[2], "\t")[1]);
$fields = str_getcsv($lines[3], "\t");
$date = toIsoDate($fields[1]);

// get start time
if (count($fields) == 3) {
    $meeting_start = $fields[2];
} else {
    $meeting_start = to24hour($fields[1]);
}

// get stop time
$fields = str_getcsv($lines[4], "\t");
if (count($fields) == 3) {
    $meeting_stop = $fields[2];
} else {
    $meeting_stop = to24hour($fields[1]);
}

$day_id = 1;
$weight = 0.5;

// insert it into the database
$stmt = $db->prepare("INSERT INTO meeting VALUES(
    NULL, :day_id, :title, :date, :start, :stop, :weight)");
$stmt->execute(["day_id" => $day_id, "title" => $title, "date" => $date, 
                "start" => $meeting_start, "stop" => $meeting_stop, "weight" => $weight]);
$meeting_id = $db->lastInsertId();

// add attendance data
$stmt = $db->prepare("INSERT INTO attendance_data VALUES(NULL, :meeting_id, :name, :start, :stop)");
for ($i = 7; $i < count($lines) -1; $i++) {
    list($name, $start, $stop, $duration, $email, $role) = str_getcsv($lines[$i], "\t");
    $start = to24hour($start);
    $stop = to24hour($stop);

    $stmt->execute(["meeting_id" => $meeting_id, "name" => $name, 
                    "start" => $start, "stop" => $stop]);
}

// generate attendance report

// get teamsNames for enrollment of offering_id = 2
$stmt = $db->prepare("SELECT u.id, u.teamsName 
    FROM enrollment e JOIN user u ON e.user_id = u.id 
    WHERE offering_id = :offering_id");
$stmt->execute(array("offering_id" => 2));
$enrolled = $stmt->fetchAll();

$enrollment = [];
foreach ($enrolled as $student) {
    $enrollment[$student["teamsName"]]  = true;
}

$stmt = $db->prepare("SELECT * FROM attendance_data WHERE meeting_id = :meeting_id ");
$stmt->execute(["meeting_id" => $meeting_id]);
$attendants = $stmt->fetchAll();

// find notEnrolled attendants (while constructing attendance array)
$attendance = [];
foreach ($attendants as $attendant) {
    $attendance[$attendant["teamsName"]] = ["notEnrolled" => 0, 
                                            "absent" => 0,
                                            "arriveLate" => 0,
                                            "leaveEarly" => 0,
                                            "middleMissing" => 0];

    if (!$enrollment[$attendant["teamsName"]]) {
        $attendance[$attendant["teamsName"]]["notEnrolled"] = 1;
    }
}

// find / add absent students
foreach ($enrolled as $student) {
    if (!$attendance[$student["teamsName"]]) {
        $attendance[$student["teamsName"]] = ["notEnrolled" => 0, 
                                            "absent" => 1,
                                            "arriveLate" => 0,
                                            "leaveEarly" => 0,
                                            "middleMissing" => 0];
    }
}

// mark those that arrived late and those that left early
$stmt = $db->prepare("SELECT teamsName, MIN(start), MAX(stop) 
                    FROM attendance_data WHERE meeting_id = :meeting_id ");
$stmt->execute(["meeting_id" => $meeting_id]);
$attendants = $stmt->fetchAll();

$margin = 3 * 60; // 3 minutes
$meeting_start = strtotime($meeting_start) + $margin;
$meeting_stop = strtotime($meeting_stop) - $margin;

foreach ($attendants as $attendant) {
    if (strtotime($attendant["start"]) > $meeting_start) {
        $attendance[$attendant["teamsName"]]["arriveLate"] = 1;
    }
    if (strtotime($attendant["stop"]) < $meeting_stop) {
        $attendance[$attendant["teamsName"]]["leaveEarly"] = 1;
    }
}

// for those with multiple entrires check if middle missing (lack of duration)
$stmt = $db->prepare("SELECT teamsName, COUNT(id) as `count`, 
                        SUM(`stop` - `start`) as `duration` 
                        FROM attendance_data 
                        WHERE meeting_id = :meeting_id 
                        GROUP BY teamsName
                        HAVING `count` > 1");
$stmt->execute(["meeting_id" => $meeting_id]);
$attendants = $stmt->fetchAll();

$meeting_duration = $meeting_stop - $meeting_start;
foreach ($attendants as $attendant) {
    if ($attendant['duration'] < $meeting_duration) {
        $attendance['teamsName']['middleMissing'] = 1;
    }
}

// insert attendance into DB
$stmt = $db->prepare("INSERT INTO attendance VALUES(NULL, :meeting_id, 
    :teamsName, :notEnrolled, :absent, :arriveLate, :leaveEarly, :middleMissing, 
    0)");
foreach ($attendance as $teamsName => $attend) {
    $stmt->execute(["meeting_id" => $meeting_id, "teamsName" => $teamsName, 
        "notEnrolled" => $attend["notEnrolled"], "absent" => $attend["absent"], 
        "arriveLate" => $attend["arriveLate"], "leaveEarly" => $attend["leaveEarly"], 
        "middleMissing" => $attend["middleMissing"]]);
}
