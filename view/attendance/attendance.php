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
    <script src="<?= $MY_BASE ?>/res/js/attendance-1.3.js"></script>
    <style>
        div#days {
            grid-template-columns: <?php for ($i = 0; $i < $offering['lessonsPerPart']; $i++) { ?>auto <?php } ?>;
            width: <?= 9 * $offering['lessonsPerPart'] ?>vw;
        }
        div#days .data div.meeting {
            font-size: 8pt;
        }

        i.fa-cloud-upload-alt {
            color: gray;
        }

        i.fa-cloud-upload-alt.GENERATED {
            color: black;
        }

        i.fa-cloud-upload-alt.EXPORTED {
            color: green;
        }
        .modal h4 {
            margin-bottom: 5px;
        }
        #offset {
            width: 50px;
        }
        #file {
            width: auto;
        }
        #addExcused {
            label {
                width: 100px;
            }
            input, select {
                width: 585px;
            }
        }
    </style>
</head>

<body>
    <?php include 'header.php'; ?>
    <main>
        <nav class="tools">
            <a href="attendance/config">
                <i title="Configure" class="fa-solid fa-gear"></i>
            </a>
            <a href="professionalism">
                <i title="Professionalism Report" class="fab fa-black-tie"></i>
            </a>
            <i id="physical_icon" title="Physical Classroom Attendance" class="fas fa-chalkboard-teacher"></i>
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
                                <i title="Add Meeting" class="far fa-plus-square"></i>
                                <i title="Excused Absences" class="fa-solid fa-user-xmark <?= isset($excused[$session['id']]) ? '' : 'inactive' ?>" data-excused='<?= json_encode($excused[$session['id']]) ?>'>
                                </i>
                                <?php if ($session['meetings']) { ?>
                                    <a href="<?= "attendance/W{$w}D{$d}/{$stype}#export" ?>">
                                        <i title="Export Attendance <?= $session['status'] ?>" class="fas fa-cloud-upload-alt <?= $session['status'] ?>"></i>
                                    </a>
                                <?php } ?>

                                <?php foreach ($session['meetings'] as $meeting) { ?>
                                    <div class="meeting">
                                        <a href="meeting/<?= $meeting['id'] ?>">
                                            <?= $meeting['title'] ?>
                                        </a>
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

        <dialog id="addMeeting" class="modal">
            <i id="closeAddMeeting" class="fas fa-times-circle close"></i>
            <h3>Add a Meeting</h3>

            <form name="create" action="meeting" method="post" enctype="multipart/form-data">
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
                    <input type="text" name="start" id="manual_start" required pattern="([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?" title="24 hour time using colon separated hours, minutes and optionally seconds. Eg: 13:37" />
                </div>
                <div>
                    <label>Stop</label>
                    <input type="text" name="stop" id="manual_stop" required pattern="([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?" title="24 hour time using colon separated hours, minutes and optionally seconds. Eg: 13:37" />
                </div>
                <h4>Teams Meeting (optional)</h4>
                <div>
                    <label>File</label>
                    <input type="text" name="offset" id="offset" value="<?= $defaults['tz_offset'] ?>" pattern="(-?[01]\d|2[0-3]):[0-5]\d" title="Timezone difference. Eg: -01:00" />
                    <input type="file" name="file" id="file" > 
                </div>
                <div class="btn">
                    <button type="submit">Create Meeting</button>
                </div>
            </form>
        </dialog>

        <dialog id="addExcused" class="modal">
            <i id="closeAddExcused" class="fas fa-times-circle close"></i>
            <h3>Excused Absences</h3>
            <div id="none">None so far</div>
            <div id="excused_list" class="hidden"></div>
            <h3>Add a Student</h3>
            <p>Only works for excuses added before a meeting happens</p>
            <form method="post" action="excuse">
                <input type="hidden" name="session_id" id="excused_session_id" />
                <div>
                    <label>Add Student</label>
                    <select name="teamsName">
                        <?php foreach ($enrollment as $student) { ?>
                            <option><?= $student['teamsName'] ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div>
                    <label>Reason</label>
                    <input name="reason" />
                </div>
                <div class="btn">
                    <button type="submit">Mark Excused</button>
                </div>
            </form>
        </dialog>

        <dialog id="physicalModal" class="modal">
            <i id="closePhysicalModal" class="fas fa-times-circle close"></i>
            <h3>Physical Attendance Report</h3>
            <div>
                <label>Week</label>
                <select id="week">
                    <?php for ($w = 1; $w <= $offering['lessonParts']; $w++) { ?>
                        <option value="<?= $w ?>">Week <?= $w ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="btn">
                <button type="button" id="physicalBtn">Get Report</button>
            </div>
        </dialog>

</body>

</html>
