<!DOCTYPE html>
<html>
    <head>
        <title><?= $abbr ?> Quiz Countdown</title>
        <meta charset="utf-8" />
        <meta name=viewport content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="res/css/font-awesome-all.min.css">
        <link rel="stylesheet" href="res/css/common-1.1.css">
        <link rel="stylesheet" href="res/css/adm.css">
        <link rel="stylesheet" href="res/css/quiz-1.1.css">
        <style>
            div#content label {
                display: inline-block;
                width: 60px;
            }

            div#countdown {
                font-size: 50px;
            }
        </style>
        <script src="res/js/quiz/countdown.js"></script>
        <script>
window.addEventListener("load", () => {    
    COUNTDOWN.start(() => window.location.reload());
});
        </script>
    </head>
    <body>
        <?php include("header.php"); ?>
        <main>
            <nav class="back" title="Back">
                <a href="../quiz">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
            </nav>
            <nav class="tools">
            </nav>
            <div id="content">
                <h1>Quiz: <?= $quiz['name'] ?></h1>
                <div>
                    <label>From:</label> <?= $quiz['start'] ?>
                </div>
                <div>
                    <label>To:</label> <?= $quiz['stop'] ?>
                </div>
                <div id="countdown">
                    <div>Start in <span id="days"><?= $start->days ?></span> day(s)</div>
                    <div>
                        and <span id="hours"><?= $start->format("%H") ?></span>:<span id="minutes"><?= $start->format("%I") ?></span>:<span id="seconds"><?= $start->format("%S") ?></span>
                    </div>
                </div>
            </div>
        </main>
    </body>
</html>
