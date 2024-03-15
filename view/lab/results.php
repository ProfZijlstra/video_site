<!DOCTYPE html>
<html>

<head>
    <title>Lab: <?= $lab['name'] ?></title>
    <meta charset="utf-8" />
    <meta name=viewport content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="res/css/lib/font-awesome-all.min.css">
    <link rel="stylesheet" href="res/css/common-1.2.css">
    <link rel="stylesheet" href="res/css/adm.css">
    <link rel="stylesheet" href="res/css/lib/prism.css">
    <link rel="stylesheet" href="res/css/lab.css">
    <script src="res/js/lib/prism.js"></script>
    <script src="res/js/back.js"></script>
</head>

<body id="labResults" class="lab">
    <?php include("header.php"); ?>
    <main>
        <nav id="back" class="back" title="Back">
            <i class="fa-solid fa-arrow-left"></i>
        </nav>
        <nav class="tools">
        </nav>
        <div id="content">
            <div class="about">
                <div><label>Start:</label> <?= $lab['start'] ?></div>
                <div><label>Stop:</label> <?= $lab['stop'] ?></div>
                <?php if ($lab['type'] == "group") : ?>
                    <div id="labGroup">
                        <label>Group:</label> <?= $group ?>
                    </div>
                <?php endif; ?>
            </div>

            <h1 id="lab_id">
                <?= $lab['name'] ?>
            </h1>

            <div class="description">
                <?= $parsedown->text($lab['desc']) ?>
            </div>

            <div class="attachments">
                <?php foreach ($attachments as $attachment) : ?>
                    <div class="attachment">
                        <a target="_blank" href="<?= $attachment['file'] ?>">
                            <i class="fa-solid fa-paperclip"></i>
                            <?= $attachment['name'] ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

            <div id="submission">
                <h2><?= count($deliverables) ?> Deliverable(s) </h2>
                <?php foreach ($deliverables as $deliv) : ?>
                    <?php $delivery = $deliveries[$deliv['id']] ?? [] ?>
                    <div class="dcontainer deliverables">
                        <div class="about">
                            <div class="meta" title="<?= $typeDesc[$deliv['type']] ?> to complete this deliverable">
                                <span class="type">
                                    <?= $deliv['type'] ?>
                                </span>
                            </div>
                            <div title="The lab total is <?= $labPoints ?>, this deliverable is <?= $deliv['points'] ?> of that total">
                                <?php if ($delivery && $delivery['points']) : ?>
                                    Points Received: <br>
                                    <strong><?= $delivery['points'] ?></strong>
                                    of <?= $deliv['points'] ?>
                                <?php else : ?>
                                    <?php $not_graded = true; ?>
                                    Points Possible: <?= $deliv['points'] ?>
                                    <h3>Not graded</h3>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="deliv">
                            <?php if ($delivery) : ?>
                                <div class="stats">
                                    <label title="Hours spent creating this deliverable">Hours:
                                        <?= substr($delivery['duration'], 0, 5); ?>
                                    </label>

                                    <label title="Approximately how far you completed this deliverable" class="completion">Complete:
                                        <?= $delivery['completion'] ?>%
                                    </label>
                                </div>

                                <div class="description">
                                    <?php if ($deliv['hasMarkDown']) : ?>
                                        <?= $parsedown->text($deliv['desc']) ?>
                                    <?php else : ?>
                                        <pre><?= $deliv['desc'] ?></pre>
                                    <?php endif; ?>
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
                                    <div>Your Comment:</div>
                                    <div class="comment">
                                        <?php if ($delivery['stuCmntHasMd']) : ?>
                                            <?= $parsedown->text($delivery['stuComment']) ?>
                                        <?php else : ?>
                                            <pre><?= $delivery['stuComment'] ?></pre>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            <?php else : ?>
                                <h3>Not submitted</h3>
                            <?php endif; ?>
                            <?php if ($delivery['gradeComment']) : ?>
                                <div>Grading Comment:</div>
                                <?php if ($delivery['gradeCmntHasMd']) : ?>
                                    <div class="description">
                                        <?= $parsedown->text($delivery['gradeComment']) ?>
                                    </div>
                                <?php else : ?>
                                    <pre><?= $delivery['gradeComment'] ?></pre>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="done">
                <?php if ($not_graded) : ?>
                    <div>Not all questions have been graded yet</div>
                <?php endif; ?>
                <strong>Total Score:</strong> <?= $received ?> out of <?= $labPoints ?>
                <nav class="back" title="Back">
                    <i class="fa-solid fa-arrow-left"></i>
                </nav>
            </div>
        </div>
    </main>
</body>

</html>
