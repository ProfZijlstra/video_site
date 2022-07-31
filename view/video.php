<!DOCTYPE html>
<html>
    <head>
        <title><?= strtoupper($course) ?> <?= $day ?> Videos</title>
        <meta charset="utf-8" />
        <meta name=viewport content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="res/css/font-awesome-all.min.css" />
        <link rel="stylesheet" href="res/css/common.css">
        <link rel="stylesheet" type="text/css" href="res/css/video.css" />
        <link rel="stylesheet" href="res/css/prism.css" />
        <script src="res/js/video.js"></script>
        <script src="res/js/prism.js"></script>
        <?php if ($_user_type === 'admin') : ?>
            <link rel="stylesheet" href="res/css/adm.css">
            <script src="https://unpkg.com/react@17/umd/react.production.min.js" crossorigin></script>
            <script src="https://unpkg.com/react-dom@17/umd/react-dom.production.min.js" crossorigin></script>
            <script src="res/js/info.js"></script>
            <script src="res/js/adm_video.js"></script>
        <?php endif; ?>
    </head>
    <body>
        <?php include("header.php"); ?>
        <i id="bars" class="fa-solid fa-bars"></i>
        <div id="container" data-oid="<?= $offering_id ?>">
        <nav id="videos" class="<?= $theater ?>">
            <nav>
                <?php if ($_user_type === 'admin') : ?>
                    <i title="View Info" id="info-btn" class="fas fa-info-circle"></i>
                <?php endif; ?>
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
            <div id="total" 
                data-day="<?= $day ?>" 
                data-day_id="<?= $days[$day]["id"] ?>" 
                data-text="<?= $days[$day]["desc"] ?>"></div>
        </nav>
        <main id="day" data-id="<?= $days[$day]["id"] ?>">
			<div id="playSpeed">
				<span id="slower">-</span>
				<span id="curSpeed"><?= number_format($speed, 1) ?></span>
				<span id="faster">+</span>
			</div>
<?php 
$passed = 0;
foreach($files as $info) :
    $passedPercent = ($passed / $totalDuration)*100;
    $currentPrecent = $passedPercent + (($info["duration"] / $totalDuration)*100);
    if ($info["parts"][0] == $video) :
