<!--
 Created on : August 30, 2014, 7:30:00 PM
 Author     : mzijlstra
-->
<!DOCTYPE html>
<html>

<head>
    <title>User Details</title>
    <meta name=viewport content="width=device-width, initial-scale=1">
    <meta charset="UTF-8">
    <link rel="stylesheet" href="res/css/lib/font-awesome-all.min.css">
    <link rel="stylesheet" href="res/css/common-1.3.css">
    <link rel="stylesheet" href="res/css/user.css">
    <script src="res/js/user.js"></script>
    <script src="res/js/userDetails-1.0.js"></script>
</head>

<body>
    <?php include("header.php"); ?>
    <main>
        <nav class="back" title="Back">
            <i class="fa-solid fa-arrow-left"></i>
        </nav>

        <div class="error"id="error"></div>
        <input type="hidden" id="uid" value="<?= $user['id'] ?>" />
        <div class="fields">
            <div id="label_first">
                <span>Given Name(s):</span>
            </div>
            <div class="text">
                <input type="text" name="first" id="first" value="<?= $user['firstname'] ?>" autofocus /> <br />
            </div>
            <div id="label_knownAs">
                <span>Known As:</span>
            </div>
            <div class="text">
                <input type="text" name="knownAs" id="knownAs" value="<?= $user['knownAs'] ?>" data-provided="<?= $user['knownAs'] ?>" /> <br />
            </div>
            <div id="label_last">
                <span>Family Name(s):</span>
            </div>
            <div class="text">
                <input type="text" name="last" id="last" value="<?= $user['lastname'] ?>" /> <br />
            </div>
            <div id="label_email">
                <span>Email:</span>
            </div>
            <div class="text">
                <input type="text" name="email" id="email" value="<?= $user['email'] ?>" /> <br />
            </div>
            <div id="label_studentID">
                <span>Student ID:</span>
            </div>
            <div class="text">
                <input type="text" name="studentID" id="studentID" value="<?= $user['studentID'] ?>" /> <br />
            </div>
            <div id="label_teamsName">
                <span>Teams Name:</span>
            </div>
            <div class="text">
                <input type="text" name="teamsName" id="teamsName" value="<?= $user['teamsName'] ?>" /> <br />
            </div>
            <div id="label_pass">
                <span>Password:</span>
            </div>
            <div class="text">
                <input type="password" name="pass" id="pass" /> <br />
            </div>
            <div id="label_isAdmin">
                <span>Is Admin:</span>
            </div>
            <div>
                <select name="isAdmin" id="isAdmin">
                    <option value="0">No</option>
                    <option value="1" <?= $user['isAdmin'] == 1 ? "selected" : "" ?>>Yes</option>
                </select> <br />
            </div>
            <div id="label_isFaculty">
                <span>Is Faculty:</span>
            </div>
            <div>
                <select name="isFaculty" id="isFaculty">
                    <option value="0">No</option>
                    <option value="1" <?= $user['isFaculty'] == 1 ? "selected" : "" ?>>Yes</option>
                </select> <br />
            </div>
            <div id="label_active">
                <span>Active:</span>
            </div>
            <div>
                <select name="active" id="active">
                    <option value="1" <?= $user['active'] == 1 ? "selected" : "" ?>>Yes</option>
                    <option value="0" <?= $user['active'] == 0 ? "selected" : "" ?>>No</option>
                </select>
            </div>
        </div>
    </main>
</body>

</html>