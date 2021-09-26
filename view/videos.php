<!DOCTYPE html>
<html>
    <head>
        <title><?= strtoupper($course) ?> <?= $day ?> Videos</title>
        <meta charset="utf-8" />
		<link rel="stylesheet" href="res/css/font-awesome-all.min.css" />
        <link rel="stylesheet" type="text/css" href="res/css/videos.css" />
        <link rel="stylesheet" href="res/css/prism.css" />
        <script src="https://unpkg.com/react@17/umd/react.production.min.js" crossorigin></script>
        <script src="https://unpkg.com/react-dom@17/umd/react-dom.production.min.js" crossorigin></script>
        <script src="res/js/info.js"></script>
        <script src="res/js/videos.js"></script>
        <script src="res/js/prism.js"></script>
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
        <nav id="videos">
            <nav>
                <table id="days">
                    <tr><th>M</th><th>T</th><th>W</th><th>T</th><th>F</th><th>S</th><th>S</th></tr>
<?php 
    for ($w = 1; $w <= 4; $w++) :
?>
                    <tr>
<?php 
        for ($d = 1; $d <= 7; $d++) :
?>
                        <td class="<?= $w < $curr_w || $w == $curr_w && $d <= $curr_d ? "done": ""?>
                            <?= $w == $page_w && $d == $page_d ? "curr": ""?>">
                            <a href="../W<?=$w?>D<?=$d?>/">&nbsp;</a></td>
<?php
        endfor; // td loop
?>
                    </tr>
<?php
    endfor; // tr loop
?>
                </table>
            </nav>
            <div id="tabs">
<?php foreach($files as $file => $info) : ?>
                <div class='video_link <?= $info["parts"][0] == $video ? "selected" : ""?>'
                    data-show="<?= $info["parts"][0]?>_<?= $info["parts"][1] ?>"
                    id="<?= $info["parts"][0] ?>">
                    <div><a href="<?= $info["parts"][0]?>"><?= $info["parts"][1] ?></a></div>
                    <div class="info"></div>
                </div>
<?php endforeach; ?>
            </div>
            <div id="total"></div>
        </nav>
        <main>
			<div id="playSpeed">
				<span id="slower">-</span>
				<span id="curSpeed">1.0</span>
				<span id="faster">+</span>
			</div>
<?php 
$passed = 0;
foreach($files as $file => $info) :
    $passedPercent = ($passed / $totalDuration)*100;
    $currentPrecent = $passedPercent + (($info["duration"] / $totalDuration)*100);
    if ($info["parts"][0] == $video) :
?>
    <article id="<?= $info["parts"][0]?>_<?= $info["parts"][1] ?>" 
            class="selected">
        <h2><?= $info["parts"][1]?></h2>
        <a id="pdf" data-file="<?= $info["parts"][0]?>_<?= $info["parts"][1] ?>.pdf"
            href='<?= "res/{$course}/{$block}/{$day}/pdf/" .$info["parts"][0] . "_" . $info["parts"][1] . ".pdf" ?>'>
            <i class="far fa-file-pdf"></i>
        </a>
        <video controls>
            <source src="<?= "res/${course}/${block}/${day}/vid/${file}" ?>" type="video/mp4"/>
        </video>
        <div class="progress">
            <div class="current" style="width: <?= number_format($currentPrecent, 2) ?>%;"></div>
            <div class="passed"  style="width: <?= number_format($passedPercent, 2) ?>%;"></div>
            <div class="time"><?= $totalTime ?></div>
        </div>
        <div id="questions">
            <h2>Questions & Comments</h2>
            <?php foreach ($questions as $question) : ?>
                <div class="asked">
                    <?= $question["firstname"]?> <?= $question["lastname"]?>  
                    <span class="date">created: <?= $question["created"]?></span>
                    <?php if ($question["edited"]) : ?>
                        <span class="date">edited: <?= $question["edited"]?></span>
                    <?php endif; ?>
                    <?php if ($_SESSION['user']['type'] === 'admin' || $_SESSION['user']['id'] == $question["user_id"]) : ?>
                        <form method="post" action="delQuestion">
                            <input type="hidden" name="id" value="<?= $question['id']?>" />
                            <input type="hidden" name="tab" value="<?= $video ?>" />
                            <i class="far fa-trash-alt" data-id=""></i>
                        </form>
                        <i class="far fa-edit" data-id="<?= $question['id']?>"></i>
                    <?php endif; ?>
                    <div class="vote" data-qid="<?= $question['id'] ?>"
                        <?php if($question["vote_id"]) : ?>  
                            data-vid="<?= $question["vote_id"] ?>"
                        <?php endif; ?>
                        <?php if($question["vote"]) : ?>  
                            data-type="<?= $question["vote"] > 0 ? "up" : "down" ?>"
                        <?php endif; ?>
                    >
                        <i class="fas fa-angle-up <?= $question["vote"] > 0 ? 'selected' : "" ?>"></i> 
                        <i class="fas fa-angle-down <?= $question["vote"] < 0 ? 'selected' : "" ?>"></i>
                    </div>
                </div>
                <div class="question" id="q<?= $question['id'] ?>"><?= $parsedown->text($question["question"]) ?></div>
            <?php endforeach; // question ?>
            <?php if (count($questions) == 0) : ?>
                <div>No questions or comments yet</div>
            <?php endif; ?>
            <h3>Add a question or comment:</h3>
            <form method="post" action="question" id="questionForm">
                <input type="hidden" name="video" value="<?= $info["parts"][2] ?>" />
                <input type="hidden" name="tab" id="tab" value="<?= $info["parts"][0] ?>" />
                <textarea name="question" class="questionText" 
                    placeholder="Use **markdown** syntax in your text like:&#10;&#10;```javascript&#10;const code = &quot;highlighted&quot;&semi;&#10;```"></textarea>
                <button class="textAction">Add</button>
            </form>
        </div> 
    </article>
<?php
        break; // no need to continue after the requested video
    endif; 
    $passed += $info["duration"];
endforeach;
?>
        </main>
    </body>
</html>

