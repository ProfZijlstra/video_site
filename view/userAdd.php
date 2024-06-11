<!--
 Created on : August 30, 2014, 7:30:00 PM
 Author     : mzijlstra
-->
<!DOCTYPE html>
<html>

<head>
    <title>Add User</title>
    <meta name=viewport content="width=device-width, initial-scale=1">
    <meta charset="UTF-8">
    <link rel="stylesheet" href="res/css/lib/font-awesome-all.min.css">
    <link rel="stylesheet" href="res/css/common-1.3.css">
    <link rel="stylesheet" href="res/css/user.css">
    <script src="res/js/back.js"></script>
    <script src="res/js/user.js"></script>
</head>

<body>
    <?php include("header.php"); ?>
    <main>
        <nav class="back" title="Back">
            <i class="fa-solid fa-arrow-left"></i>
        </nav>

        <div class="error"><?= $msg ?></div>
        <form method="post" action="../user">
            <div class="fields">
                <div id="label_first">
                    <span>Given Name(s):</span>
                </div>
                <div class="text">
                    <input type="text" name="first" id="first" value="" autofocus /> <br />
                </div>
                <div id="label_knownAs">
                    <span>Known As:</span>
                </div>
                <div class="text">
                    <input type="text" name="knownAs" id="knownAs" value="" /> <br />
                </div>
                <div id="label_last">
                    <span>Family Name(s):</span>
                </div>
                <div id="last" class="text">
                    <input type="text" name="last" value="" /> <br />
                </div>
                <div id="label_email">
                    <span>Email:</span>
                </div>
                <div id="email" class="text">
                    <input type="text" name="email" value="" /> <br />
                </div>
                <div id="label_studentID">
                    <span>Student ID:</span>
                </div>
                <div id="studentID" class="text">
                    <input type="text" name="studentID" value="" /> <br />
                </div>
                <div id="label_teamsName">
                    <span>Teams Name:</span>
                </div>
                <div id="teamsName" class="text">
                    <input type="text" name="teamsName" value="" /> <br />
                </div>
                <div id="label_pass">
                    <span>Password:</span>
                </div>
                <div id="pass" class="text">
                    <input type="password" name="pass" /> <br />
                </div>
                <div id="label_isAdmin">
                    <span>Is Admin:</span>
                </div>
                <div id="isAdmin">
                    <select name="isAdmin">
                        <option value="0">No</option>
                        <option value="1">Yes</option>
                    </select> <br />
                </div>
                <div id="label_isFaculty">
                    <span>Is Faculty:</span>
                </div>
                <div id="isFaculty">
                    <select name="isFaculty">
                        <option value="0">No</option>
                        <option value="1">Yes</option>
                    </select> <br />
                </div>
                <div id="label_active">
                    <span>Active:</span>
                </div>
                <div id="active">
                    <select name="active">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                    <div id="btn">
                        <button>Add</button>
                    </div>
                </div>
            </div>
        </form>
    </main>
</body>

</html>