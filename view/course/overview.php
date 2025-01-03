<!DOCTYPE html>
<html>

<head>
    <title><?= $course ?> Videos</title>
    <meta charset="utf-8" />
    <meta name=viewport content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="res/css/lib/font-awesome-all.min.css">
    <link rel="stylesheet" href="res/css/common-1.3.css">
    <link rel="stylesheet" href="res/css/offering-1.3.css">
    <script src="res/js/offering.js"></script>
    <?php if (hasMinAuth('instructor')) { ?>
        <link rel="stylesheet" href="res/css/adm-1.0.css">
        <script src="https://unpkg.com/react@17/umd/react.production.min.js" crossorigin></script>
        <script src="https://unpkg.com/react-dom@17/umd/react-dom.production.min.js" crossorigin></script>
        <script src="res/js/info.js"></script>
        <script src="res/js/adm_offering.js"></script>
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
        <?php if (hasMinAuth('instructor')) { ?>
            <nav class="tools">
                <?php if (! $isRemembered) { ?>
                    <i title="View Info" id="info-btn" class="fa-solid fa-eye"></i>
                    <i title="Edit Calendar" id="edit" class="far fa-edit"></i>
                    <i title="Clone Offering" id="clone" class="far fa-copy"></i>
                    <i title="Delete Offering" id="delete" class="far fa-trash-alt"></i>
                <?php } else { ?>
                    <a href="reAuth">
                        <i title="View Info" class="fas fa-info-circle"></i>
                        <i title="Edit Calendar" class="far fa-edit"></i>
                        <i title="Clone Offering" class="far fa-copy"></i>
                        <i title="Delete Offering" class="far fa-trash-alt"></i>
                    </a>
                <?php } ?>
                <a href="settings">
                    <i title="Offering Settings" class="fa-solid fa-gear"></i>
                </a>
            </nav>
        <?php } ?>

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
                    <?php $date = $start + ($w - 1) * 60 * 60 * 24 * $offering['daysPerLesson'] * $offering['lessonsPerPart'] + ($d - 1) * 60 * 60 * 24 * $offering['daysPerLesson']; ?>

                    <div class="data <?= $w == 1 ? 'w1' : '' ?> <?= $d == 1 ? 'd1 ' : '' ?><?= $offering['showDates'] && $date < $now ? 'done' : '' ?> <?= date('z', $date) == date('z', $now) ? 'curr' : '' ?>" id="<?= "W{$w}D{$d}" ?>" data-day="<?= "W{$w}D{$d}" ?>" data-day_id="<?= $days["W{$w}D{$d}"]['id'] ?>" data-text="<?= $days["W{$w}D{$d}"]['desc'] ?>">
                        <div class="info"></div>
                        <a href="W<?= $w ?>D<?= $d ?>/">
                            <span class="day"><?= "W{$w}D{$d}" ?></span>
                            <span class="text"><?= $days["W{$w}D{$d}"]['desc'] ?></span>
                        </a>
                        <?php if ($offering['showDates']) { ?>
                            <time><?= date('M j Y', $date); ?></time>
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
        <div id="overlay">
            <i id="close-overlay" class="fas fa-times-circle"></i>
            <div id="clone_modal" class="modal hide">
                <h2>Clone Offering</h2>
                <form method="POST" action="clone" id="clone_form">
                    <input type="hidden" name="offering_id" value=<?= $offering['id'] ?> />
                    <div class="line">
                        <label>New Block:</label>
                        <input name="block" id="block" required pattern="20\d{2}-\d{2}[^\/]*" title="Block code"/>
                    </div>
                    <div class="line">
                        <label>Faculty</label>
                        <select name="fac_user_id" id="fac_user_id">
                            <?php foreach ($faculty as $user) { ?>
                                <option value="<?= $user['id'] ?>"><?= $user['firstname'].' '.$user['lastname'] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="line">
                        <label>Start Date:</label>
                        <input type="date" name="start" required />
                    </div>
                    <div>
                        <label>Days per Lesson</label>
                        <input type="number" name="daysPerLesson" value="<?= $offering['daysPerLesson'] ?>" />
                        <label>Lessons per Part (cols)</label>
                        <input type="number" name="lessonsPerPart" value="<?= $offering['lessonsPerPart'] ?>" />
                        <label>Parts (rows)</label>
                        <input type="number" name="lessonParts" value="<?= $offering['lessonParts'] ?>" />
                    </div>
                    <div class="indent">
                        <label class="checkbox">
                            <input type="checkbox" name="hasQuiz" value="1" <?= $offering['hasQuiz'] ? 'checked' : '' ?> />
                            Has Quiz
                        </label>
                        <label class="checkbox">
                            <input type="checkbox" name="hasLab" value="1" <?= $offering['hasLab'] ? 'checked' : '' ?> />
                            Has Lab
                        </label>
                        <label class="checkbox">
                            <input type="checkbox" name="showDates" value="1" <?= $offering['showDates'] ? 'checked' : '' ?> />
                            Show Dates
                        </label>
                        <label class="checkbox">
                            <input type="checkbox" name="usesFlowcharts" value="1" <?= $offering['usesFlowcharts'] ? 'checked' : '' ?> />
                            Flowcharts
                        </label>

                    </div>
                    <div class="indent">
                        <label class="checkbox">
                            <input type="checkbox" name="cloneFiles" value="1" checked />
                            Clone Public Files
                        </label>
                        <label class="checkbox">
                            <input type="checkbox" name="cloneComments" value="1" />
                            Clone Comments
                        </label>
                    </div>
                    <div class="submit">
                        <button>Submit</button>
                    </div>
                </form>
            </div>
            <div id="delete_modal" class="modal hide">
                <h2>Delete Offering</h2>
                <p>
                    Please confirm that you want to delete this offering.
                </p>
                <form method="POST" action="delete">
                    <input type="hidden" name="offering_id" value=<?= $offering['id'] ?> />
                    <div class="submit">
                        <button type="button" id="cancel_delete">Cancel</button>
                        <button type="submit" id="ok_delete">OK</button>
                    </div>
                </form>
            </div>
            <div id="edit_modal" class="modal hide">
                <h2>Edit Day Title</h2>
                <form method="POST" action="edit">
                    <input type="hidden" name="day_id" id="day_id" value="" />
                    <div class="line">
                        <label>Title:</label>
                        <input name="desc" id="day_desc" placeholder="" />
                    </div>
                    <div class="submit">
                        <button>Submit</button>
                    </div>
                </form>
            </div>

            <div id="content">
                <!-- React puts it's table with view data here -->
            </div>
        </div>
    <?php } ?>

</body>

</html>
