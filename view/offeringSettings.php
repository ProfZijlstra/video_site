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
            #content h2, #content h3 {
                margin-bottom: 10px;
            }
            .settings {
                display: grid;
                grid-template-columns: 50px auto;
                row-gap: 10px;
                column-gap: 20px;
            }
            #settings input[type=number] {
                width: 40px;
            }
            .hide {
                display: none;
            }
        </style>
        <script src="res/js/offeringSettings.js"></script>
        <script src="res/js/ensureSaved.js"></script>
    </head>
    <body>
        <?php include("header.php"); ?>
        <main>
            <nav class="areas">
                <div title="Back">
                    <a href="../<?= $block ?>/">
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>
                </div>
            </nav>

            <div id="content">
                <h2><?= strtoupper($course) ?> Offering</h2>
                <div id="settings" class="settings">
                    <div>
                        Block
                    </div>
                    <div>
                        <input type="hidden" id="offering_id" value="<?= $offering['id'] ?>" />
                        <input id="block" value="<?= $block ?>" />
                    </div>
                    <div>
                        Start
                    </div>
                    <div>
                        <input type="date" id="start" value="<?= substr($offering['start'], 0, 10) ?>" />
                    </div>
                    <div>
                        <input type="number" id="daysPerLesson" value="<?= $offering['daysPerLesson']?>" />
                    </div>
                    <div>
                        Days Per Lesson
                    </div>
                    <div>
                        <input type="number" id="lessonsPerPart" value="<?= $offering['lessonsPerPart']?>" />
                    </div>
                    <div>
                        Lessons Per Part (columns in overview)
                    </div>
                    <div>
                        <input type="number" id="lessonParts" value="<?= $offering['lessonParts']?>" />
                    </div>
                    <div>
                        Lesson Parts (rows in overview)
                    </div>
                    <div>
                        <input type="checkbox" id="hasQuiz" <?= $offering['hasQuiz'] ? "checked" : "" ?> />
                    </div>
                    <div>
                        <label for="hasQuiz">Quizzes Enabled for this Offering</label>
                    </div>
                    <div>
                        <input type="checkbox" id="hasLab" <?= $offering['hasLab'] ? "checked" : "" ?> />
                    </div>
                    <div>
                        <label for="hasLab">Labs Enabled for this Offering</label>
                    </div>

                    <div>
                        <input type="checkbox" id="showDates" <?= $offering['showDates'] ? "checked" : "" ?> />
                    </div>
                    <div>
                        <label for="showDates">Show Dates for this Offering</label>
                    </div>
                    <div>
                        <input type="checkbox" id="usesFlowcharts" <?= $offering['usesFlowcharts'] ? "checked" : "" ?> />
                    </div>
                    <div>
                        <label for="usesFlowcharts">Students in this offering use Manalabs Flowcharts</label>
                    </div>

                    <div>
                        <input type="checkbox" id="hasCAMS" <?= $offering['hasCAMS'] ? "checked" : "" ?> />
                    </div>
                    <div>
                        <label for="hasCAMS">Enable attendance export to CAMS</label>
                    </div>
                </div>

                <h3 id="CAMSheader" class="<?= $offering['hasCAMS'] ? '' : 'hide' ?>"">CAMS Settings</h3>
                <div id="CAMSsettings" class="settings <?= $offering['hasCAMS'] ? '' : 'hide' ?>">
                    <div>
                        <label for="username">User</label>
                    </div>
                    <div>
                        <input type="text" id="username" placeholder="CAMS Username" value="<?= $CAMS['username'] ?>" />
                    </div>

                    <div>
                        <label for="course_id">Course</label>
                    </div>
                    <div>
                        <input type="number" min="0" id="course_id" placeholder="CAMS Course ID" value="<?= $CAMS['course_id'] ?>" />
                    </div>

                    <div>
                        <label for="AM_id">AM id</label>
                    </div>
                    <div>
                        <input type="number" min="0" id="AM_id" placeholder="Course AM ID" value="<?= $CAMS['AM_id'] ?>" />
                    </div>

                    <div>
                        <label for="PM_id">PM id</label>
                    </div>
                    <div>
                        <input type="number" min="0" id="PM_id" placeholder="Course PM ID" value="<?= $CAMS['PM_id'] ?>" />
                    </div>

                    <div>
                        <label for="SAT_id">SAT id</label>
                    </div>
                    <div>
                        <input type="number" min="0" id="SAT_id" placeholder="Course Saturday ID"  value="<?= $CAMS['SAT_id'] ?>" />
                    </div>
                </div>


                </div>
            </div>
        </main>
    </body>
</html>
