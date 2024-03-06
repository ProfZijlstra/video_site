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
            <h1 id="lab_id" data-id="<?= $lab['id'] ?>">
                Lab: <?= $lab['name'] ?>
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

            <?php foreach ($questions as $question) : ?>
                <div class="qcontainer">
                    <div class="about">
                        <div class="seq"><?= $question['seq'] ?></div>
                        <div class="points">Points: <?= $question['points'] ?></div>
                    </div>
                    <div class="question" data-id="<?= $question['id'] ?>">
                        <div class="qType" data-type="<?= $question['type'] ?>">Type: <?= $question['type'] == "text" ? "Text" : "Image Upload" ?></div>
                        <div>Question Text:</div>
                        <div class="questionText">
                            <?php if ($question['hasMarkDown']) : ?>
                                <?= $parsedown->text($question['text']) ?>
                            <?php else : ?>
                                <pre><?= $question['text'] ?></pre>
                            <?php endif; ?>
                        </div>
                        <div>Your Answer:</div>
                        <?php if ($question['type'] == "text") : ?>
                            <div class="textContainer">
                                <i title="Markdown" class="fa-brands fa-markdown <?= $answers[$question['id']]['hasMarkDown'] ? 'active' : "" ?>"></i>
                                <textarea class="answer" data-id="<?= $answers[$question['id']]['id'] ?>" data-md="Use **markdown** syntax in your text like:&#10;&#10;```javascript&#10;const code = &quot;highlighted&quot;&semi;&#10;```" data-txt="Write your answer here" placeholder="Write your answer here"><?= $answers[$question['id']]['text'] ?></textarea>
                                <div class="mdContainer <?= $answers[$question['id']]['hasMarkDown'] ? 'active' : "" ?>">
                                    <div class="preview"><button tabindex="-1" class="previewBtn">Preview Markdown</button></div>
                                    <div class="previewArea"></div>
                                </div>
                            </div>
                        <?php elseif ($question['type'] == "image") : ?>
                            <?php if ($answers[$question['id']]) : ?>
                                <img class="answer" data-id="<?= $answers[$question['id']]['id'] ?>" src="<?= $answers[$question['id']]['text'] ?>" />
                            <?php else : ?>
                                <img class="answer hide" />
                            <?php endif; ?>
                            <div>
                                <?php if ($answers[$question['id']]) : ?>
                                    <label>Upload Replacement: </label>
                                <?php else : ?>
                                    <label>Upload Answer: </label>
                                <?php endif; ?>
                                <input type="file" class="img_replace" />
                                <i class="fa-solid fa-circle-notch"></i>
                            </div>

                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
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
