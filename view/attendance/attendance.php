<?php
$de = false;
$cols = $offering['lessonsPerPart'];
if ($cols < 7) {
    $de = true;
    $cols += 1;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title><?= $block ?> Attendance</title>
    <meta charset="utf-8" />
    <meta name=viewport content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="res/css/font-awesome-all.min.css">
    <link rel="stylesheet" href="res/css/common-1.1.css">
    <link rel="stylesheet" href="res/css/offering-1.1.css">
    <link rel="stylesheet" href="res/css/adm.css">
    <link rel="stylesheet" href="res/css/attendance.css">
    <script src="res/js/attendance.js"></script>
    <style>
        div#days {
            grid-template-columns: <?php for ($i = 0; $i < $cols; $i++): ?>auto <?php endfor; ?>;
            width: <?= 9 * $cols ?>vw;
        }
        <?php if ($de): ?>
            #days .fa-black-tie {
                right: 4vw;
            }
        <?php endif; ?>
    </style>
</head>

<body>
    <?php include("header.php"); ?>
    <main>
        <nav class="areas">
            <div title="Videos"><a href="../<?= $block ?>/"><i class="fas fa-film"></a></i></div>
            <?php if ($offering['hasQuiz']): ?>
            <div title="Quizzes"><a href="quiz"><i class="fas fa-vial"></i></a></div>
            <?php endif; ?>
            <?php if ($offering['hasLab']): ?>
            <div title="Labs"><i class="fas fa-flask"></i></div>
            <?php endif; ?>
            <div title="Attendance" class="active"><i class="fas fa-user-check"></i></div>
            <div title="Enrolled"><a href="enrolled"><i class="fas fa-user-friends"></i></a></div>
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
                        data-date="<?= date("Y-m-d", $date) ?>">

                        <?php if ($d == 7): ?>
                            <a href="physical/W<?= $w ?>">
                                <i title="Physical Classroom Attendance Report" class="fas fa-chalkboard-teacher"></i>
                            </a>
                            <?php if ($w == 4) : ?>
                                <a href="professionalism">
                                    <i title="Professionalism Report" class="fab fa-black-tie"></i>
                                </a>
                            <?php endif; ?>
                        <?php else : ?>
                            <?php foreach (["AM", "PM"] as $stype): ?>
                                <div class="session <?= $stype ?>" data-session_id="<?= $days["W{$w}D{$d}"][$stype]["id"] ?>"
                                    data-stype="<?= $stype ?>">
                                    <?= $stype ?>
                                    <i title="Add Meeting" class="far fa-plus-square"></i>
                                    <?php if ($days["W{$w}D{$d}"][$stype]["meetings"]) : ?>
                                    <a href="<?= "attendance/W{$w}D{$d}/$stype" ?>">
                                        <i title="Export Attendance" class="fas fa-cloud-upload-alt"></i>
                                    </a>
                                    <?php endif; ?>

                                <?php foreach ($days["W{$w}D{$d}"][$stype]["meetings"] as $meeting) : ?>
                                    <div class="meeting">
                                        <a href="meeting/<?= $meeting["id"] ?>">
                                            <?= $meeting["title"] ?>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <time>
                            <?= date("M j Y", $date); ?>
                        </time>
                    </div>

                    <?php // for DE courses add a column to show professionalism link ?>
                    <?php if($d == $offering['lessonsPerPart'] && $d < $cols): ?>
                        <div class="data <?= $w == 1 ? "w1" : "" ?>">
                            <?php if ($w == $offering['lessonParts']): ?>
                                <a href="professionalism">
                                    <i title="Professionalism Report" class="fab fa-black-tie"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endfor ?>
            <?php endfor ?>
        </div>
    </main>

    <div id="overlay">
        <i id="close-overlay" class="fas fa-times-circle"></i>
        <div class="modal">
            <h3>Add a Meeting</h3>

            <h4>Upload a Teams Meeting</h4>
            <form name="import" action="" method="post" enctype="multipart/form-data" id="upload_form">
                <input type="hidden" id="session_id" name="session_id" />
                <div>
                    <label>Start</label>
                    <input type="text" name="start" id="start" required pattern="([0-1]\d|2[0-3]):[0-5]\d(:[0-5]\d)?" title="24 hour time using colon separated hours, minutes and optionally seconds. Eg: 13:37"/>
                </div>
                <div>
                    <label>File*</label>
                    <input type="file" id="list_file" name="list" required />
                </div>
                <div class="btn"><button>Upload Meeting</button></div>
                <p class="right">*Filename will be used as meeting title</p>
            </form>

            <h4>Or Manually Create a Meeting</h4>
            <form name="create" action="meeting" method="post"> 
                <input type="hidden" id="manual_session_id" name="session_id" />
                <div>
                    <label>Title</label>
                    <input type="text" name="title" id="manual_title" required />
                </div>
                <div>
                    <label>Date</label>
                    <input type="date" name="date" id="manual_date" required />
                </div>
                <div>
                    <label>Start</label>
                    <input type="text" name="start" id="manual_start" required pattern="([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?" title="24 hour time using colon separated hours, minutes and optionally seconds. Eg: 13:37"/>
                </div>
                <div>
                    <label>Stop</label>
                    <input type="text" name="stop" id="manual_stop" required pattern="([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?" title="24 hour time using colon separated hours, minutes and optionally seconds. Eg: 13:37"/>
                </div>
                <div class="btn">
                    <button type="submit">Create Meeting</button>
                </div>
            </form>
        </div>

    </div>

</body>

</html>