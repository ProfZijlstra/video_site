<!DOCTYPE html>
<html>

<head>
    <title><?= $block ?> Settings</title>
    <meta charset="utf-8" />
    <meta name=viewport content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="res/css/lib/font-awesome-all.min.css">
    <link rel="stylesheet" href="res/css/common-1.2.css">
    <link rel="stylesheet" href="res/css/adm.css">
    <style>
        #content h2,
        #content h3 {
            margin-bottom: 10px;
        }

        .defaults {
            display: grid;
            grid-template-columns: 70px 80px 30px 80px;
            row-gap: 10px;
            column-gap: 20px;

            #physical {
                grid-column: 2 / span 3;
            }
        }

        .settings {
            display: grid;
            grid-template-columns: 70px auto;
            row-gap: 10px;
            column-gap: 20px;
        }

        input[type="submit"] {
            margin-left: 100px;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <?php include("header.php"); ?>
    <main>
        <nav class="areas">
            <div title="Back">
                <a href="../attendance">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
            </div>
        </nav>

        <div id="content">
            <div class="error">
                <?= $error ?>
            </div>
            <h3>Meeting Defaults</h3>
            <form method="post" action="defaults">
                <div class="defaults">
                    <div>
                        <label for="AM_start">AM Start</label>
                    </div>
                    <div>
                        <input type="time" name="AM_start" id="AM_start" value="<?= substr($defaults['AM_start'], 0, 5) ?>" />
                    </div>

                    <div>
                        <label for="AM_stop">Stop </label>
                    </div>
                    <div>
                        <input type="time" name="AM_stop" id="AM_stop" value="<?= substr($defaults['AM_stop'], 0, 5) ?>" />
                    </div>

                    <div>
                        <label for="PM_start">PM Start</label>
                    </div>
                    <div>
                        <input type="time" name="PM_start" id="PM_start" value="<?= substr($defaults['PM_start'], 0, 5) ?>" />
                    </div>

                    <div>
                        <label for="PM_stop">Stop </label>
                    </div>
                    <div>
                        <input type="time" name="PM_stop" id="PM_stop" value="<?= substr($defaults['PM_stop'], 0, 5) ?>" />
                    </div>
                    <div title="Do students default to physically being in class?">
                        <label for="inClass">In Class</label>
                    </div>
                    <div id="physical" title="Do students default to physically being in class?">
                        <input type="checkbox" name="inClass" id="inClass" value="1" <?= $defaults['inClass'] ? "checked" : "" ?> />
                    </div>
                </div>
                <input type="submit" value="Save Defaults" />
            </form>
            <h3 id="CAMSheader">
                CAMS Integration
            </h3>
            <form method="post" action="config">
                <div id="settings" class="settings">
                    <div>
                        <label for="course_id">Course</label>
                    </div>
                    <div>
                        <input autofocus type="number" required min="0" name="course_id" id="course_id" placeholder="CAMS Course ID" value="<?= $CAMS['course_id'] ?>" />
                    </div>

                    <div>
                        <label for="username">User</label>
                    </div>
                    <div>
                        <input required type="text" name="username" id="username" placeholder="CAMS Username" value="<?= $CAMS['username'] ?>" />
                    </div>

                    <div>
                        <label for="password">Pass</label>
                    </div>
                    <div>
                        <input required type="password" name="password" id="password" placeholder="Will not be stored" />
                    </div>
                </div>
                <input type="submit" value="Save CAMS settings / Retrieve Optional" />
                <h4>CAMS Optional / System will try to determine</h4>
                <div id="optional" class="settings">
                    <div>
                        <label for="AM_id">AM id</label>
                    </div>
                    <div>
                        <input type="number" min="0" name="AM_id" id="AM_id" placeholder="Course AM ID" value="<?= $CAMS['AM_id'] ?>" />
                    </div>

                    <div>
                        <label for="PM_id">PM id</label>
                    </div>
                    <div>
                        <input type="number" min="0" name="PM_id" id="PM_id" placeholder="Course PM ID" value="<?= $CAMS['PM_id'] ?>" />
                    </div>

                    <div>
                        <label for="SAT_id">SAT id</label>
                    </div>
                    <div>
                        <input type="number" min="0" name="SAT_id" id="SAT_id" placeholder="Course Saturday ID" value="<?= $CAMS['SAT_id'] ?>" />
                    </div>
                </div>
            </form>
        </div>
    </main>
</body>

</html>
