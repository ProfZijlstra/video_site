<!DOCTYPE html>
<html>
    <head>
        <title><?= strtoupper($course) ?> <?= $day ?> Videos</title>
        <meta charset="utf-8" />
         <link rel="stylesheet" type="text/css" href="res/css/videos.css">
         <script src="res/js/tabs.js"></script>
    </head>
    <body>
        <header>
			<div id="user" data-id="<?= $_SESSION['user']['id'] ?>">
				Hi <?= $_SESSION['user']['first'] ?>! <a href="logout">logout</a>
				<?php if ($_SESSION['user']['type'] === 'admin') : ?>
					<a href="/videos/user">users</a>
				<?php endif; ?>
			</div>
            <h1>
				<span id="day" data-id="<?= $days[$day]["id"] ?>"><?= $day ?></span> - 
				<span class="title"><?= $days[$day]["desc"] ?></span>
				<div id="course"><a href=".."><?= strtoupper($course) ?></a></div>
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
                            <a href="W<?=$w?>D<?=$d?>">&nbsp;</a></td>
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
                <div>
                </div>
                <div><?= $info["parts"][1] ?></div>
            </div>
<?php
    if ($first) {
        $first = false;
    }
} // end foreach loop
?>
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

