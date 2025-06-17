<!DOCTYPE html> <?php global $MY_BASE ?>
<html>

<head>
    <title>Profile</title>
    <meta name=viewport content="width=device-width, initial-scale=1">
    <meta charset="UTF-8">
    <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/lib/font-awesome-all.min.css">
    <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/common-1.3.css">
    <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/user.css">
    <script src="<?= $MY_BASE ?>/res/js/back.js"></script>
    <script src="<?= $MY_BASE ?>/res/js/profile.js"></script>

    <style>
    div#msg {
        min-height: 35px;
    }
    div.fields {
        grid-template-columns: 150px auto;
    }
    span.small {
        font-size: 0.8em;
    }
    input:invalid {
        box-shadow: 0 0 5px red;
    }
    input:invalid:focus {
        box-shadow: none;
    }
    dialog.modal {
        background-color: #DDD;
    }
    </style>
</head>

<body>
    <?php include 'header.php'; ?>
    <main>
        <nav class="back" title="Back">
            <i class="fa-solid fa-arrow-left"></i>
        </nav>
        <nav class="tools">
            <i title="Password Reset" class="fa-solid fa-key"></i>
        </nav>

        <div id="msg" class="error"id="error"><?= $msg ?></div>

        <input type="hidden" id="uid" value="<?= $user['id'] ?>" />
        <div class="fields">
            <div id="label_knownAs">
                <span>Known As:</span>
            </div>
            <div class="text">
                <input type="text" name="knownAs" id="knownAs" value="<?= $user['knownAs'] ?>" data-provided="<?= $user['knownAs'] ?>" /> <br />
            </div>
            <div id="label_first">
                <span>Given Name(s):</span>
            </div>
            <div class="text">
                <?= $user['firstname'] ?>
            </div>
            <div id="label_last">
                <span>Family Name(s):</span>
            </div>
            <div class="text">
                <?= $user['lastname'] ?>
            </div>
            <div id="label_email">
                <span>Email:</span>
            </div>
            <div class="text">
                <?= $user['email'] ?>
            </div>
            <div id="label_studentID">
                <span>Student ID:</span>
            </div>
            <div class="text">
                <?= $user['studentID'] ?>
            </div>
            <div id="label_teamsName">
                <span>Teams Name:</span>
            </div>
            <div class="text">
                <?= $user['teamsName'] ?>
            </div>
        </div>
    </main>

        <dialog id="resetDialog" class="modal">
            <i id="closeResetDialog" class="fas fa-times-circle close"></i>
            <h3>Password Reset</h3>
            <form id="resetForm" method="post" action="<?= $MY_BASE ?>/user/resetPassword">
                <input type="hidden" name="uid" value="<?= $user['id'] ?>" />
                <div>
                    <label>Current Password:</label>
                    <input type="password" name="currentPassword" id="currentPassword" required />
                </div>
                <div>
                    <label>New Password: <span class="small">(min 8 characters)</span></label>
                    <input type="password" name="newPassword" id="newPassword" required minLength="8" />
                </div>
                <div>
                    <label>Confirm Password:</label>
                    <input type="password" name="confirmPassword" id="confirmPassword" required />
                </div>
                <div class="btn">
                    <button type="submit">Reset Password</button>
                </div>
            </form>
        </dialog>

</body>

</html>
