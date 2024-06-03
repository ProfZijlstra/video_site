<!DOCTYPE html>
<html>

<head>
    <title><?= $block ?> Labs</title>
    <meta charset="utf-8" />
    <meta name=viewport content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="res/css/lib/font-awesome-all.min.css">
    <link rel="stylesheet" href="res/css/common-1.2.css">
    <link rel="stylesheet" href="res/css/offering-1.2.css">
    <link rel="stylesheet" href="res/css/adm.css">
    <style>
        div#days {
            grid-template-columns: <?php for ($i = 0; $i < $offering['lessonsPerPart']; $i++) : ?>auto <?php endfor; ?>;
            width: <?= 9 * $offering['lessonsPerPart'] ?>vw;
        }
    </style>
    <script src="res/js/assignment.js"></script>
</head>

<body>
    <?php include("header.php"); ?>
    <main>
        <nav class="areas">
            <div title="Videos"><a href="../<?= $block ?>/"><i class="fas fa-film"></a></i></div>
            <?php if ($offering['hasQuiz']) : ?>
                <div title="Quizzes"><a href="quiz"><i class="fas fa-vial"></i></a></div>
            <?php endif; ?>
            <div title="Labs" class="active"><i class="fas fa-flask"></i></div>
            <?php if (hasMinAuth('assistant')) : ?>
                <div title="Attendance"><a href="attendance"><i class="fas fa-user-check"></i></a></div>
            <?php endif; ?>
            <?php if (hasMinAuth('instructor')) : ?>
                <div title="Enrolled"><a href="enrolled"><i class="fas fa-user-friends"></i></a></div>
            <?php endif; ?>
            <div title="Back to My Courses">
                <a href="../../">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
            </div>
        </nav>

        <div id="days" class="lab">
            <?php if ($offering['lessonsPerPart'] == 7 && $offering['showDates']): ?>
                <div class="dayHeader">Monday</div>
                <div class="dayHeader">Tuesday</div>
                <div class="dayHeader">Wednesday</div>
                <div class="dayHeader">Thursday</div>
                <div class="dayHeader">Friday</div>
                <div class="dayHeader">Saturday</div>
                <div class="dayHeader">Sunday</div>
            <?php endif; ?>
            <?php for ($w = 1; $w <= $offering['lessonParts']; $w++) : ?>
                <?php for ($d = 1; $d <= $offering['lessonsPerPart']; $d++) : ?>
                    <?php $date = $start + ($w - 1) * 60 * 60 * 24 * $offering['daysPerLesson'] * $offering['lessonsPerPart'] + ($d - 1) * 60 * 60 * 24 * $offering["daysPerLesson"]; ?>
                    <?php $next = $date + 60 * 60 * 24 * $offering['daysPerLesson']; ?>

                    <div class="data <?= $w == 1 ? "w1" : "" ?> <?= $d == 1 ? "d1 " : "" ?><?= $date < $now ? "done" : "" ?> <?= date("z", $date) == date("z", $now) ? "curr" : "" ?>" id="<?= "W{$w}D{$d}" ?>" data-day="<?= "W{$w}D{$d}" ?>" data-day_id="<?= $days["W{$w}D{$d}"]["id"] ?>" data-date="<?= date("Y-m-d", $date) ?>" data-next="<?= date("Y-m-d", $next) ?>">

                        <?php if (hasMinAuth('instructor')) : ?>
                            <?php if (!$isRemembered) : ?>
                                <i title="Add Lab" class="far fa-plus-square"></i>
                            <?php else : ?>
                                <a href="reAuth">
                                    <i title="Add Lab" class="far fa-plus-square"></i>
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php foreach ($days["W{$w}D{$d}"]['labs'] as $lab) : ?>
                            <?php
                            $grade = $graded[$lab['id']];
                            $gradeStatus = '';
                            if ($grade['answers'] != 0) {
                                $gradeStatus = 'graded';
                                if ($grade['ungraded'] != 0) {
                                    $gradeStatus = 'ungraded';
                                }
                            }
                            ?>
                            <div>
                                <a href="<?= "lab/" . $lab['id'] ?>" class="<?= $lab['visible'] ? 'visible' : 'invisible' ?> <?= $gradeStatus ?>" title="<?= $lab['visible'] ? 'visible' : 'invisible' ?> <?= $gradeStatus ?>">
                                    <?= $lab['name'] ?>
                                </a>
                                <?php if (hasMinAuth('instructor')) : ?>
                                    <a class="edit" href="<?= "lab/" . $lab['id'] . "/edit" ?>">
                                        <i title="Edit Lab" class="fa-solid fa-gear"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if (hasMinAuth('assistant')) : ?>
                                    <a href="<?= "lab/" . $lab['id'] . "/grade" ?>">
                                        <i title="Grade Lab" class="fa-solid fa-magnifying-glass"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>

                        <time>
                            <?= date("M j Y", $date); ?>
                        </time>
                    </div>
                <?php endfor ?>
            <?php endfor ?>
        </div>
    </main>

    <div id="overlay">
        <i id="close-overlay" class="fas fa-times-circle"></i>
        <div id="add_modal" class="modal hide">
            <h3>Add Lab</h3>
            <form action="lab" method="post" id="add_form">
                <input type="hidden" name="day_id" value="<?= $day_id ?>" id="day_id" />
                <div>
                    <label>From:</label>
                    <input id="startdate" type="date" name="startdate" value="<?= date("m/d/Y", $date) ?>" />
                    <input type="time" name="starttime" value="10:00" />
                </div>
                <div>
                    <label>To:</label>
                    <?php $next = $date + 60 * 60 * 24 * $offering['daysPerLesson']; ?>
                    <input id="stopdate" type="date" name="stopdate" value="<?= date("m/d/Y", $next) ?>" />
                    <input type="time" name="stoptime" value="10:00" />
                </div>
                <div>
                    <label>Name:</label>
                    <input type="text" name="name" id="name" placeholder="name" />
                </div>
                <div class="btn"><button>Add Lab</button></div>
            </form>
        </div>
    </div>

</body>

</html>
