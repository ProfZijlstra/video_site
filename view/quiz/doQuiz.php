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
    <link rel="stylesheet" href="res/css/quiz-1.7.css">
    <script src="res/js/lib/prism.js"></script>
    <script src="res/js/markdown-1.8.js"></script>
    <script src="res/js/camera-1.4.js"></script>
    <script src="res/js/countdown-1.1.js"></script>
    <script src="res/js/quiz/quiz-1.8.js"></script>
    <script src="res/js/ensureSaved.js"></script>
    <script src="res/js/lab_quiz_spa.js"></script>
</head>

<body id="doQuiz" data-selected="<?= $selected ?>">
    <?php include 'header.php'; ?>
    <main>
        <nav id="back" class="back" title="Back">
            <i class="fa-solid fa-arrow-left"></i>
        </nav>
        <?php include 'areas.php'; ?>
        <nav class="tools">
            <h3><span id="hours"><?= $stop->format('%H') ?></span>:<span id="minutes"><?= $stop->format('%I') ?></span>:<span id="seconds"><?= $stop->format('%S') ?></span></h3>
            <div class="icons">
                <i id="multiPage" title="Switch to multi-page" class="fa-solid fa-expand <?= $selected ? 'hide' : '' ?>"></i>
                <i id="keyShortCuts"
                    title="CTRL+> next, CTRL+< previous"
                    class="fa-regular fa-keyboard <?= $selected ? '' : 'hide' ?>"></i>
                <i id="singlePage" title="Switch to single-page" class="fa-solid fa-compress <?= $selected ? '' : 'hide' ?>"></i>
            </div>
        </nav>
        <div id="content">
            <div class="quiz">
                <div id="quiz_id" class="status" data-id="<?= $quiz['id'] ?>">
                    <h2 class="single <?= $selected ? 'hide' : '' ?>"><?= count($questions) ?> Question(s)</h2>
                    <h2 class="multi <?= $selected ? '' : 'hide' ?>">
                        <span class="mobileBlock">Question</span>
                        <i id="chevLeft" class="fa-solid fa-chevron-left <?= $selected && $selected > 1 ? 'active' : '' ?>"></i>
                        <?php for ($i = 1; $i <= count($questions); $i++) { ?>
                        <span id="db<?= $i ?>" class="questNum <?= $i == $selected ? 'active' : '' ?>"><?= $i ?></span>
                        <?php } ?>
                        <i id="chevRight" class="fa-solid fa-chevron-right <?= $selected && $selected < count($questions) ? 'active' : '' ?>"></i>
                        <span>of <?= count($questions) ?></span>
                    </h2>
                    <?php if ($user_id) { ?>
                        <input type="hidden" id="user_id" value="<?= $user_id ?>">
                    <?php } ?>
                </div>
            </div>

            <?php $i = 0; ?>
            <?php foreach ($questions as $question) { ?>
                <?php $i++; ?>
                <div id="d<?= $i ?>" class="qcontainer  <?= $selected ? ($selected == $i ? '' : 'hide') : '' ?>">
                    <div class="about">
                        <div class="qType"><?= $question['type'] == 'text' ? 'txt' : 'img' ?></div>
                        <div class="points">Points: <?= $question['points'] ?></div>
                    </div>

                    <div class="question" data-id="<?= $question['id'] ?>">
                        <div>Question Text:</div>
                        <div class="questionText">
                            <?php if ($question['hasMarkDown']) { ?>
                                <?= $parsedown->text($question['text']) ?>
                            <?php } else { ?>
                                <pre><?= htmlspecialchars($question['text']) ?></pre>
                            <?php } ?>
                        </div>
                    </div> <!-- close question -->

                    <?php $answer = $answers[$question['id']] ?>
                    <div class="answer camContainer" data-id="<?= $question['id'] ?>">
                        <div>Your Answer:</div>
                        <?php if ($question['type'] == 'text') { ?>
                            <div class="textContainer">
                                <i title="Markdown" class="fa-brands fa-markdown <?= $answer['hasMarkDown'] ? 'active' : '' ?>"></i>
                            <textarea class="answer" data-id="<?= $answer['id'] ?>" 
                                data-md="Use **markdown** syntax in your text like:&#10;&#10;```javascript&#10;const code = &quot;highlighted&quot;&semi;&#10;```" 
                                data-txt="Write your answer here" 
                                placeholder="Write your answer here"><?= $answer['text'] ?></textarea>
                            <div class="mdContainer <?= $answer['hasMarkDown'] ? 'active' : '' ?>">
                                <div class="preview">
                                    <button tabindex="-1" class="previewBtn">Preview Markdown</button>
                                </div>
                                <div class="previewArea"></div>
                                </div>
                            </div>
                        <?php } elseif ($question['type'] == 'image') { ?>
                            <span class="block">
                                <input type="file" class="img_replace" />
                                <i title="Upload image" class="fa-solid fa-upload"></i>
                                <i class="fa-solid fa-circle-notch"></i>
                                <i title="Open camera" class="fa-solid fa-camera"></i>
                                <?php if ($answer) { ?>
                                    <a class="fileLink" href="<?= $answer['text'] ?>" target="_blank">
                                        <?= basename($answer['text']) ?>
                                    </a>
                                <?php } else { ?>
                                    <a class="fileLink" href="" target="_blank"></a>
                                <?php } ?>
                                <i title="Delete" 
                                    class="fa-solid fa-trash-can <?= $answer ? '' : 'hide' ?>" 
                                    data-id="<?= $answer['id'] ?>"></i>
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
                            <?php if ($answer) { ?>
                                <img class="answer" data-id="<?= $answer['id'] ?>" src="<?= $answer['text'] ?>" />
                            <?php } else { ?>
                                <img class="answer hide" />
                            <?php } ?>
                        <?php } ?>
                    </div> <!-- close answer -->
                </div>
            <?php } ?>
            <div class="done">
                <div class="note">Answers are saved automatically</div>
                    <div class="finish <?= $selected != count($questions) ? 'hide' : '' ?>">
                        <form id="finishQuiz" method="POST" action="<?= $selected ? '../' : ''?><?= $quiz['id'] ?>/finish">
                        <button id="finish">Finish Quiz</button>
                    </form>
                </div>
            </div>
        </div>
    </main>
</body>

</html>
