<!DOCTYPE html>
<html>

<head>
    <title>Grade Question</title>
    <meta charset="utf-8" />
    <meta name=viewport content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="res/css/lib/font-awesome-all.min.css">
    <link rel="stylesheet" href="res/css/common-1.2.css">
    <link rel="stylesheet" href="res/css/adm.css">
    <link rel="stylesheet" href="res/css/lib/prism.css">
    <link rel="stylesheet" href="res/css/lab.css">
    <script src="res/js/lib/prism.js"></script>
    <script src="res/js/markdown-1.1.js"></script>
    <script src="res/js/ensureSaved.js"></script>
    <script></script>
</head>

<body id="gradeDeliverable" class="lab grade labDeliverables">
    <?php include("header.php"); ?>
    <main>
        <nav class="back" title="Back">
            <a href="../grade">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
        </nav>

        <nav class="tools">
            <?php if ($prev_id) : ?>
                <a href="<?= $prev_id ?>">
                    <i title="Previous Deliverable" class="fa-solid fa-arrow-left"></i>
                </a>
            <?php endif; ?>
            <?php if ($next_id) : ?>
                <a href="<?= $next_id ?>">
                    <i title="Next Deliverable" class="fa-solid fa-arrow-right"></i>
                </a>
            <?php endif; ?>
        </nav>
        <div id="content">
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
                        <?php if ($deliv['hasMarkDown']) : ?>
                            <?= $parsedown->text($deliv['desc']) ?>
                        <?php else : ?>
                            <pre><?= $deliv['desc'] ?></pre>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <table>
                <tr>
                    <th>Group/User</th>
                    <th>Answer</th>
                    <th>Comment</th>
                    <th>Points</th>
                </tr>
                <?php for ($i = 0; $i < count($deliveries); $i++) : ?>
                    <?php $delivery = $deliveries[$i]; ?>
                    <tr>
                        <td class="users">
                            <div>
                                <?php if ($delivery['group']) : ?>
                                    Group: <?= $delivery['group'] ?>,
                                <?php endif; ?>
                                <?= $delivery['knownAs'] ?>
                                <?= $delivery['lastname'] ?>
                            </div>
                            <div class="timestamp">Created: <?= substr($delivery['created'], 11) ?></div>
                            <div class="timestamp">Updated: <?= substr($delivery['updated'], 11) ?></div>
                        </td>
                        <td class="delivery">
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
                            <?php if ($deliv['type'] == 'txt') : ?>
                                <div class="txtDelivery">
                                    <?php if ($delivery['hasMarkDown']) : ?>
                                        <?= $parsedown->text($delivery['text']) ?>
                                    <?php else : ?>
                                        <pre><?= $delivery['text'] ?></pre>
                                    <?php endif; ?>
                                </div>
                            <?php else : ?>
                                <?php if ($deliv['type'] == 'url') : ?>
                                    <div class="urlContainer">
                                        <a href="<?= $delivery['text'] ?>"><?= $delivery['text'] ?></a>
                                    </div>
                                <?php else : /* type is: img, pdf, zip */ ?>
                                    <div class="fileContainer">
                                        <a class="fileLink" href="<?= $delivery['file'] ?>" target="_blank"><?= $delivery['name'] ?></a>
                                        <?php if ($deliv['type'] == 'img') : ?>
                                            <img src="<?= $delivery['file'] ?>" class="<?= $delivery['file'] ? 'show' : '' ?>">
                                        <?php elseif ($deliv['type'] == "zip") : ?>
                                            <pre class="listing"><?= $delivery['text'] ?></pre>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php if ($delivery['stuComment']) : ?>
                                <div>Submission Comment:</div>
                                <div class="comment">
                                    <?php if ($delivery['stuCmntHasMd']) : ?>
                                        <?= $parsedown->text($delivery['stuComment']) ?>
                                    <?php else : ?>
                                        <pre><?= $delivery['stuComment'] ?></pre>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </td>

                        <td class="comment">
                            <textarea class="comment" placeholder="Use **markdown** syntax in your text like:&#10;&#10;```javascript&#10;const code = &quot;highlighted&quot;&semi;&#10;```"><?= $delivery['gradeComment'] ?></textarea>
                        </td>
                        <td class="points">
                            <input type="number" value="<?= $delivery['points'] ? $delivery['points'] : 0 ?>" step="0.01" max="<?= $question['points'] ?>" name="points" class="points" />
                        </td>
                    </tr>
                <?php endfor; ?>
            </table>

            <div class="done">

                <?php if ($prev_id) : ?>
                    <a href="<?= $prev_id ?>" tabindex="-1">
                        <i title="Previous Deliverable" class="fa-solid fa-arrow-left"></i>
                    </a>
                <?php endif; ?>
                <?php if ($next_id) : ?>
                    <a href="<?= $next_id ?>" tabindex="-1">
                        <i title="Next Deliverable" class="fa-solid fa-arrow-right"></i>
                    </a>
                <?php endif; ?>


                <a href="../grade" tabindex="-1">
                    <i title="Finish Grading" class="fa-solid fa-check"></i>
                </a>
            </div>
        </div>
    </main>
</body>

</html>
