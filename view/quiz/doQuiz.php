<!DOCTYPE html>
<html>

<head>
    <title>Quiz: <?= $quiz['name'] ?></title>
    <meta charset="utf-8" />
    <meta name=viewport content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="res/css/lib/font-awesome-all.min.css">
    <link rel="stylesheet" href="res/css/common-1.3.css">
    <link rel="stylesheet" href="res/css/adm-1.0.css">
    <link rel="stylesheet" href="res/css/lib/prism.css">
    <link rel="stylesheet" href="res/css/quiz-1.6.css">
    <script src="res/js/lib/prism.js"></script>
    <script src="res/js/markdown-1.8.js"></script>
    <script src="res/js/camera-1.3.js"></script>
    <script src="res/js/countdown-1.1.js"></script>
    <script src="res/js/quiz/quiz-1.7.js"></script>
    <script src="res/js/ensureSaved.js"></script>
</head>

<body>
    <?php include("header.php"); ?>
    <main>
        <nav id="back" class="back" title="Back">
            <i class="fa-solid fa-arrow-left"></i>
        </nav>
        <?php include("areas.php"); ?>
        <nav class="tools">
            <h3><span id="hours"><?= $stop->format("%H") ?></span>:<span id="minutes"><?= $stop->format("%I") ?></span>:<span id="seconds"><?= $stop->format("%S") ?></span></h3>
        </nav>
        <div id="content">
            <div class="quiz">
                <div id="quiz_id" class="status" data-id="<?= $quiz['id'] ?>">
                    <h1><?= $quiz['name'] ?></h1>
                    <?php if ($user_id): ?>
                        <input type="hidden" id="user_id" value="<?= $user_id ?>">
                    <?php endif; ?>
                </div>
            </div>

            <?php foreach ($questions as $question) : ?>
                <div class="qcontainer">
                    <div class="about">
                        <div class="seq"><?= $question['seq'] ?></div>
                        <div class="points">Points: <?= $question['points'] ?></div>
                    </div>
                    <div class="question camContainer" data-id="<?= $question['id'] ?>">
                        <div class="qType" data-type="<?= $question['type'] ?>">Type: <?= $question['type'] == "text" ? "Text" : "Image Upload" ?></div>
                        <div>Question Text:</div>
                        <div class="questionText">
                            <?php if ($question['hasMarkDown']) : ?>
                                <?= $parsedown->text($question['text']) ?>
                            <?php else : ?>
                                <pre><?= htmlspecialchars($question['text']) ?></pre>
                            <?php endif; ?>
                        </div>
                        <span class="block">Your Answer:</span>
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
                            <span class="block">
                                <input type="file" class="img_replace" />
                                <i title="Upload image" class="fa-solid fa-upload"></i>
                                <i class="fa-solid fa-circle-notch"></i>
                                <i title="Open camera" class="fa-solid fa-camera"></i>
                                <?php if ($answers[$question['id']]) : ?>
                                    <a class="fileLink" href="<?= $answers[$question['id']]['text'] ?>" target="_blank">
                                        <?= basename($answers[$question['id']]['text']) ?>
                                    </a>
                                <?php else : ?>
                                    <a class="fileLink" href="" target="_blank"></a>
                                <?php endif; ?>
                                <i title="Delete" 
                                    class="fa-solid fa-trash-can <?= $answers[$question['id']] ? '' : 'hide' ?>" 
                                    data-id="<?= $answers[$question['id']]['id'] ?>"></i>
                            </span>
                            <div class="camera">
                                <video></video>
                                <div title="Close Camera" class="closeCamera hide">
                                    <i class="fa-solid fa-xmark"></i>
                                </div>
                                <div title="[Space] Take picture" class="takePicture hide"></div>
                                <div title="Switch Camera" class="switchCamera hide">
                                    <i class="fas fa-sync"></i>
                                    <div class="camera_icon">
                                        <i class="fas fa-camera"></i>
                                    </div>
                                </div>
                                <canvas></canvas>
                            </div>
                            <?php if ($answers[$question['id']]) : ?>
                                <img class="answer" data-id="<?= $answers[$question['id']]['id'] ?>" src="<?= $answers[$question['id']]['text'] ?>" />
                            <?php else : ?>
                                <img class="answer hide" />
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            <div class="done">
                <div class="note">Answers are saved automatically</div>
                <form method="POST" action="<?= $quiz['id'] ?>/finish">
                    <button id="finish">Finish Quiz</button>
                </form>
            </div>
        </div>
    </main>
</body>

</html>
