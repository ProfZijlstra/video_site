<!DOCTYPE html>
<html>

<head>
    <title><?= $course ?> Videos</title>
    <meta charset="utf-8" />
    <meta name=viewport content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="res/css/lib/font-awesome-all.min.css">
    <link rel="stylesheet" href="res/css/common-1.3.css">
    <link rel="stylesheet" href="res/css/offering-1.3.css">
    <link rel="stylesheet" href="res/css/adm-1.0.css">
    <script src="res/js/offering.js"></script>
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
            <a href="chart">
                <i title="View Statistics" class="fa-solid fa-chart-column"></i>
            </a>
        <?php if (hasMinAuth('instructor')) { ?>
            <?php if (! $isRemembered) { ?>
                <i title="Edit Calendar" id="edit" class="far fa-edit"></i>
            <?php } else { ?>
                <a href="reAuth">
                    <i title="Edit Calendar" class="far fa-edit"></i>
                </a>
            <?php } ?>
            <a href="settings">
                <i title="Offering Settings" class="fa-solid fa-gear"></i>
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
                    <?php $date = $start + ($w - 1) * 60 * 60 * 24 * $offering['daysPerLesson'] * $offering['lessonsPerPart'] + ($d - 1) * 60 * 60 * 24 * $offering['daysPerLesson']; ?>

                    <div class="data <?= $w == 1 ? 'w1' : '' ?> <?= $d == 1 ? 'd1 ' : '' ?><?= $offering['showDates'] && $date < $now ? 'done' : '' ?> <?= date('z', $date) == date('z', $now) ? 'curr' : '' ?>" id="<?= "W{$w}D{$d}" ?>" data-day="<?= "W{$w}D{$d}" ?>"  data-text="<?= $days["W{$w}D{$d}"]['desc'] ?>">
                        <div class="info"></div>
                        <a href="W<?= $w ?>D<?= $d ?>/">
                            <span class="text"><?= $days["W{$w}D{$d}"]['desc'] ?></span>
                        </a>
                        <i class="far fa-edit hide" 
                            title="Edit Day Title"
                            data-day_id="<?= $days["W{$w}D{$d}"]['id'] ?>"></i>
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
    <?php } ?>
</body>

</html>
