<!DOCTYPE html>
<html>

<head>
    <title><?= strtoupper($course) ?> <?= $day ?> Videos</title>
    <meta charset="utf-8" />
    <meta name=viewport content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="res/css/lib/font-awesome-all.min.css" />
    <link rel="stylesheet" href="res/css/common-1.2.css">
    <link rel="stylesheet" type="text/css" href="res/css/video-1.5.css" />
    <link rel="stylesheet" href="res/css/lib/prism.css" />
    <script src="res/js/markdown-1.3.js"></script>
    <script src="res/js/video-1.9.js"></script>
    <script src="res/js/lib/prism.js"></script>
    <?php if (hasMinAuth('instructor')) : ?>
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
                <?php if (hasMinAuth('instructor')) : ?>
                    <?php if (!$isRemembered) : ?>
                        <i title="View Info" id="info-btn" class="fas fa-info-circle"></i>
                        <i title="Configure Videos" id="config-btn" class="fa-solid fa-gear"></i>
                    <?php else : ?>
                        <a href="reAuth">
                            <i title="View Info" id="info-btn" class="fas fa-info-circle"></i>
                        </a>
                        <a href="reAuth">
                            <i title="Configure Videos" id="config-btn" class="fa-solid fa-gear"></i>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
                <table id="days">
                    <!-- <tr><th>M</th><th>T</th><th>W</th><th>T</th><th>F</th><th>S</th><th>S</th></tr> -->
                    <?php for ($w = 1; $w <= $offering['lessonParts']; $w++) : ?>
                        <tr>
                            <?php for ($d = 1; $d <= $offering['lessonsPerPart']; $d++) : ?>
                                <td class="<?= $offering['showDates'] && $w < $curr_w || ($w == $curr_w && $d <= $curr_d) ? "done" : "" ?>
                            <?= $offering['showDates'] && $w == $page_w && $d == $page_d ? "curr" : "" ?>">
                                    <a href="../W<?= $w ?>D<?= $d ?>/">&nbsp;</a>
                                </td>
                            <?php endfor; // td loop 
                            ?>
                        </tr>
                    <?php endfor; // tr loop 
                    ?>
                </table>
            </nav>
            <div id="tabs">
                <?php
                $video_count = 0;
                ?>
                <?php foreach ($files as $file => $info) : ?>
                    <div class='video_link <?= $info["parts"][0] == $video ? "selected" : "" ?>' data-show="<?= $info["parts"][0] ?>_<?= $info["parts"][1] ?>" id="<?= $info["parts"][0] ?>">
                        <div>
                            <a href="<?= $info["parts"][0] ?>"><?= $info["parts"][1] ?></a>
                            <?php if (hasMinAuth('instructor')) : ?>
                                <span class="config">
                                    <?php
                                    $decrease = true;
                                    $increase = true;
                                    if ($video_count == 0) {
                                        $decrease = false;
                                    }
                                    if ($video_count == (count($files) - 1)) {
                                        $increase = false;
                                    }
                                    ?>
                                    <i title="Move video up" class="fa-solid fa-arrow-up <?= !$decrease ? "disabled" : "" ?>" <?php if ($increase) : ?> data-file="<?= $info["file"] ?>" data-prev_file="<?= $files[$video_count - 1]["file"] ?>" <?php endif; ?>>
                                    </i>
                                    <i title="Move video down" class="fa-solid fa-arrow-down <?= !$increase ? "disabled" : "" ?>" <?php if ($decrease) : ?> data-file="<?= $info["file"] ?>" data-next_file="<?= $files[$video_count + 1]["file"] ?>" <?php endif; ?>>
                                    </i>
                                    <i title="Edit title" class="fa-regular fa-pen-to-square" data-title="<?= $info["parts"][1] ?>" data-file="<?= $info["file"] ?>"></i>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="info"></div>
                    </div>
                    <?php $video_count++ ?>
                <?php endforeach; ?>
            </div>
            <div id="total" data-day="<?= $day ?>" data-day_id="<?= $days[$day]["id"] ?>" data-text="<?= $days[$day]["desc"] ?>"></div>
            <div id="back">
                <a href="../">
                    <i class="fa-solid fa-arrow-left"></i>
                    Back to Overview
                </a>
                <?php if (hasMinAuth('instructor')) : ?>
                    <span class="config">
                        <i title="Add Video" class="fa-solid fa-plus" id="add_video"></i>
                    </span>
                <?php endif; ?>
            </div>
        </nav>
        <main id="day" data-id="<?= $days[$day]["id"] ?>">
            <div id="playSpeed">
                <span id="slower">-</span>
                <span id="curSpeed"><?= number_format($speed, 1) ?></span>
                <span id="faster">+</span>
            </div>
            <?php
            $passed = 0;
            foreach ($files as $info) :
                $passedPercent = ($passed / $totalDuration) * 100;
                $currentPrecent = $passedPercent + (($info["duration"] / $totalDuration) * 100);
                if ($info["parts"][0] == $video) :
            ?>
                    <article id="<?= $info["parts"][0] ?>_<?= $info["parts"][1] ?>" class="selected">
                        <h2><?= $info["parts"][1] ?></h2>
                        <?php if ($pdf) : ?>
                            <a id="pdf" target="_blank" data-file="<?= $info["parts"][0] ?>_<?= $info["parts"][1] ?>" href='<?= $pdf_file ?>'>
                                <i class="far fa-file-pdf"></i>
                            </a>
                        <?php endif; ?>
                        <video controls controlslist="nodownload">
                            <source src="<?= "res/{$course}/{$block}/{$day}/vid/{$info["file"]}" ?>" type="video/mp4" />
                        </video>
                        <div class="progress">
                            <div class="current" style="width: <?= number_format($currentPrecent, 2) ?>%;"></div>
                            <div class="passed" style="width: <?= number_format($passedPercent, 2) ?>%;"></div>
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
                                    <?= $comment["knownAs"] ?> <?= $comment["lastname"] ?>
                                    <span class="date">created: <?= $comment["created"] ?></span>
                                    <?php if ($comment["edited"]) : ?>
                                        <span class="date">edited: <?= $comment["edited"] ?></span>
                                    <?php endif; ?>
                                    <?php if (hasMinAuth('instructor') || $_user_id == $comment["user_id"]) : ?>
                                        <form method="post" action="delComment">
                                            <input type="hidden" name="id" value="<?= $comment['id'] ?>" />
                                            <input type="hidden" name="tab" value="<?= $video ?>" />
                                            <i class="far fa-trash-alt" data-id=""></i>
                                        </form>
                                        <i class="far fa-edit" data-id="<?= $comment['id'] ?>"></i>
                                    <?php endif; ?>
                                    <div class="vote" data-id="<?= $comment['id'] ?>" <?php if ($comment["vote_id"]) : ?> data-vid="<?= $comment["vote_id"] ?>" <?php endif; ?> <?php if ($comment["vote"]) : ?> data-type="<?= $comment["vote"] > 0 ? "up" : "down" ?>" <?php endif; ?>>
                                        <i class="fas fa-angle-up <?= $comment["vote"] > 0 ? 'selected' : "" ?>" title="Vote Up"></i>
                                        <i class="fas fa-angle-down <?= $comment["vote"] < 0 ? 'selected' : "" ?>" title="Vote Down"></i>
                                    </div>
                                </div>
                                <div class="comment mdBox" id="q<?= $comment['id'] ?>">
                                    <div class="qText"><?= $parsedown->text($comment["text"]) ?></div>
                                    <?php foreach ($replies[$comment['id']] as $reply) : ?>
                                        <div class="author">
                                            <?= $reply["knownAs"] ?> <?= $reply["lastname"] ?>
                                            <span class="date">created: <?= $reply["created"] ?></span>
                                            <?php if ($reply["edited"]) : ?>
                                                <span class="date">edited: <?= $reply["edited"] ?></span>
                                            <?php endif; ?>
                                            <?php if (hasMinAuth('instructor') || $_user_id == $reply["user_id"]) : ?>
                                                <form method="post" action="delReply">
                                                    <input type="hidden" name="id" value="<?= $reply['id'] ?>" />
                                                    <input type="hidden" name="tab" value="<?= $video ?>" />
                                                    <i class="far fa-trash-alt" data-id=""></i>
                                                </form>
                                                <i class="far fa-edit" data-id="<?= $reply['id'] ?>"></i>
                                            <?php endif; ?>
                                            <div class="vote" data-id="<?= $reply['id'] ?>" <?php if ($reply["vote_id"]) : ?> data-vid="<?= $reply["vote_id"] ?>" <?php endif; ?> <?php if ($reply["vote"]) : ?> data-type="<?= $reply["vote"] > 0 ? "up" : "down" ?>" <?php endif; ?>>
                                                <i class="fas fa-angle-up <?= $reply["vote"] > 0 ? 'selected' : "" ?>" title="Vote Up"></i>
                                                <i class="fas fa-angle-down <?= $reply["vote"] < 0 ? 'selected' : "" ?>" title="Vote Down"></i>
                                            </div>
                                        </div>
                                        <div class="reply mdBox" id="r<?= $reply['id'] ?>"><?= $parsedown->text($reply["text"]) ?></div>
                                    <?php endforeach; ?>
                                    <div class="addReply">add reply</div>
                                </div>
                            <?php endforeach; // comment 
                            ?>
                            <?php if (count($comments) == 0) : ?>
                                <div>No questions or comments yet</div>
                            <?php endif; ?>
                            <h3>Add a question or comment:</h3>
                            <form method="post" action="comment" id="commentForm">
                                <input type="hidden" name="video" value="<?= $info["parts"][2] ?>" />
                                <input type="hidden" name="tab" id="tab" value="<?= $info["parts"][0] ?>" />
                                <textarea name="text" class="commentText" placeholder="Use **markdown** syntax in your text like:&#10;&#10;```javascript&#10;const code = &quot;highlighted&quot;&semi;&#10;```"></textarea>
                                <div>
                                    <div class="commentActions">
                                        <button class="previewBtn">Preview Markdown</button>
                                        <button>Add</button>
                                    </div>
                                    <div class="previewArea"></div>
                                </div>
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

    <?php if (hasMinAuth('instructor')) : ?>
        <div id="overlay">
            <i id="close-overlay" class="fas fa-times-circle"></i>

            <div id="edit_modal" class="modal hide">
                <h2>Edit Video Title</h2>
                <form method="POST" action="title">
                    <input type="hidden" name="file" id="video_file" value="" />
                    <div class="line">
                        <label>Title:</label>
                        <input name="title" id="video_title" value="" />
                    </div>
                    <div class="submit">
                        <button>Submit</button>
                    </div>
                </form>
            </div>

            <div id="content"></div>

            <!-- TODO instead of full page refresh for these this might be 
                a good place to start using HTMX for partial page refreshes -->
            <form method="post" action="decrease" id="decreaseSequence">
                <input type="hidden" name="file" id="up_file" />
                <input type="hidden" name="prev_file" id="prev_file" />
            </form>

            <form method="post" action="increase" id="increaseSequence">
                <input type="hidden" name="file" id="down_file" />
                <input type="hidden" name="next_file" id="next_file" />
            </form>

            <div id="add_modal" class="modal hide">
                <h2>Add Video</h2>
                <form method="POST" action="add" enctype="multipart/form-data">
                    <div class="line">
                        <label>File:</label>
                        <input name="file" type="file" id="add_file" value="" />
                    </div>
                    <div class="line">
                        <label>Title:</label>
                        <input name="title" id="video_title" value="" />
                    </div>
                    <div class="submit">
                        <button>Submit</button>
                    </div>
                </form>
            </div>

        </div>
    <?php endif; ?>

</body>

</html>
