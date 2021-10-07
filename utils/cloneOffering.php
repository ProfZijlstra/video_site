<?php
/**
 * Clone offering takes an offering ID (num), a block (string like "2022-01"),
 * and a start date (string like "2022-01-10").
 * 
 * Then creates the directory structure inside the /res directory, making
 * symbolic links for *.mp4 in the vid dirs and *.pdf in the pdf dirs.
 * To create these directories it expects the CWD to be the utils dir!
 * 
 * Next creates an row in the offering table, and rows in the day table. The
 * day rows are clones of the day rows for the original offering.
 */

// get user input
$offering_id = "";
$matches = [];
while(!$matches) {
    print("Offering ID of offering to clone: ");
    $offering_id = fgets(STDIN);
    preg_match("/^(\d+).*$/", $offering_id, $matches);
}
$offering_id = $matches[1];

$block = "";
$matches = [];
while (!$matches) {
    print("Block of new offering: ");
    $block = fgets(STDIN);
    preg_match("/^(\d{4}-\d{2}).*$/", $block, $matches);
}
$block = $matches[1];

$start = "";
$matches = [];
while (!$matches) {
    print("Start date of new offering: ");
    $start = fgets(STDIN);
    preg_match("/^(\d{4}-\d{2}-\d{2}).*$/", $start, $matches);
}
$start = $matches[1];

// calculate stop date
$stop = date_create($start);
date_add($stop, date_interval_create_from_date_string("24 days"));
$stop = date_format($stop, "Y-m-d");
echo "Stop date: $stop \n"; 



// setup the DB connection
$db = new PDO("mysql:dbname=cs472;host=localhost", "cs472dbuser", "WAP Passwd");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// get the course number from the provided offering_id
$stmt = $db->prepare("SELECT * FROM offering WHERE id = :offering_id ");
$stmt->execute(array("offering_id" => $offering_id));
$data = $stmt->fetch();
$course_number = $data["course_number"];
$old_block = $data["block"];

// change directory to where the course materials are and start clone
chdir("../res/$course_number");
mkdir($block);
chdir($block);

// clone the day of week directories
for ($week = 1; $week < 5; $week++) {
    for ($day = 1; $day < 7; $day++) { // we don't make sunday dirs
        mkdir("W${week}D${day}");
        chdir("W${week}D${day}");
        // make symlinks to previous offering videos
        mkdir("vid");
        // find previoud video files
        if (chdir("../../${old_block}/W${week}D${day}/vid")) {
            $videos = glob("*.mp4");
            // make links in new vid directory
            chdir("../../../${block}/W${week}D${day}/vid");
            foreach ($videos as $video) {
                symlink("../../../${old_block}/W${week}D${day}/vid/$video", $video);
            }    
            chdir(".."); // exit vid dir
        }
        // make symlinks to previous offering pdfs
        mkdir("pdf");
        // find previoud pdf files
        if (chdir("../../${old_block}/W${week}D${day}/pdf")) {
            $pdfs = glob("*.pdf");
            // make links in new pdf directory
            chdir("../../../${block}/W${week}D${day}/pdf");        
            foreach ($pdfs as $pdf) {
                symlink("../../../${old_block}/W${week}D${day}/pdf/$pdf", $pdf);
            }
            chdir(".."); // exit pdf dir    
        }
        chdir(".."); // exit day dir
    }
}


// create the new offering in the DB
$stmt = $db->prepare(
    "INSERT INTO offering 
    VALUES(NULL, :course_number, :block, :start, :stop)"
);
$stmt->execute(array("course_number" => $course_number, "block" => $block, 
    "start" => $start, "stop" => $stop));
$new_offering = $db->lastInsertId();

// get old days from the DB
$stmt = $db->prepare("SELECT * FROM day
WHERE offering_id = :offering_id");
$stmt->execute(array("offering_id" => $offering_id));
$days = $stmt->fetchAll();

// clone days
$stmt = $db->prepare(
    "INSERT INTO day
    VALUES(NULL, :offering_id, :abbr, :desc)"
);
foreach ($days as $day) {
    $stmt->execute(array("offering_id" => $new_offering, "abbr" => $day["abbr"], 
        "desc" => $day["desc"]));
}
