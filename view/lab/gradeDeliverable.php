<!DOCTYPE html>
<html>

<head>
    <title>Grade Deliverable</title>
    <meta charset="utf-8" />
    <meta name=viewport content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="res/css/lib/font-awesome-all.min.css">
    <link rel="stylesheet" href="res/css/common-1.3.css">
    <link rel="stylesheet" href="res/css/adm-1.0.css">
    <link rel="stylesheet" href="res/css/lib/prism.css">
    <link rel="stylesheet" href="res/css/lab-1.10.css">
    <script src="res/js/lib/prism.js"></script>
    <script src="res/js/lab/gradeDeliverable.js"></script>
    <script src="res/js/markdown-1.8.js"></script>
    <script src="res/js/ensureSaved.js"></script>
</head>

<body id="gradeDeliverable" class="lab grade labDeliverables">
    <?php include 'header.php'; ?>
    <main>
        <nav class="back" title="Back">
            <a href="../grade">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
        </nav>
        <?php include 'areas.php'; ?>
        <nav class="tools">
        </nav>
        <div id="content">
                <h2>
                    <span>Deliverable</span>
                    <a href="<?= $prev_id ?>">
                        <i id="chevLeft" class="fa-solid fa-chevron-left <?= $prev_id ? 'active' : '' ?>"></i>
                    </a>
                    <?php for ($i = 0; $i < count($deliverables); $i++) { ?>
                    <a class="delivNum <?= $deliverables[$i]['id'] == $deliv['id'] ? 'active' : '' ?>"
                        href="./<?= $deliverables[$i]['id']?>">
                        <?= $i + 1 ?>
                    </a>
                    <?php } ?>
                    <a href="<?= $next_id ?>">
                        <i id="chevRight" class="fa-solid fa-chevron-right <?= $next_id ? 'active' : '' ?>"></i>
                    </a>
                    <span>of <?= count($deliverables) ?></span>
                </h2>
            <div class="dcontainer deliverables">
                <div class="about">
                    <div class="meta" title="<?= $typeDesc[$deliv['type']] ?> to complete this deliverable">
                        <span class="type">
                            <?= $deliv['type'] ?>
                        </span>
                    </div>
                    <div>
                        Points possible: <?= $deliv['points'] ?> <br />
                    </div>
                </div>
                <div class="deliv" data-id="<?= $deliv['id'] ?>">
                    <div>Deliverable Description:</div>
                    <div class="description">
                        <?php if ($deliv['hasMarkDown']) { ?>
                            <?= $parsedown->text($deliv['desc']) ?>
                        <?php } else { ?>
                            <pre><?= htmlspecialchars($deliv['desc']) ?></pre>
                        <?php } ?>
                    </div>

                    <div class="attachments">
                        <?php foreach ($attachments as $attachment) { ?>
                        <div class="attachment">
                            <?php if ($attachment['type'] == 'zip') { ?>
                                <a target="_blank" href="<?= $lab['id'].'/download/'.$attachment['id'] ?>">
                            <?php } else { ?>
                                <a target="_blank" href="<?= $attachment['file'] ?>">
                            <?php } ?>
                                    <i class="fa-solid fa-paperclip"></i>
                                    <?= $attachment['name'] ?>
                                </a>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <div class="note">
                <i class="fa-solid fa-keyboard"></i> CTRL+< for prev deliverable, CTRL+> for next deliverable<br>
                <i class="fa-solid fa-keyboard"></i> Pressing CTRL+M inside a comment textarea toggles markdown <br>
                <i class="fa-solid fa-keyboard"></i> Pressing N or P inside a points field takes you to the next / previous user
            </div>

            <div class="gradeContainer">
                <div class="header">Group/User</div>
                <div class="header">Answer</div>
                <div class="header">Comment</div>
                <div class="header">Points</div>

                <?php for ($i = 0; $i < count($deliveries); $i++) { ?>
                    <?php $delivery = $deliveries[$i]; ?>
                    <div class="users">
                        <div>
                            <a href="../submission/<?= $delivery['submission_id']?>">
                                <?php if ($delivery['group']) { ?>
                                    <?= $delivery['group'] ?>:
                                <?php } ?>
                                <?= $delivery['knownAs'] ?>
                                <?= $delivery['lastname'] ?>
                            </a>
                        </div>
                        <div class="timestamp">Created: <?= $delivery['created'] ?></div>
                        <div class="timestamp">Updated: <?= $delivery['updated'] ?></div>

                        <?php if ($deliv['type'] == 'zip') { ?>
                        <div>Failed Checks:</div>
                        <div class="stats">
                            <?php foreach ($stats as $stat) { ?>
                            <?php if ($stat['delivery_id'] == $delivery['id']) { ?>
                            <div>
                                <?= $stat['created']?>
                                <span class="error"><?= $stat['type']?></span>
                                <?= $stat['file']?>
                                <span class="error"><?= $stat['comment']?></span>
                            </div>
                            <?php } ?>
                            <?php } ?>
                        </div>
                        <?php } ?>
                    </div>
                    <div class="delivery">
                        <div class="stats">
                            <label title="Hours spent creating this deliverable">
                                Hours:
                                <?= substr($delivery['duration'], 0, 5) ?>
                            </label>
                            <label title="Approximately how far you completed this deliverable" class="completion">
                                Complete:
                                <?= $delivery['completion'] ?>%
                            </label>
                        </div>
                        <?php if ($deliv['type'] == 'txt') { ?>
                            <div class="txtDelivery">
                                <?php if ($delivery['hasMarkDown']) { ?>
                                    <?= $parsedown->text($delivery['text']) ?>
                                <?php } else { ?>
                                    <pre><?= htmlspecialchars($delivery['text']) ?></pre>
                                <?php } ?>
                            </div>
                        <?php } else { ?>
                            <?php if ($deliv['type'] == 'url') { ?>
                                <div class="urlContainer">
                                    <a href="<?= $delivery['text'] ?>" target="_blank"><?= $delivery['text'] ?></a>
                                </div>
                            <?php } else { /* type is: img, pdf, zip */ ?>
                                <div class="fileContainer">
                                    <a class="fileLink" href="<?= $delivery['file'] ?>" target="_blank"><?= $delivery['name'] ?></a>
                                    <?php if ($deliv['type'] == 'img') { ?>
                                        <img src="<?= $delivery['file'] ?>" class="<?= $delivery['file'] ? 'show' : '' ?>">
                                    <?php } elseif ($deliv['type'] == 'zip') { ?>
                                        <div class="listing"><?= $delivery['text'] ?></div>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        <?php } ?>

                        <?php if ($delivery['stuComment']) { ?>
                            <div>Submission Comment:</div>
                            <div class="comment">
                                <?php if ($delivery['stuCmntHasMD']) { ?>
                                    <?= $parsedown->text($delivery['stuComment']) ?>
                                <?php } else { ?>
                                    <pre><?= htmlspecialchars($delivery['stuComment']) ?></pre>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="comment" data-delivery_id="<?= $delivery['id'] ?>">
                        <textarea autofocus class="comment" placeholder="Use **markdown** syntax in your text like:&#10;&#10;```javascript&#10;const code = &quot;highlighted&quot;&semi;&#10;```"><?= $delivery['gradeComment'] ?></textarea>

                        <i title="Markdown" class="txt fa-brands fa-markdown <?= $delivery['gradeCmntHasMD'] ? 'active' : ''?>"></i>
                        <div class="mdContainer <?= $delivery['gradeCmntHasMD'] ? 'active' : ''?>">
                            <i title="Preview Markdown" class="fa-solid fa-eye previewBtn"></i>
                            <div class="previewArea"></div>
                        </div>
                    </div>
                    <div class="points">
                        <input type="number" value="<?= $delivery['points'] ?? '' ?>" step="0.01" max="<?= $deliv['points'] ?>" min="0" name="points" class="points" />
                    </div>
                <?php } ?>
            </div>

            <div class="done">

                <?php if ($prev_id) { ?>
                    <a href="<?= $prev_id ?>">
                        <i title="Previous Deliverable" class="fa-solid fa-arrow-left"></i>
                    </a>
                <?php } ?>
                <?php if ($next_id) { ?>
                    <a href="<?= $next_id ?>">
                        <i title="Next Deliverable" class="fa-solid fa-arrow-right"></i>
                    </a>
                <?php } ?>


                <a href="../grade">
                    <i title="Finish Grading" class="fa-solid fa-check"></i>
                </a>

            </div>
        </div>
    </main>
</body>

</html>
