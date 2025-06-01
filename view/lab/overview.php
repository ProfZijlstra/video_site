<!DOCTYPE html>
<html>

<head>
    <title><?= $block ?> Labs</title>
    <meta charset="utf-8" />
    <meta name=viewport content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="res/css/lib/font-awesome-all.min.css">
    <link rel="stylesheet" href="res/css/common-1.3.css">
    <link rel="stylesheet" href="res/css/offering-1.3.css">
    <link rel="stylesheet" href="res/css/adm-1.0.css">
    <link rel="stylesheet" href="res/css/lab-1.9.css">
    <style>
        div#days {
            grid-template-columns: <?php for ($i = 0; $i < $offering['lessonsPerPart']; $i++) { ?>auto <?php } ?>;
            width: <?= 9 * $offering['lessonsPerPart'] ?>vw;
        }
    </style>
    <script src="res/js/addDialog.js"></script>
</head>

<body id="gradeOverview">
    <?php include 'header.php'; ?>
    <main>
        <?php include 'areas.php'; ?>

        <?php if (hasMinAuth('student')) { ?>
            <nav class="tools">
                <a href="lab/report">
                    <i title="Download Lab Totals Report" class="fa-solid fa-square-poll-vertical"></i>
                </a>
            </nav>
        <?php } ?>

        <div id="days" class="lab">
            <?php if ($offering['lessonsPerPart'] == 7 && $offering['showDates']) { ?>
                <div class="dayHeader">Monday</div>
                <div class="dayHeader">Tuesday</div>
                <div class="dayHeader">Wednesday</div>
                <div class="dayHeader">Thursday</div>
                <div class="dayHeader">Friday</div>
                <div class="dayHeader">Saturday</div>
                <div class="dayHeader">Sunday</div>
            <?php } ?>
            <?php for ($w = 1; $w <= $offering['lessonParts']; $w++) { ?>
                <?php for ($d = 1; $d <= $offering['lessonsPerPart']; $d++) { ?>
                    <?php $date = $start + ($w - 1) * 60 * 60 * 24 * $offering['daysPerLesson'] * $offering['lessonsPerPart'] + ($d - 1) * 60 * 60 * 24 * $offering['daysPerLesson']; // 79200 is 10pm?>
                    <?php $next = $date + 60 * 60 * 24 * $offering['daysPerLesson']; ?>

                    <div class="data <?= $w == 1 ? 'w1' : '' ?> <?= $d == 1 ? 'd1 ' : '' ?><?= $date < $now ? 'done' : '' ?> <?= date('z', $date) == date('z', $now) ? 'curr' : '' ?>" id="<?= "W{$w}D{$d}" ?>" data-day="<?= "W{$w}D{$d}" ?>" data-day_id="<?= $days["W{$w}D{$d}"]['id'] ?>" data-date="<?= date('Y-m-d', $date) ?>" data-next="<?= date('Y-m-d', $next) ?>">

                        <?php if (hasMinAuth('instructor')) { ?>
                            <?php if (! $isRemembered) { ?>
                                <i title="Add Lab" class="far fa-plus-square"></i>
                            <?php } else { ?>
                                <a href="reAuth">
                                    <i title="Add Lab" class="far fa-plus-square"></i>
                                </a>
                            <?php } ?>
                        <?php } ?>

                        <?php foreach ($labTimes as $labTime) { ?>
                            <?php if ($date < $labTime['start'] || $date > $labTime['stop']) {
                                continue;
                            } ?>
                            <?php
                            $lab = $labTime['lab'];
                            $grade = $graded[$lab['id']];
                            $gradeStatus = '';
                            if ($grade['answers'] != 0) {
                                $gradeStatus = 'graded';
                                if ($grade['ungraded'] != 0) {
                                    $gradeStatus = 'ungraded';
                                }
                            }
                            ?>
                            <div class="lab">
                                <a href="<?= 'lab/'.$lab['id'] ?>" class="<?= $lab['visible'] ? 'visible' : 'invisible' ?> <?= $gradeStatus ?>" title="<?= $gradeStatus ?>">
                                    <?= $lab['name'] ?>
                                </a>
                                <?php if (hasMinAuth('instructor')) { ?>
                                    <a class="edit" href="<?= 'lab/'.$lab['id'].'/edit' ?>">
                                        <i title="Edit Lab" class="fa-solid fa-gear"></i>
                                    </a>
                                <?php } ?>
                                <?php if (hasMinAuth('assistant')) { ?>
                                    <a href="<?= 'lab/'.$lab['id'].'/grade' ?>">
                                        <i title="Grade Lab" class="fa-solid fa-magnifying-glass"></i>
                                    </a>
                                <?php } ?>
                            </div>
                        <?php } ?>

                        <time>
                            <?= date('M j Y', $date); ?>
                        </time>
                    </div>
                <?php } ?>
            <?php } ?>
        </div>
    </main>

        <dialog id="add_modal" class="modal">
            <i id="closeAddDialog" class="fas fa-times-circle close"></i>
            <h3>Add Lab</h3>
            <form action="lab" method="post" id="add_form">
                <input type="hidden" name="day_id" value="<?= $day_id ?>" id="day_id" />
                <div>
                    <label>From:</label>
                    <input id="startdate" type="date" name="startdate" value="<?= date('m/d/Y', $date) ?>" />
                    <input type="time" name="starttime" value="10:00" />
                </div>
                <div>
                    <label>To:</label>
                    <?php $next = $date + 60 * 60 * 24 * $offering['daysPerLesson']; ?>
                    <input id="stopdate" type="date" name="stopdate" value="<?= date('m/d/Y', $next) ?>" />
                    <input type="time" name="stoptime" value="10:00" />
                </div>
                <div>
                    <label>Name:</label>
                    <input type="text" name="name" id="name" placeholder="name" />
                </div>
                <div class="btn"><button>Add Lab</button></div>
            </form>
        </dialog>

</body>

</html>
