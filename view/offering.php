<!DOCTYPE html>
<html>
    <head>
        <title><?= $course ?> Videos</title>
        <meta charset="utf-8" />
        <meta name=viewport content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="res/css/font-awesome-all.min.css">
		<link rel="stylesheet" href="res/css/offering.css">
        <script src="res/js/offering.js"></script>
        <?php if ($_SESSION['user']['type'] === 'admin') : ?>
            <link rel="stylesheet" href="res/css/adm.css">
            <script src="https://unpkg.com/react@17/umd/react.production.min.js" crossorigin></script>
            <script src="https://unpkg.com/react-dom@17/umd/react-dom.production.min.js" crossorigin></script>
            <script src="res/js/info.js"></script>
            <script src="res/js/adm_offering.js"></script>
        <?php endif; ?>
    </head>
    <body>
        <header>
			<div id="controls" data-id="<?= $_SESSION['user']['id'] ?>">
				<?php if ($_SESSION['user']['type'] === 'admin') : ?>
					<i class="far fa-copy" style="color: lightgray"></i>
					<i id="info-btn" class="fas fa-info-circle"></i>
					<a href="/videos/user"><i class="fas fa-users"></i></a>
				<?php endif; ?>
				<a href="logout"><i class="fas fa-power-off"></i></a>
			</div>
            <div id="course">
                <a href=".." id="course_num"><?= strtoupper($course) ?></a>
                <span data-id="<?= $offering['id']?>" id="offering"> <?= $offering['block'] ?> </span>
            </div>
            <h1>
                <span class="title" >
					<?= $title ?> 
				</span>
            </h1>
        </header>
        <main>
            <table id="days">
                <tr><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th><th>Sun</th></tr>
            <?php for ($w = 1; $w <= 4; $w++): ?>
                <tr>
                <?php for ($d = 1; $d <= 7; $d++): ?>
                    <?php $date = $start + ($w - 1)*60*60*24*7 + ($d - 1)*60*60*24; ?>
                    <td class="<?= $date < $now ? "done" : "" ?> <?= date("z", $date) == date("z", $now)? "curr" : ""?>"
                            id="<?= "W{$w}D{$d}" ?>"
                            data-day="<?= "W{$w}D{$d}" ?>" 
                            data-day_id="<?= $days["W{$w}D{$d}"]["id"] ?>"
                            data-text="<?= $days["W{$w}D{$d}"]["desc"] ?>">
                        <div class="info"></div>
                        <a href="W<?= $w ?>D<?= $d ?>/">
                            <span class="text"><?= $days["W{$w}D{$d}"]["desc"] ?></span>
                        </a>
                        <time><?= date("M j Y", $date);?></time>
                    </td>
                <?php endfor ?>
                </tr>
            <?php endfor ?>
            </table>
			<div id="total"><div class="info"></div></div>
        </main>
        <?php if ($_SESSION['user']['type'] === 'admin') : ?>
            <div id="overlay">
                <i id="close-overlay" class="fas fa-times-circle"></i>
                <div id="tables"></div>
            </div>
        <?php endif; ?>

    </body>
</html>
