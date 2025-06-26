<!DOCTYPE html> <?php global $MY_BASE ?>
<html>

<head>
    <title><?= $course ?> Videos</title>
    <meta charset="utf-8" />
    <meta name=viewport content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/lib/font-awesome-all.min.css">
    <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/common-1.3.css">
    <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/offering-1.4.css">
    <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/adm-1.0.css">
<?php if (hasMinAuth('instructor')) { ?>
    <script src="<?= $MY_BASE ?>/res/js/offering.js"></script>
<?php } ?>
    <style>
        div#days {
            grid-template-columns: <?php for ($i = 0; $i < $offering['lessonsPerPart']; $i++) { ?>auto <?php } ?>;
            width: <?= 9 * $offering['lessonsPerPart'] ?>vw;
        }
    </style>
</head>

<body>
    <?php include 'header.php'; ?>
    <main>
        <nav class="tools">
        <?php if (hasMinAuth('instructor')) { ?>
            <i title="Toggle Configure Calendar" id="edit" class="fa-solid fa-gear"></i>
            <a href="settings">
                <i title="Offering Settings" class="fa-solid fa-gears"></i>
            </a>
        <?php } ?>
        </nav>

        <?php include 'areas.php'; ?>
        <div id="days">
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
                    <?php $date = $start + ($w - 1) * 60 * 60 * 24 * $offering['daysPerLesson'] * $offering['lessonsPerPart'] + ($d - 1) * 60 * 60 * 24 * $offering['daysPerLesson'] + 12 * 60 * 60; ?>
                    <?php $next = $date + 60 * 60 * 24 * $offering['daysPerLesson']; ?>

                    <div class="data <?= $w == 1 ? 'w1' : '' ?> <?= $d == 1 ? 'd1 ' : '' ?><?= $offering['showDates'] && $date < $now ? 'done' : '' ?> <?= date('z', $date) == date('z', $now) ? 'curr' : '' ?>" 
                        id="<?= "W{$w}D{$d}" ?>" data-day="<?= "W{$w}D{$d}" ?>"  
                        data-text="<?= $days["W{$w}D{$d}"]['desc'] ?>" 
                        data-day_id="<?= $days["W{$w}D{$d}"]['id'] ?>" 
                        data-date="<?= date('Y-m-d', $date) ?>" 
                        data-next="<?= date('Y-m-d', $next) ?>">
                        <span class="title">
                            <a href="W<?= $w ?>D<?= $d ?>/">
                                <span class="text"><?= $days["W{$w}D{$d}"]['desc'] ?></span>
                            </a>
                            <i class="far fa-edit hide" 
                                title="Edit Day Title"
                                data-day_id="<?= $days["W{$w}D{$d}"]['id'] ?>"></i>
                        </span>

                        <i title="Add Quiz / Lab" class="fa-regular fa-square-plus hide"></i>
                        <div class="icons">
                        <!-- Quiz icons -->
                        <?php foreach ($days["W{$w}D{$d}"]['quizzes'] as $quiz) { ?>
                            <?php $quizStatus = $quiz_status[$quiz['id']] ?? 'not-started'; ?>
                            <a href="<?= 'quiz/'.$quiz['id'] ?>" class="<?= $quiz['visible'] ? 'visible' : 'invisible' ?> <?= $quizStatus ?>" 
                                title="<?= $quiz['visible'] ? '' : 'Invisible' ?> Quiz: <?= "{$quiz['name']} ({$quizStatus})" ?>">
                                    <i class="fa-solid fa-vial"></i>
                                </a>
                        <?php } ?>

                        <!-- Lab icons -->
                        <?php foreach ($labTimes as $labTime) { ?>
                            <?php if ($date < $labTime['start'] || $date > $labTime['stop']) {
                                continue;
                            } ?>
                            <?php $lab = $labTime['lab']; ?>
                            <?php $labStatus = $lab_status[$lab['id']] ?? 'not-started'; ?>
                            <a href="<?= 'lab/'.$lab['id'] ?>" class="<?= $lab['visible'] ? 'visible' : 'invisible' ?> <?= $labStatus ?>" 
                                title="<?= $lab['visible'] ? '' : 'Invisible' ?> Lab: <?= "{$lab['name']} ({$labStatus})" ?>")>
                                <i class="fa-solid fa-flask"></i>
                            </a>
                        <?php } ?>
                        </div>

                        <!-- Date -->
                        <?php if ($offering['showDates']) { ?>
                        <time>
                            <?= date('M', $date); ?>
                            <span class="date"><?= date('j', $date); ?></span>
                        </time>
                        <?php } ?>
                    </div>

                <?php } ?>
            <?php } ?>
        </div>
        <div id="total">
            <div class="info"></div>
        </div>
    </main>

    <?php if (hasMinAuth('instructor')) { ?>
        <dialog id="editDialog" class="modal">
            <i id="closeEditDialog" class="fas fa-times-circle close"></i>
            <h3>Edit Day Title</h3>
            <form method="POST" action="edit" id="editForm">
                <input type="hidden" name="day_id" id="day_id" value="" />
                <div class="line">
                    <label>Title:</label>
                    <input name="desc" id="day_desc" autofocus />
                </div>
                <div class="submit">
                    <button>Submit</button>
                </div>
            </form>
        </dialog>

        <dialog id="add_modal" class="modal">
            <i id="closeAddDialog" class="fas fa-times-circle close"></i>
            <h3>
                Add 
                <select autofocus id="add_select">
                    <option>Lab / Homework / Project</option>
                    <option>Quiz / Test / Exam</option>
                </select>
            </h3>
            <form action="quiz" method="post" id="add_quiz" class="hide">
                <input type="hidden" name="day_id" value="<?= $day_id ?>" id="quiz_day_id" />
                <div>
                    <label>From:</label>
                    <input id="quiz_startdate" type="date" name="startdate" value="<?= date('m/d/Y', $date) ?>" />
                    <input type="time" name="starttime" value="10:00" />
                </div>
                <div>
                    <label>To:</label>
                    <input id="quiz_stopdate" type="date" name="stopdate" value="<?= date('m/d/Y', $date) ?>" />
                    <input type="time" name="stoptime" value="10:30" />
                </div>
                <div>
                    <label>Name:</label>
                    <input id="quiz_name" type="text" name="name" class="name" placeholder="name" />
                </div>
                <div class="btn"><button>Add Quiz</button></div>
            </form>
            <form action="lab" method="post" id="add_lab">
                <input type="hidden" name="day_id" value="<?= $day_id ?>" id="lab_day_id" />
                <div>
                    <label>From:</label>
                    <input id="lab_startdate" type="date" name="startdate" value="<?= date('m/d/Y', $date) ?>" />
                    <input type="time" name="starttime" value="10:00" />
                </div>
                <div>
                    <label>To:</label>
                    <?php $next = $date + 60 * 60 * 24 * $offering['daysPerLesson']; ?>
                    <input id="lab_stopdate" type="date" name="stopdate" value="<?= date('m/d/Y', $next) ?>" />
                    <input type="time" name="stoptime" value="10:00" />
                </div>
                <div>
                    <label>Name:</label>
                    <input type="text" name="name" class="name" placeholder="name" />
                </div>
                <div class="btn"><button>Add Lab</button></div>
            </form>
        </dialog>
    <?php } ?>
</body>

</html>
