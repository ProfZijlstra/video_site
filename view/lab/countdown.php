<!DOCTYPE html> <?php global $MY_BASE ?>
<html>

<head>
    <title><?= $abbr ?> Lab Countdown</title>
    <meta charset="utf-8" />
    <meta name=viewport content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/lib/font-awesome-all.min.css">
    <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/common-1.3.css">
    <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/adm-1.0.css">
    <style>
        div#content label {
            display: inline-block;
            width: 60px;
        }

        div#countdown {
            font-size: 50px;
        }
        main nav.areas {
            margin-top: 50px;
        }
    </style>
    <script src="<?= $MY_BASE ?>/res/js/countdown-1.1.js"></script>
    <script>
        window.addEventListener("load", () => {
            COUNTDOWN.start(() => window.location.reload());
        });
    </script>
</head>

<body>
    <?php include 'header.php'; ?>
    <main>
        <nav class="back" title="Back">
            <a href="../../">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
        </nav>
        <?php include 'areas.php'; ?>
        <nav class="tools">
            <?php if (hasMinAuth('instructor')) { ?>
                <a href="edit">
                    <i title="Configure Lab" class="fa-solid fa-gear"></i>
                </a>
            <?php } ?>
        </nav>
        <div id="content">
            <h1>Lab: <?= $lab['name'] ?></h1>
            <div>
                <label>From:</label> <?= $lab['start'] ?>
            </div>
            <div>
                <label>To:</label> <?= $lab['stop'] ?>
            </div>
            <div id="countdown">
                <div>Start in <span id="days"><?= $start->days ?></span> day(s)</div>
                <div>
                    and <span id="hours"><?= $start->format('%H') ?></span>:<span id="minutes"><?= $start->format('%I') ?></span>:<span id="seconds"><?= $start->format('%S') ?></span>
                </div>
            </div>
        </div>
    </main>
</body>

</html>
