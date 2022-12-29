<!--
 Created on : August 30, 2014, 7:30:00 PM
 Author     : mzijlstra
-->
<?php 
if (!isset($user)) {
    $user = false;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>User Details</title>
        <meta name=viewport content="width=device-width, initial-scale=1">
        <meta charset="UTF-8">
        <link rel="stylesheet" href="res/css/font-awesome-all.min.css">
        <link rel="stylesheet" href="res/css/common-1.1.css">
        <link rel="stylesheet" href="res/css/user.css">
        <script src="res/js/back.js"></script>
    </head>
    <body>
        <?php $title="User Details:"; include("header.php");?>
        <main>
            <nav class="back" title="Back">
                <i class="fa-solid fa-arrow-left"></i>
            </nav>

        <div class="error"><?= $msg ?></div>
        <form method="post" action="<?= $user ? $user['id'] : "../user" ?>">
        <div class="fields">
            <div id="label_knownAs">
                <span >Known As:</span>
            </div>
            <div id="knownAs" class="text">
                <input type="text" name="knownAs" value="<?= $user ? $user['knownAs'] : "" ?>" /> <br />
            </div>
            <div id="label_first">
                <span >Given Name(s):</span>
            </div>
            <div id="first" class="text">
                <input type="text" name="first" value="<?= $user ? $user['firstname'] : "" ?>" /> <br />
            </div>
            <div id="label_last" >
                <span>Family Name(s):</span>
            </div>
            <div id="last" class="text">
                <input type="text" name="last"  value="<?= $user ? $user['lastname'] : "" ?>"/> <br />
            </div>
            <div id="label_email" >
                <span>Email:</span>
            </div>
            <div id="email" class="text">
                <input type="text" name="email"  value="<?= $user ? $user['email'] : "" ?>"/> <br />
            </div>
            <div id="label_studentID" >
                <span>Student ID:</span>
            </div>
            <div id="studentID" class="text">
                <input type="text" name="studentID"  value="<?= $user ? $user['studentID'] : "" ?>"/> <br />
            </div>
            <div id="label_teamsName" >
                <span>Teams Name:</span>
            </div>
            <div id="teamsName" class="text">
                <input type="text" name="teamsName"  value="<?= $user ? $user['teamsName'] : "" ?>"/> <br />
            </div>
            <div id="label_pass" >
                <span>Password:</span>
            </div>
            <div id="pass" class="text">
                <input type="password" name="pass" /> <br />
            </div>
            <div id="label_type" >
                <span>Type:</span>
            </div>
            <div id="type">
                <select name="type">
                    <?php foreach ($types as $type): ?>
                        <option <?= $user['type'] == $type ? 'selected' : ''?>><?= $type ?></option>
                    <?php endforeach; ?>                    
                </select> <br />
                </div>
            <div id="label_active" >
                <span>Active:</span>
            </div>
            <div id="active">
                <select name="active">
                    <option value="true">Yes</option>
                    <option value="" <?= $user && $user['active']==1 ? "" : "selected" ?>>No</option>
                </select>
                <div id="btn">
                    <button><?= $user ? 'Update' : 'Add' ?></button> 
                </div>
            </div>
        </form>
        </main>
    </body>
</html>
