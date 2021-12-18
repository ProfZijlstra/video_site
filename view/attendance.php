<!DOCTYPE html>
<html>
    <head>
        <title><?= $course ?> Videos</title>
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
            div.meeting {
                font-size; 10px;
                max-height: 15px;
                overflow: hidden;
                margin: 10px 0px;
            }
        </style>

        <script src="res/js/attendance.js"></script>
    </head>
    <body>
        <header>
			<div id="controls" data-id="<?= $_SESSION['user']['id'] ?>">
				<?php if ($_SESSION['user']['type'] === 'admin') : ?>
					<a href="/videos/user" title="Users"><i class="fas fa-users"></i></a>
				<?php endif; ?>
				<a href="logout" title="Logout"><i title="Logout" class="fas fa-power-off"></i></a>
			</div>
            <div id="course">
                <?= strtoupper($course) ?>
                <span data-id="<?= $offering['id']?>" id="offering"> <?= $offering['block'] ?> </span>
            </div>
            <h1>
                <span class="title" >
					<?= Attendance ?> 
				</span>
            </h1>
        </header>
        <main>
            <nav class="areas">
                <div title="Videos"><a href="../<?= $offering['block'] ?>/"><i class="fas fa-film"></a></i></div>
                <div title="Labs"><i class="fas fa-flask"></i></div>
                <div title="Quizzes"><i class="fas fa-school"></i></div>
                <div title="Attendance" class="active"><i class="fas fa-user-check"></i></div>
                <div title="Enrollment"><a href="enrollment"><i class="fas fa-user-friends"></i></a></div>
            </nav>

            <table id="days">
                <tr><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th><th>Sun</th></tr>
            <?php for ($w = 1; $w <= 4; $w++): ?>
                <tr>
                <?php for ($d = 1; $d <= 7; $d++): ?>
                    <?php $date = $start + ($w - 1)*60*60*24*7 + ($d - 1)*60*60*24; ?>
                    <td class="<?= $date < $now ? "done" : "" ?> <?= date("z", $date) == date("z", $now)? "curr" : ""?>"
                            id="<?= "W{$w}D{$d}" ?>"
                            data-day="<?= "W{$w}D{$d}" ?>" 
                            data-day_id="<?= $days["W{$w}D{$d}"]["id"] ?>">

                        <?php foreach ($days["W{$w}D{$d}"]["meetings"] as $meeting): ?>
                            <div class="meeting">
                                <a href="meeting/<?= $meeting["id"] ?>">
                                    <i class="far fa-check-square"></i>
                                    <?= $meeting["title"] ?>
                                </a>
                            </div>
                        <?php endforeach; ?>
                        <time>
                            <?= date("M j Y", $date);?>
                        </time>
                        <?php if ($d < 7): ?>
                            <i title="Add Meeting" class="far fa-plus-square"></i>
                        <?php else: ?>
                            <i title="End of Week Report" class="far fa-chart-bar"></i>
                        <?php endif; ?>
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
                    <input type="hidden" id="day_id" name="day_id" />
                    <input type="file" id="list_file" name="list" />
                    <div class="btn"><button>Upload Attendance</button></div>
                </form>
            </div>

        </div>

    </body>
</html>
