<!DOCTYPE html>
<html>

<head>
    <title>Grade Lab by Submission</title>
    <meta charset="utf-8" />
    <meta name=viewport content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="res/css/lib/font-awesome-all.min.css">
    <link rel="stylesheet" href="res/css/common-1.3.css">
    <link rel="stylesheet" href="res/css/adm.css">
    <link rel="stylesheet" href="res/css/lib/prism.css">
    <link rel="stylesheet" href="res/css/lab.css">
    <script src="res/js/lib/prism.js"></script>
    <script src="res/js/markdown-1.6.js"></script>
    <script src="res/js/lab/gradeSubmission.js"></script>
    <script src="res/js/ensureSaved.js"></script>
</head>

<body id="gradeSubmission" class="lab grade labDeliverables">
    <?php include("header.php"); ?>
    <main>
        <nav class="back" title="Back">
            <a href="../grade">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
        </nav>
        <div id="content">
            <!-- Submission Being Graded -->
            <?php if ($submission['group']) : ?>
                <h2 data-id="<?= $submission['id'] ?>" data-group="<?= $submission['group'] ?>">
                    Group: <?= $submission['group'] ?>
                </h2>
                <div class="members">
                    <?php foreach ($members as $member) : ?>
                        <div><?= $member['knownAs'] ?> <?= $member['lastname'] ?></div>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <h2 data-id="<?= $submission['id'] ?>" data-user="<?= $members[0]['id'] ?>">
                    Student: <?= $members[0]['knownAs'] ?> <?= $members[0]['lastname'] ?>
                </h2>
            <?php endif; ?>

            <div class="timestamp">Submission created: <?= $submission['start'] ?></div>
            <div class="timestamp">Submission updated: <?= $submission['stop'] ?></div>

            <!-- For erach deliverable show -->
            <?php foreach ($deliverables as $deliv) : ?>
                <?php $delivery = $deliveries[$deliv['id']] ?? [] ?>
                <div class="dcontainer deliverables" data-id="<?= $delivery['id'] ?>" data-deliverable="<?= $deliv['id'] ?>">
                    <div class="about">
                        <div class="meta" title="<?= $typeDesc[$deliv['type']] ?> to complete this deliverable">
                            <span class="type">
                                <?= $deliv['type'] ?>
                            </span>
                        </div>
                        Points Possible: <?= $deliv['points'] ?> <br />
                        <input autofocus class="points" type="number" value="<?= $delivery['points'] ?? '' ?>" step="0.01" max="<?= $deliv['points'] ?>" min="0" name="points" class="points" data-value="<?= $delivery['points'] ? $delivery['points'] : 0 ?>" />
                    </div>
                    <div class="deliv">
                        <h3>Deliverable Description</h3>
                        <div class="description">
                            <?php if ($deliv['hasMarkDown']) : ?>
                                <?= $parsedown->text($deliv['desc']) ?>
                            <?php else : ?>
                                <pre><?= htmlspecialchars($deliv['desc']) ?></pre>
                            <?php endif; ?>
                        </div>
                        <?php if ($delivery) : ?>
                            <h3>Submitted by Student</h3>
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
                                        <pre><?= htmlspecialchars($delivery['text'] ?: "") ?></pre>
                                    <?php endif; ?>
                                </div>
                            <?php else : ?>
                                <?php if ($deliv['type'] == 'url') : ?>
                                    <div class="urlContainer">
                                        <a href="<?= $delivery['text'] ?>" target="_blank"><?= $delivery['text'] ?></a>
                                    </div>
                                <?php else : /* type is: img, pdf, zip */ ?>
                                    <div class="fileContainer">
                                        <a class="fileLink" href="<?= $delivery['file'] ?>" target="_blank"><?= $delivery['name'] ?></a>
                                        <?php if ($deliv['type'] == 'img') : ?>
                                            <img src="<?= $delivery['file'] ?>" class="<?= $delivery['file'] ? 'show' : '' ?>">
                                        <?php elseif ($deliv['type'] == "zip") : ?>
                                            <div class="listing"><?= $delivery['text'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php if ($delivery['stuComment']) : ?>
                                <h3>Student Comment:</h3>
                                <div class="comment">
                                    <?php if ($delivery['stuCmntHasMD']) : ?>
                                        <?= $parsedown->text($delivery['stuComment']) ?>
                                    <?php else : ?>
                                        <pre><?= htmlspecialchars($delivery['stuComment']) ?></pre>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                        <?php else : ?>
                            <h2>Not Submitted</h2>
                        <?php endif; ?>

                        <h3>Grading Comment:</h3>
                        <div class="textContainer">
                            <textarea class="comment" data-id="<?= $delivery['id'] ?>" placeholder="Write grading comments here" data-txt="Write grading comments here" data-md="Use **markdown** syntax in your text like:&#10;&#10;```javascript&#10;const code = &quot;highlighted&quot;&semi;&#10;```"><?= $delivery['gradeComment'] ?></textarea>
                            <i title="Markdown" class="cmt fa-brands fa-markdown <?= $delivery['gradeCmntHasMD'] ? "active" : "" ?>"></i>
                            <div class="mdContainer <?= $delivery['gradeCmntHasMD'] ? "active" : "" ?>">
                                <div class="preview"><button class="previewBtn">Preview Markdown</button></div>
                                <div class="previewArea"></div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="done">

                <?php if ($idx > 0) : ?>
                    <a href="<?= $ids[$idx - 1] ?>?idx=<?= $idx - 1 ?>">
                        <i title="Previous Submission" class="fa-solid fa-arrow-left"></i>
                    </a>
                <?php endif; ?>
                <?php if ($idx < count($ids) - 1) : ?>
                    <a href="<?= $ids[$idx + 1] ?>?idx=<?= $idx + 1 ?>">
                        <i title="Next Submission" class="fa-solid fa-arrow-right"></i>
                    </a>
                <?php endif; ?>


                <a href="../grade">
                    <i title="Finish Grading" class="fa-solid fa-check"></i>
                </a>
            </div>
        </div>
    </main>
</body>

</html>
