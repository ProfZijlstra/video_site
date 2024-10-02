<!DOCTYPE html>
<html>

<head>
    <title><?= $abbr ?> Lab Countdown</title>
    <meta charset="utf-8" />
    <meta name=viewport content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="res/css/lib/font-awesome-all.min.css">
    <link rel="stylesheet" href="res/css/common-1.3.css">
    <link rel="stylesheet" href="res/css/adm-1.0.css">
    <style>
        div#content label {
            display: inline-block;
            width: 60px;
        }
    </style>
    <script src="res/js/back.js"></script>
</head>

<body>
    <?php include("header.php"); ?>
    <main>
        <nav class="back" title="Back">
            <a href="../lab">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
        </nav>
        <nav class="tools">
        </nav>
        <div id="content">
            <h1>Lab: <?= $lab['name'] ?> - Requires a Group</h1>
            <div>
                <label>From:</label> <?= $lab['start'] ?>
            </div>
            <div>
                <label>To:</label> <?= $lab['stop'] ?>
            </div>
            <div>
                <p>This is a group based lab. You do not appear to be in a group for this course.</p>
                <p>Please <strong>contact your instructor</strong> to be added to a group.</p>
            </div>
        </div>
    </main>
</body>

</html>