?>
    <article id="<?= $info["parts"][0]?>_<?= $info["parts"][1] ?>" 
            class="selected">
        <h2><?= $info["parts"][1]?></h2>
        <a id="pdf" target="_blank" data-file="<?= $info["parts"][0]?>_<?= $info["parts"][1] ?>"
            href='<?= "res/{$course}/{$block}/{$day}/pdf/" .$info["parts"][0] . "_" . $info["parts"][1] . ".pdf" ?>'>
            <i class="far fa-file-pdf"></i>
        </a>
        <video controls>
            <source src="<?= "res/${course}/${block}/${day}/vid/${info["file"]}" ?>" type="video/mp4"/>
        </video>
        <div class="progress">
            <div class="current" style="width: <?= number_format($currentPrecent, 2) ?>%;"></div>
            <div class="passed"  style="width: <?= number_format($passedPercent, 2) ?>%;"></div>
            <div class="time"><?= $totalTime ?></div>
            <div id="autoplay">autoplay <i id="auto_toggle" class="fas fa-toggle-<?= $autoplay ?>"></i></div>
            <div id="shortcuts" title="Keyboard Shortcuts"><i class="fa-solid fa-keyboard"></i></div>
        </div>

        <div id="keyboard" class="hidden">
            <section>
                <h5>Playback</h5>
                <div>
                    <span class="key">Space</span>
                    <span class="action">Play / Pause</span>
                </div>
                <div>
                    <span class="key"><i class="fa-solid fa-arrow-left"></i></span>
                    <span class="action">Back 10 secconds</span>
                </div>
                <div>
                    <span class="key"><i class="fa-solid fa-arrow-right"></i></span>
                    <span class="action">Forward 10 seconds</span>
                </div>
            </section>
            <section>
                <h5>Alternate Playback</h5>
                <div>
                    <span class="key">K</span>
                    <span class="action">Play / Pause</span>
                </div>
                <div>
                    <span class="key">J</i></span>
                    <span class="action">Back 5 Secconds</span>
                </div>
                <div>
                    <span class="key">L</i></span>
                    <span class="action">Forward 5 Seconds</span>
                </div>
            </section>
            <section>
                <h5>Content Control</h5>
                <div>
                    <span class="key">N</span>
                    <span class="action">Next Video</span>
                </div>
                <div>
                    <span class="key">P</i></span>
                    <span class="action">Previous Video</span>
                </div>
                <div>
                    <span class="key">D</i></span>
                    <span class="action">Download PDF</span>
                </div>
            </section>
            <section>
                <h5>Speed</h5>
                <div>
                    <span class="key">[</span>
                    <span class="action">Decrease Speed</span>
                </div>
                <div>
                    <span class="key">]</span>
                    <span class="action">Increase Speed</span>
                </div>
                <div>
                    <span class="key">0</span>
                    <span class="action">Normal Speed</span>
                </div>
            </section>
            <section>
                <h5>Modes</h5>
                <div>
                    <span class="key">A</span>
                    <span class="action">Toggle Auto Play</span>
                </div>
                <div>
                    <span class="key">T</span>
                    <span class="action">Toggle Theater Mode</span>
                </div>
                <div>
                    <span class="key">F</span>
                    <span class="action">Toggle Full Screen</span>
                </div>
            </section>
        </div>

        <div id="comments">
            <h2>Questions & Comments</h2>
            <?php foreach ($comments as $comment) : ?>
                <div class="author">
                    <?= $comment["knownAs"]?> <?= $comment["lastname"]?>  
                    <span class="date">created: <?= $comment["created"]?></span>
                    <?php if ($comment["edited"]) : ?>
                        <span class="date">edited: <?= $comment["edited"]?></span>
                    <?php endif; ?>
                    <?php if ($_user_type === 'admin' || $_user_id == $comment["user_id"]) : ?>
                        <form method="post" action="delComment">
                            <input type="hidden" name="id" value="<?= $comment['id']?>" />
                            <input type="hidden" name="tab" value="<?= $video ?>" />
                            <i class="far fa-trash-alt" data-id=""></i>
                        </form>
                        <i class="far fa-edit" data-id="<?= $comment['id']?>"></i>
                    <?php endif; ?>
                    <div class="vote" data-id="<?= $comment['id'] ?>"
                        <?php if($comment["vote_id"]) : ?>  
                            data-vid="<?= $comment["vote_id"] ?>"
                        <?php endif; ?>
                        <?php if($comment["vote"]) : ?>  
                            data-type="<?= $comment["vote"] > 0 ? "up" : "down" ?>"
                        <?php endif; ?>
                    >
                        <i class="fas fa-angle-up <?= $comment["vote"] > 0 ? 'selected' : "" ?>"></i> 
                        <i class="fas fa-angle-down <?= $comment["vote"] < 0 ? 'selected' : "" ?>"></i>
                    </div>
                </div>
                <div class="comment mdBox" id="q<?= $comment['id'] ?>">
                    <div class="qText"><?= $parsedown->text($comment["text"]) ?></div>
                    <?php foreach ($replies[$comment['id']] as $reply) : ?>
                        <div class="author">
                            <?= $reply["knownAs"]?> <?= $reply["lastname"]?>  
                            <span class="date">created: <?= $reply["created"]?></span>
                            <?php if ($reply["edited"]) : ?>
                                <span class="date">edited: <?= $reply["edited"]?></span>
                            <?php endif; ?>
                            <?php if ($_user_type === 'admin' || $_user_id == $reply["user_id"]) : ?>
                                <form method="post" action="delReply">
                                    <input type="hidden" name="id" value="<?= $reply['id']?>" />
                                    <input type="hidden" name="tab" value="<?= $video ?>" />
                                    <i class="far fa-trash-alt" data-id=""></i>
                                </form>
                                <i class="far fa-edit" data-id="<?= $reply['id']?>"></i>
                            <?php endif; ?>
                            <div class="vote" data-id="<?= $reply['id'] ?>"
                                <?php if($reply["vote_id"]) : ?>  
                                    data-vid="<?= $reply["vote_id"] ?>"
                                <?php endif; ?>
                                <?php if($reply["vote"]) : ?>  
                                    data-type="<?= $reply["vote"] > 0 ? "up" : "down" ?>"
                                <?php endif; ?>
                            >
                                <i class="fas fa-angle-up <?= $reply["vote"] > 0 ? 'selected' : "" ?>"></i> 
                                <i class="fas fa-angle-down <?= $reply["vote"] < 0 ? 'selected' : "" ?>"></i>
                            </div>
                        </div>
                        <div class="reply mdBox" id="r<?= $reply['id'] ?>"><?= $parsedown->text($reply["text"]) ?></div>
                    <?php endforeach; ?>
                    <div class="addReply">add reply</div>
                </div>
            <?php endforeach; // comment ?>
            <?php if (count($comments) == 0) : ?>
                <div>No comments or comments yet</div>
            <?php endif; ?>
            <h3>Add a comment or comment:</h3>
            <form method="post" action="comment" id="commentForm">
                <input type="hidden" name="video" value="<?= $info["parts"][2] ?>" />
                <input type="hidden" name="tab" id="tab" value="<?= $info["parts"][0] ?>" />
                <textarea name="comment" class="commentText" 
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
        </div> <!-- close container-->

        <?php if ($_user_type === 'admin') : ?>
            <div id="overlay">
                <i id="close-overlay" class="fas fa-times-circle"></i>
                <div id="content"></div>
            </div>
        <?php endif; ?>

    </body>
</html>

