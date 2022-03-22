<?php
$usage = "
The .csv file should be formatted like a copy/pasted infosys classlist

To add students to an existing offering use the -a option and provide the
offering id that you want to add these students to

php addStudents.php -a 5 extraStudents.csv

It's safe to add existing students a second time, it checks to see if a 
student is already in the database. It also checks to see if the student is
already enrolled in the offering.";

if (($argv[1] !== '-a') || count($argv) !== 4) {
	print($usage);
	exit(1);
}	

$db = new PDO("mysql:dbname=cs472;host=mysql.manalabs.org", "cs472dbuser", "WAP Passwd");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$offering_id = $argv[2];
$lines = file($argv[3]);

# we expect the CSV file to be formatted like a copy pasted infosys classlist
foreach($lines as $line) {

	# lines that do not start with an index and then a studentId are ignored
	if (preg_match("/^\d+\s*,\s*0{3}-[169]\d-\d{4}/", $line)) {
    	list($idx, $sid, $first, $middle, $last, $email) = str_getcsv($line);

		# check if the user is already in the database
		$stmt = $db->prepare("SELECT * FROM user WHERE email = :email");
		$stmt->execute(array("email" => $email));
		if ($stmt->rowCount() == 0) { // user does not exists in DB
			# transform social security formatted student ID into 6 digit 
			$matches = array();
			preg_match("/0{3}-([169]\d)-(\d{4})/", $sid, $matches);
			$id6 = $matches[1] . $matches[2];
			$hash = password_hash($id6, PASSWORD_DEFAULT);
			$teamsName = "$first $last";

			$stmt = $db->prepare("INSERT INTO user VALUES
				(NULL, :first, :last, :knownAs, :email, :studentId, :teamsName, 
				:pass, :type, NOW(), NOW(), :active)");
			$stmt->execute(array(
				"first" => $first, "last" => $last, "knownAs" => $first, 
				"email" => $email, "studentId" => $id6, "teamsName" => $teamsName, 
				"pass" => $hash, "type" => "user", "active" => 1));
			$user_id = $db->lastInsertId();

			# create custom welcome message
			$message = 
"Dear $first $middle $last,

Professor Michael Zijlstra's CS472 course has its lecture videos at: https://manalabs.org/videos/cs472/

To access these videos the following account has been created for you:

user: $email
pass: $id6

Please do not reply to this email, instead please ask your questions in class!

Enjoy your CS472 course,

Manalabs.org Automated Account Creator
";

			#email the user about his newly created account
			$headers ='From: "Manalabs Video System" <videos@manalabs.org> \r\n';
			mail($email, "CS472 manalabs.org account", $message, $headers);
			echo "created account: $email\n";
		} else { // user does exist in db
			$row = $stmt->fetch();
			$user_id = $row["id"];
			echo "account for $email exists\n";
		}

		# Check if the user is already enrolled in the offering
		$stmt = $db->prepare("SELECT * FROM enrollment 
			WHERE user_id = :user_id AND offering_id = :offering_id");
		$args = array("user_id" => $user_id, "offering_id" => $offering_id);
		$stmt->execute($args);
		if ($stmt->rowCount() == 0) {
			$stmt = $db->prepare("INSERT INTO enrollment 
				VALUES(NULL, :user_id, :offering_id)");
			$stmt->execute($args);
			echo "$email enrolled in $offering_id\n";
		} else {
			echo "$email was already registered to $offering_id\n";
		}

	} else {
        print "Ignoring: " . $line;
    }
}

?>
