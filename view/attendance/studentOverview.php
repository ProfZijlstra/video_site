<!DOCTYPE html> <?php global $MY_BASE ?>
<html>

<head>
    <title><?= $block ?> Attendance</title>
    <meta charset="utf-8" />
    <meta name=viewport content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/lib/font-awesome-all.min.css">
    <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/common-1.3.css">
    <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/offering-1.4.css">
    <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/adm-1.0.css">
    <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/attendance-1.0.css">
    <style>
        div#days {
            grid-template-columns: <?php for ($i = 0; $i < $offering['lessonsPerPart']; $i++) { ?>auto <?php } ?>;
            width: <?= 9 * $offering['lessonsPerPart'] ?>vw;
        }
        
        div.meeting {
            font-size: 15px;
            cursor: help;
        }
        div.meeting.absent {
            color: red;
        }
        div.meeting.present {
            color: green;
        }
        div.meeting.tardy {
            text-shadow: 0 0 1px var(--border);
            color: orange;
        }
        div.meeting.excused {
            color: blue;
        }
    </style>
</head>

<body>
    <?php include 'header.php'; ?>
    <main>
        <nav class="tools">
        </nav>
        <?php include 'areas.php'; ?>

        <div id="days" data-amstart="<?= $defaults['AM_start'] ?>" data-amstop="<?= $defaults['AM_stop'] ?>" data-pmstart="<?= $defaults['PM_start'] ?>" data-pmstop="<?= $defaults['PM_stop'] ?>">
            <?php for ($w = 1; $w <= $offering['lessonParts']; $w++) { ?>
                <?php for ($d = 1; $d <= $offering['lessonsPerPart']; $d++) { ?>
                    <?php $date = $start + ($w - 1) * 60 * 60 * 24 * $offering['daysPerLesson'] * $offering['lessonsPerPart'] + ($d - 1) * 60 * 60 * 24 * $offering['daysPerLesson']; ?>

                    <div class="data <?= $w == 1 ? 'w1' : '' ?> <?= $d == 1 ? 'd1 ' : '' ?><?= $date < $now ? 'done' : '' ?> <?= date('z', $date) == date('z', $now) ? 'curr' : '' ?>" id="<?= "W{$w}D{$d}" ?>" data-day="<?= "W{$w}D{$d}" ?>" data-day_id="<?= $days["W{$w}D{$d}"]['id'] ?>" data-date="<?= date('Y-m-d', $date) ?>">

                        <?php foreach (['AM', 'PM'] as $stype) { ?>
                            <?php $session = $days["W{$w}D{$d}"][$stype]; ?>
                            <div class="session <?= $stype ?>" data-session_id="<?= $session['id'] ?>" data-stype="<?= $stype ?>">
                                <?= $stype ?>
                                <?php foreach ($session['meetings'] as $meeting) { ?>
                                    <div class="meeting <?= $meeting['status'] ?>" title="<?= $meeting['status']?>">
                                        <?= $meeting['title'] ?>
                                        <?php if ($meeting['inClass']) { ?>
                                        <i title="Physically in classroom" class="fa-solid fa-chalkboard-user"></i>
                                        <?php } ?>
                                    </div>
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

</body>

</html>
