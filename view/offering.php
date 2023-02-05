<!DOCTYPE html>
<html>
    <head>
        <title><?= $course ?> Videos</title>
        <meta charset="utf-8" />
        <meta name=viewport content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="res/css/font-awesome-all.min.css">
        <link rel="stylesheet" href="res/css/common-1.1.css">
		<link rel="stylesheet" href="res/css/offering-1.1.css">
        <script src="res/js/offering.js"></script>
        <?php if ($_user_type === 'admin') : ?>
            <link rel="stylesheet" href="res/css/adm.css">
            <script src="https://unpkg.com/react@17/umd/react.production.min.js" crossorigin></script>
            <script src="https://unpkg.com/react-dom@17/umd/react-dom.production.min.js" crossorigin></script>
            <script src="res/js/info.js"></script>
            <script src="res/js/adm_offering.js"></script>
        <?php endif; ?>
        <style>
        div#days {
            grid-template-columns: <?php for ($i = 0; $i < $offering['lessonsPerPart']; $i++): ?>auto <?php endfor; ?>;
            width: <?= 9 * $offering['lessonsPerPart'] ?>vw;
        }
        </style>
    </head>
    <body>
        <?php include("header.php"); ?>
        <main>
            <?php if ($_user_type === 'admin') : ?>
            <nav class="tools">
                <a href="settings">
                    <i title="Offering Settings" class="fa-solid fa-gear"></i>
                </a>
                <i title="View Info" id="info-btn" class="fas fa-info-circle"></i>
                <i title="Edit Calendar" id="edit" class="far fa-edit"></i>
                <i title="Clone Offering" id="clone" class="far fa-copy"></i>
            </nav>
            <?php endif; ?>

            <nav class="areas">
                <div title="Videos" class="active"><i class="fas fa-film"></i></div>
                <?php if ($offering['hasQuiz']): ?>
                <div title="Quizzes"><a href="quiz"><i class="fas fa-vial"></i></a></div>
                <?php endif; ?>
                <?php if ($offering['hasLab']): ?>
                <div title="Labs"><i class="fas fa-flask"></i></div>
                <?php endif; ?>
                <?php if ($_user_type === 'admin') : ?>
                <div title="Attendance"><a href="attendance"><i class="fas fa-user-check"></i></a></div>
                <div title="Enrolled"><a href="enrolled"><i class="fas fa-user-friends"></i></a></div>
                <?php endif; ?>
                <div title="Back to My Courses">
                    <a href="../../">
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>
                </div>
            </nav>
            <div id="days">
                <?php for ($w = 1; $w <= $offering['lessonParts']; $w++): ?>
                    <?php for ($d = 1; $d <= $offering['lessonsPerPart']; $d++): ?>
                        <?php $date = $start + ($w - 1)*60*60*24*$offering['daysPerLesson']*$offering['lessonsPerPart'] + ($d - 1)*60*60*24*$offering["daysPerLesson"]; ?>

                        <div class="data <?= $w == 1 ? "w1" : "" ?> <?= $d == 1 ? "d1 " : "" ?><?= $date < $now ? "done" : "" ?> <?= date("z", $date) == date("z", $now)? "curr" : ""?>"
                            id="<?= "W{$w}D{$d}" ?>"
                            data-day="<?= "W{$w}D{$d}" ?>" 
                            data-day_id="<?= $days["W{$w}D{$d}"]["id"] ?>"
                            data-text="<?= $days["W{$w}D{$d}"]["desc"] ?>">
                        <div class="info"></div>
                        <a href="W<?= $w ?>D<?= $d ?>/">
                            <span class="day"><?= "W{$w}D{$d}" ?></span>
                            <span class="text"><?= $days["W{$w}D{$d}"]["desc"] ?></span>
                        </a>
                        <time><?= date("M j Y", $date);?></time>
                    </div>

                    <?php endfor ?>
                <?php endfor ?>
            </div>
			<div id="total"><div class="info"></div></div>
        </main>
        <?php if ($_user_type === 'admin') : ?>
            <div id="overlay">
                <i id="close-overlay" class="fas fa-times-circle"></i>
                <div id="content"></div>
            </div>
        <?php endif; ?>

    </body>
</html>
