<?php

$db = new PDO("mysql:dbname=cs472;host=mysql.manalabs.org", "cs472dbuser", "WAP Passwd");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $db->prepare("SELECT * FROM meeting ");
$stmt->execute();
$meetings = $stmt->fetchAll();

$stmt_updMeet = $db->prepare("UPDATE meeting SET session_id = :session_id WHERE id = :meeting_id");
$stmt_getSession = $db->prepare("SELECT * from `session` WHERE day_id = :day_id AND `type` = :type");

foreach ($meetings as $meeting) {
    if ($meeting["start"] < "20:00:00") {
        $stmt_getSession->execute(["day_id" => $meeting["day_id"], "type" => "AM"]);
        $session = $stmt_getSession->fetch();
    } else {
        $stmt_getSession->execute(["day_id" => $meeting["day_id"], "type" => "PM"]);
        $session = $stmt_getSession->fetch();
    }
    $stmt_updMeet->execute(["session_id" =>  $session["id"], "meeting_id" => $meeting["id"]]);
}