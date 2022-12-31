<!DOCTYPE html>
<html>
    <head>
        <title>Quiz: <?= $quiz['name'] ?></title>
        <meta charset="utf-8" />
        <meta name=viewport content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="res/css/font-awesome-all.min.css">
        <link rel="stylesheet" href="res/css/common-1.1.css">
        <link rel="stylesheet" href="res/css/adm.css">
        <link rel="stylesheet" href="res/css/prism.css">
        <link rel="stylesheet" href="res/css/quiz-1.1.css">
        <script src="res/js/prism.js"></script>
        <script src="res/js/markdown.js"></script>
        <script src="res/js/quiz/countdown.js"></script>
        <script src="res/js/quiz/quiz-1.1.js"></script>
    </head>
    <body>
        <?php include("header.php"); ?>
        <main>
            <nav id="back" class="back" title="Back">
                <i class="fa-solid fa-arrow-left"></i>
            </nav>
            <nav class="tools">
                <h3><span id="hours"><?= $stop->format("%H") ?></span>:<span id="minutes"><?= $stop->format("%I") ?></span>:<span id="seconds"><?= $stop->format("%S") ?></span></h3>
            </nav>
            <div id="content">
                <div class="quiz">
                    <div id="quiz_id" class="status" data-id="<?= $quiz['id']?>">
                       <h1><?= $quiz['name']?></h1> 
                    </div>
                </div>

                <?php foreach ($questions as $question): ?>
                    <div class="qcontainer">
                        <div class="about">
                            <div class="seq"><?= $question['seq'] ?></div>
                            <div class="points">Points: <?= $question['points'] ?></div>
                        </div>
                        <div class="question" data-id="<?= $question['id']?>">
                            <div>Question Text:</div> 
                            <div class="questionText">
                                <?= $parsedown->text($question['text']) ?>
                            </div>
                            <div>Your Answer:</div> 
                            <textarea class="answer" data-id="<?= $answers[$question['id']]['id'] ?>" placeholder="Use **markdown** syntax in your text like:&#10;&#10;```javascript&#10;const code = &quot;highlighted&quot;&semi;&#10;```"><?= $answers[$question['id']]['text']?></textarea>
                            <div>
                                <div class="preview"><button class="previewBtn">Preview Markdown</button></div>
                                <div class="previewArea"></div>
                            </div>
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
