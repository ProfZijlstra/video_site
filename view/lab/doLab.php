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
    <script src="res/js/markdown-1.1.js"></script>
    <script src="res/js/countdown-1.1.js"></script>
    <script src="res/js/back.js"></script>
    <script>
        window.addEventListener("load", () => {
            COUNTDOWN.start(() => window.location.reload());

            // markdown related functions
            function mdToggle() {
                // TODO: send the hasMarkDown value to the server
            }
            MARKDOWN.enablePreview("../markdown");
            MARKDOWN.activateButtons(mdToggle);
        });
    </script>
</head>

<body id="doLab" class="lab">
    <?php include("header.php"); ?>
    <main>
        <nav id="back" class="back" title="Back">
            <i class="fa-solid fa-arrow-left"></i>
        </nav>
        <nav class="tools">
            <h3 title="Time Remaining">
                <span id="days"><?= $stop->format("%a") ?></span> Day(s)
                <span id="hours"><?= $stop->format("%H") ?></span>:<span id="minutes"><?= $stop->format("%I") ?></span>:<span id="seconds"><?= $stop->format("%S") ?></span>
            </h3>
        </nav>
        <div id="content">
            <div class="about">
                <div><label>Start:</label> <?= $lab['start'] ?></div>
                <div><label>Stop:</label> <?= $lab['stop'] ?></div>
                <?php if ($lab['type'] == "group") : ?>
                    <div><label>Group:</label> <?= $group ?></div>
                <?php endif; ?>
            </div>

            <h1 id="lab_id" data-id="<?= $lab['id'] ?>">
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

            <div class="deliverables">
                <h2><?= count($deliverables) ?> Deliverable(s) </h2>
                <p class="delivInstr">Deliverables with 0:00 hours or 0% complete are auto-graded to zero points</p>
                <?php foreach ($deliverables as $deliv) : ?>
                    <div class="dcontainer deliverables" data-id="<?= $deliv['id'] ?>">
                        <div class="about">
                            <div class="meta" title="<?= $typeDesc[$deliv['type']] ?> to complete this deliverable">
                                <span class="type" data-type="<?= $deliv['type'] ?>">
                                    <?= $deliv['type'] ?>
                                </span>
                            </div>
                            <div title="The lab total is <?= $labPoints ?>, this deliverable is <?= $deliv['points'] ?> of that total">
                                Worth <br />
                                <?= $deliv['points'] ?> of <span class="labPoints"><?= $labPoints ?></span><br />
                                points
                            </div>
                        </div>
                        <div class="deliv">
                            <div class="stats">
                                <label>Hours: </label>
                                <select class="duration" autofocus>
                                    <?php
                                    $now = new DateTime();
                                    $now->setTime(0, 0, 0);
                                    $interval = new DateInterval('PT15M');
                                    ?>
                                    <?php for ($i = 0.25; $i <= 23.75; $i += 0.25) : ?>
                                        <?= $time = $now->format('G:i'); ?>
                                        <option value="<?= $time ?>"><?= $time ?></option>
                                        <?php $now->add($interval); ?>
                                    <?php endfor; ?>
                                </select>

                                <label class="completion">Complete: </label>
                                <select class="completion">
                                    <?php for ($i = 0; $i <= 100; $i += 10) : ?>
                                        <option value="<?= $i ?>"><?= $i ?>%</option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="description">
                                <?= $parsedown->text($deliv['desc']) ?>
                            </div>
                            <?php if ($deliv['type'] == 'txt') : ?>
                                <div class="textContainer">
                                    <textarea class="" placeholder="Please write the text for your deliverable here.&#10;&#10;Feel free to also add any questions or comments about this exercise." data-md="Use **markdown** syntax in your text like:&#10;&#10;```javascript&#10;const code = &quot;highlighted&quot;&semi;&#10;```" data-txt="Write your question text here"></textarea>

                                    <i title="Markdown" class="fa-brands fa-markdown <?= $deliv['hasMarkDown'] ? "active" : "" ?>"></i>
                                    <div class="mdContainer <?= $deliv['hasMarkDown'] ? "active" : "" ?>">
                                        <div class="preview"><button class="previewBtn">Preview Markdown</button></div>
                                        <div class="previewArea"></div>
                                    </div>
                                </div>
                            <?php elseif ($deliv['type'] == 'img' || $deliv['type'] == 'pdf' || $deliv['type'] == 'zip') : ?>
                                <div class="fileContainer">
                                    <input type="file" class="file" />
                                </div>
                            <?php elseif ($deliv['type'] == 'url') : ?>
                                <div class="urlContainer">
                                    <input type="url" class="url" placeholder="https://github.com/student/project" />
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="done">
                <div class="note">Deliverables are saved automatically</div>
                <nav class="back" title="Back">
                    <i class="fa-solid fa-arrow-left"></i>
                </nav>
            </div>
        </div>
    </main>
</body>

</html>
