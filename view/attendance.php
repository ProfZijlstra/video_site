<!DOCTYPE html>
<html>

<head>
    <title><?= $offering['block'] ?> Attendance</title>
    <meta charset="utf-8" />
    <meta name=viewport content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="res/css/font-awesome-all.min.css">
    <link rel="stylesheet" href="res/css/offering.css">
    <link rel="stylesheet" href="res/css/adm.css">
    <style>
        table#days td {
            vertical-align: top;
            cursor: default;
            text-align: left;
        }

        div.session {
            margin-top: 5px;
            border-bottom: 1px solid black;
        }
        div.session.AM {
            padding-top: 5px;
            border-top: 1px solid black;
        }
        div.session .fa-plus-square {
            cursor: pointer;
        }
        div.meeting {
            max-height: 15px;
            margin-left: 10px;
            margin-bottom: 7px;
        }

        #days .fa-chalkboard-teacher, 
        #days .fa-black-tie {
            bottom: 75px;
            right: 75px;
            font-size: 30px;
            cursor: pointer;
        }
    </style>

    <script src="res/js/attendance.js"></script>
</head>

<body>
    <header>
        <div id="controls" data-id="<?= $_SESSION['user']['id'] ?>">
            <a href="/videos/user" title="Users"><i class="fas fa-users"></i></a>
            <a href="logout" title="Logout"><i title="Logout" class="fas fa-power-off"></i></a>
        </div>
        <div id="course">
            <?= strtoupper($course) ?>
            <span data-id="<?= $offering['id'] ?>" id="offering"> <?= $offering['block'] ?> </span>
        </div>
        <h1>
            <span class="title">
                Attendance
            </span>
        </h1>
    </header>
    <main>
        <nav class="areas">
            <div title="Videos"><a href="../<?= $offering['block'] ?>/"><i class="fas fa-film"></a></i></div>
            <div title="Labs"><i class="fas fa-flask"></i></div>
            <div title="Quizzes"><i class="fas fa-school"></i></div>
            <div title="Attendance" class="active"><i class="fas fa-user-check"></i></div>
            <div title="Enrolled"><a href="enrolled"><i class="fas fa-user-friends"></i></a></div>
        </nav>

        <table id="days">
            <tr>
                <th>Mon</th>
                <th>Tue</th>
                <th>Wed</th>
                <th>Thu</th>
                <th>Fri</th>
                <th>Sat</th>
                <th>Sun</th>
            </tr>
            <?php for ($w = 1; $w <= 4; $w++) : ?>
                <tr>
                    <?php for ($d = 1; $d <= 7; $d++) : ?>
                        <?php $date = $start + ($w - 1) * 60 * 60 * 24 * 7 + ($d - 1) * 60 * 60 * 24; ?>
                        <td class="<?= $date < $now ? "done" : "" ?> <?= date("z", $date) == date("z", $now) ? "curr" : "" ?>" id="<?= "W{$w}D{$d}" ?>" data-day="<?= "W{$w}D{$d}" ?>" data-day_id="<?= $days["W{$w}D{$d}"]["id"] ?>">
                            <?php if ($w == 4 && $d == 6) : ?>
                                <i title="Professionalism Report" class="fab fa-black-tie"></i>
                            <?php elseif ($d == 7): ?>
                                <i title="Physical Classroom Attendance Report" class="fas fa-chalkboard-teacher"></i>
                            <?php else : ?>
                                <?php foreach (["AM", "PM"] as $stype): ?>
                                    <div class="session <?= $stype ?>" data-session_id="<?= $days["W{$w}D{$d}"][$stype]["id"] ?>">
                                        <?= $stype ?>
                                        <i title="Add Meeting" class="far fa-plus-square"></i>

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
                        </td>
                    <?php endfor ?>
                </tr>
            <?php endfor ?>
        </table>
    </main>

    <div id="overlay">
        <i id="close-overlay" class="fas fa-times-circle"></i>
        <div class="modal">
            <h3>Upload Attendance</h3>
            <p>Expected format is a Teams report with addditional column for meeting attendance start and stop time</p>
            <form action="" method="post" enctype="multipart/form-data" id="upload_form">
                <input type="hidden" id="session_id" name="session_id" />
                <input type="file" id="list_file" name="list" />
                <div class="btn"><button>Upload Attendance</button></div>
            </form>
        </div>

    </div>

</body>

</html>