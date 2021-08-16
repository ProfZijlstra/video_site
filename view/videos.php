<!DOCTYPE html>
<html>
    <head>
        <title><?= strtoupper($course) ?> <?= $day ?> Videos</title>
        <meta charset="utf-8" />
		<link rel="stylesheet" href="res/css/font-awesome-all.min.css">
        <link rel="stylesheet" type="text/css" href="res/css/videos.css">
        <script src="https://unpkg.com/react@17/umd/react.production.min.js" crossorigin></script>
        <script src="https://unpkg.com/react-dom@17/umd/react-dom.production.min.js" crossorigin></script>
        <script src="res/js/info.js"></script>
        <script src="res/js/videos.js"></script>
    </head>
    <body>
        <header>
			<div id="user" data-id="<?= $_SESSION['user']['id'] ?>">
				<?php if ($_SESSION['user']['type'] === 'admin') : ?>
					<i id="info-btn" class="fas fa-info-circle"></i>
					<a href="/videos/user"><i class="fas fa-users"></i></a>
				<?php endif; ?>
				<a href="logout"><i class="fas fa-power-off"></i></a>
			</div>
            <h1>
				<span id="day" data-id="<?= $days[$day]["id"] ?>"><?= $day ?></span> - 
				<span class="title"><?= $days[$day]["desc"] ?></span>
				<div id="course"><a href=".."><?= strtoupper($course) ?> <?= $block ?></a></div>
            </h1>
        </header>
        <nav id ="videos">
            <nav>
                <table id="days">
                    <tr><th>M</th><th>T</th><th>W</th><th>T</th><th>F</th><th>S</th><th>S</th></tr>
<?php 
    for ($w = 1; $w <= 4; $w++) {
?>
                    <tr>
<?php 
        for ($d = 1; $d <= 7; $d++) {
?>
                        <td class="<?= $w < $curr_w || $w == $curr_w && $d <= $curr_d ? "done": ""?>
                            <?= $w == $page_w && $d == $page_d ? "curr": ""?>">
                            <a href="../W<?=$w?>D<?=$d?>/">&nbsp;</a></td>
<?php
        } // end td for loop
?>
                    </tr>
<?php
    } // end tr for loop
?>
                </table>
            </nav>
<?php
$first = true;
foreach($files as $file => $info) {
?>
            <div class='video_link <?= $first ? "selected" : ""?>'
                data-show="<?= $info["parts"][0]?>_<?= $info["parts"][1] ?>">
                <div><?= $info["parts"][1] ?></div>
				<div class="info"></div>
            </div>
<?php
    if ($first) {
        $first = false;
    }
} // end foreach loop
?>
			<div id="total"></div>
        </nav>
        <main>
			<div id="playSpeed">
				<span id="slower">-</span>
				<span id="curSpeed">1.0</span>
				<span id="faster">+</span>
			</div>
<?php 
$first = true;
$passed = 0;
foreach($files as $file => $info) {
    $passedPercent = ($passed / $totalDuration)*100;
    $currentPrecent = $passedPercent + (($info["duration"] / $totalDuration)*100);
?>
    <article id="<?= $info["parts"][0]?>_<?= $info["parts"][1] ?>" 
            class='<?= $first ? "selected" : "" ?>'>
        <h2><?= $info["parts"][1]?></h2>
        <video controls>
            <source src="<?= "res/vid/${course}/${block}/${day}/${file}" ?>" type="video/mp4"/>
        </video>
        <div class="progress">
            <div class="current" style="width: <?= number_format($currentPrecent, 2) ?>%;"></div>
            <div class="passed"  style="width: <?= number_format($passedPercent, 2) ?>%;"></div>
            <div class="time"><?= $totalTime ?></div>
        </div>
<?php
    if ($first) {
        $first = false;
    }
    $passed += $info["duration"];
?>
    </article>
<?php
} // end foreach loop 
?>
        </main>
    </body>
</html>

