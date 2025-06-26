<!DOCTYPE html> <?php global $MY_BASE ?>
<html>

<head>
    <title>Course Offerings</title>
    <meta charset="utf-8" />
    <meta name=viewport content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/lib/font-awesome-all.min.css">
    <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/common-1.3.css">
    <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/adm-1.0.css">
    <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/courses.css">
    <?php if (hasMinAuth('instructor')) { ?>
    <script src="<?= $MY_BASE ?>/res/js/courses.js"></script>
    <?php } ?>
</head>

<body>
    <?php include 'header.php'; ?>
    <main>
        <nav class="tools">
            <?php if ($type == 'my') { ?>
            <a href="all">
                <i class="fa-solid fa-globe" title="All Courses"></i>
            </a>
            <?php } elseif ($type == 'all') { ?>
            <a href="../videos/">
                <i class="fa-solid fa-user" title="My Courses"></i>
            </a>
            <?php } ?>
            <?php if (hasMinAuth('admin')) { ?>
            <i id="createCourse" title="Create Course" class="fa-regular fa-square-plus"></i>
            <?php } ?>
        </nav>
        <div id="content">
            <?php if ($msg) { ?>
                <p class="error"><?= $msg ?></p>
            <?php } ?>

            <?php foreach ($offerings as $offering) { ?>
                <div class="offering" 
                    data-oid="<?= $offering['id'] ?>"
                    data-course="<?= $offering['number'] ?>"
                    data-block="<?= $offering['block'] ?>"
                    data-daysperlesson="<?= $offering['daysPerLesson'] ?>"
                    data-lessonsperpart="<?= $offering['lessonsPerPart'] ?>"
                    data-lessonparts="<?= $offering['lessonParts'] ?>"
                    data-showdates="<?= $offering['showDates'] ?>"
                    >
                    <a href="<?= $offering['number'].'/'.$offering['block'].'/' ?>">
                        <span class="number"><?= strtoupper($offering['number']) ?></span>
                        <span class="block"><?= $offering['block'] ?></span>
                        <span class="name"><?= $offering['name'] ?></span>
                    </a>
                    <span class="actions">
                        <?php if (hasMinAuth('instructor')) { ?>
                        <i title="Clone Offering" class="far fa-copy"></i>
                        <?php } ?>
                        <?php if (hasMinAuth('admin')) { ?>
                        <i title="Delete Offering" class="far fa-trash-alt"></i>
                        <?php } ?>
                    </span>
                    <span class="faculty"><?= $names[$offering['id']] ?></span>
                </div>
            <?php } ?>

        </div>
    </main>

    <?php if (hasMinAuth('instructor')) { ?>
        <dialog id="cloneDialog" class="modal">
            <i id="closeCloneDialog" class="fas fa-times-circle close"></i>
            <h3>Clone Offering</h3>

            <form id="clone_form" method="POST" action="">
                <input id="clone_offering_id" type="hidden" name="offering_id" >
                <div class="line">
                    <label>New Block:</label>
                    <input name="block" id="block" required pattern="20\d{2}-\d{2}[^\/]*" title="Block code"
                    autofocus />
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
                <div class="line">
                    <label>Days per Lesson</label>
                    <input id="daysPerLesson" type="number" name="daysPerLesson" value="" />
                </div>
                <div class="line">
                    <label>Lessons per Part</label>
                    <input id="lessonsPerPart" type="number" name="lessonsPerPart" value="" />
                </div>
                <div class="line">
                    <label>Parts (rows)</label>
                    <input id="parts" type="number" name="lessonParts" value="" />
                </div>
                <div class="indent">
                    <label class="checkbox">
                        <input id="showDates" type="checkbox" name="showDates" value="1"  />
                        Show Dates
                    </label>
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
        </dialog>
    <?php } ?>

    <?php if (hasMinAuth('admin')) { ?>
        <dialog id="createDialog" class="modal">
            <i id="closeCreateDialog" class="fas fa-times-circle close"></i>
            <h3>Create Course</h3>
            <p>Note: you generally want to clone an existing offering. Only use this create a new course with a new course number</p>
            <form method="POST" action="createCourse">
                <div class="line">
                    <label>Course Number</label>
                    <input type="text" name="number" />
                </div>
                <div class="line">
                    <label>Course Name</label>
                    <input type="text" name="name" />
                </div>
                <hr />
                <div class="line">
                    <label>First Block:</label>
                    <input name="block" />
                </div>
                <div class="line">
                    <label>Faculty</label>
                    <select name="fac_user_id">
                        <?php foreach ($faculty as $fac) { ?>
                        <option value="<?= $fac['id']?>"><?= $fac['firstname']?> <?= $fac['lastname']?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="line">
                    <label>Start Date:</label>
                    <input type="date" name="date" />
                </div>
                <div>
                    <label>Days per Lesson</label>
                    <input type="number" value="1" name="daysPerLesson" />
                    <label>Lessons per Part</label>
                    <input type="number" value="7" name="lessonsPerPart" />
                    <label>Parts</label>
                    <input type="number" value="4" name="lessonParts" />
                </div>
                <div class="submit">
                    <button>Submit</button>
                </div>
            </form>
        </dialog>

        <dialog id="deleteDialog" class="modal">
            <i id="closeDeleteDialog" class="fas fa-times-circle close"></i>
            <h3>Delete Offering</h3>
            <p>
                Please confirm that you want to delete this offering.
            </p>
            <form id="deleteFrom" method="POST" action="">
                <input type="hidden" name="offering_id" >
                <div class="submit">
                    <button type="button" id="cancel_delete" autofocus>Cancel</button>
                    <button type="submit" id="ok_delete">OK</button>
                </div>
            </form>
        </dialog>

    <?php } ?>
</body>

</html>
