<?php

$db = new PDO("mysql:dbname=cs472;host=mysql.manalabs.org", "cs472dbuser", "WAP Passwd");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $db->prepare("SELECT * FROM day ");
$stmt->execute();
$days = $stmt->fetchAll();

$stmt = $db->prepare("INSERT INTO `session` VALUES(NULL, :day_id, :type, 0)");
foreach ($days as $day) {
    $stmt->execute(["day_id" => $day["id"], "type" => "AM"]);
    $stmt->execute(["day_id" => $day["id"], "type" => "PM"]);
}