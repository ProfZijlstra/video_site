<!DOCTYPE html>
<html>

<head>
    <title><?= $block ?> Settings</title>
    <meta charset="utf-8" />
    <meta name=viewport content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="res/css/lib/font-awesome-all.min.css">
    <link rel="stylesheet" href="res/css/common-1.3.css">
    <link rel="stylesheet" href="res/css/adm.css">
    <style>
        #content h2,
        #content h3 {
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
                    <input type="number" id="daysPerLesson" value="<?= $offering['daysPerLesson'] ?>" />
                </div>
                <div>
                    Days Per Lesson
                </div>
                <div>
                    <input type="number" id="lessonsPerPart" value="<?= $offering['lessonsPerPart'] ?>" />
                </div>
                <div>
                    Lessons Per Part (columns in overview)
                </div>
                <div>
                    <input type="number" id="lessonParts" value="<?= $offering['lessonParts'] ?>" />
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
            </div>
        </div>
    </main>
</body>

</html>
