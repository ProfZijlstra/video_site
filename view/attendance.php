<!DOCTYPE html>
<html>

<head>
    <title><?= $block ?> Attendance</title>
    <meta charset="utf-8" />
    <meta name=viewport content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="res/css/font-awesome-all.min.css">
    <link rel="stylesheet" href="res/css/common.css">
    <link rel="stylesheet" href="res/css/offering.css">
    <link rel="stylesheet" href="res/css/adm.css">
    <link rel="stylesheet" href="res/css/attendance.css">
    <script src="res/js/attendance.js"></script>
</head>

<body>
    <?php include("header.php"); ?>
    <main>
        <nav class="areas">
            <div title="Videos"><a href="../<?= $block ?>/"><i class="fas fa-film"></a></i></div>
            <div title="Labs"><i class="fas fa-flask"></i></div>
            <div title="Quizzes"><i class="fas fa-vial"></i></div>
            <div title="Attendance" class="active"><i class="fas fa-user-check"></i></div>
            <div title="Enrolled"><a href="enrolled"><i class="fas fa-user-friends"></i></a></div>
        </nav>


        <div id="days">
            <div class="header">Mon</div>
            <div class="header">Tue</div>
            <div class="header">Wed</div>
            <div class="header">Thu</div>
            <div class="header">Fri</div>
            <div class="header">Sat</div>
            <div class="header">Sun</div>

            <?php for ($w = 1; $w <= 4; $w++): ?>
                <?php for ($d = 1; $d <= 7; $d++): ?>
                    <?php $date = $start + ($w - 1)*60*60*24*7 + ($d - 1)*60*60*24; ?>

                    <div class="data <?= $d == 1 ? "mon " : "" ?><?= $date < $now ? "done" : "" ?> <?= date("z", $date) == date("z", $now)? "curr" : ""?>"
                        id="<?= "W{$w}D{$d}" ?>"
                        data-day="<?= "W{$w}D{$d}" ?>" 
                        data-day_id="<?= $days["W{$w}D{$d}"]["id"] ?>"
                        data-date="<?= date("Y-m-d", $date) ?>">

                        <?php if ($w == 4 && $d == 6) : ?>
                            <a href="professionalism">
                                <i title="Professionalism Report" class="fab fa-black-tie"></i>
                            </a>
                        <?php elseif ($d == 7): ?>
                            <a href="physical/W<?= $w ?>">
                                <i title="Physical Classroom Attendance Report" class="fas fa-chalkboard-teacher"></i>
                            </a>
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