<!DOCTYPE html>
<html>

<head>
    <title>Grade Quiz by Student</title>
    <meta charset="utf-8" />
    <meta name=viewport content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="res/css/lib/font-awesome-all.min.css">
    <link rel="stylesheet" href="res/css/common-1.2.css">
    <link rel="stylesheet" href="res/css/adm.css">
    <link rel="stylesheet" href="res/css/lib/prism.css">
    <link rel="stylesheet" href="res/css/quiz-1.4.css">
    <script src="res/js/lib/prism.js"></script>
    <script src="res/js/markdown-1.3.js"></script>
    <script src="res/js/quiz/gradeUser.js"></script>
    <script src="res/js/ensureSaved.js"></script>
    <script src="res/js/copyAnswer.js"></script>
</head>

<body id="gradeUser">
    <?php include("header.php"); ?>
    <main>
        <nav class="back" title="Back">
            <a href="../grade">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
        </nav>
        <div id="content">
            <!-- Student Being Graded -->
            <h3 id="user" data-user_id="<?= $user['id'] ?>">
                <?= $user['studentID'] ?> <?= $user['knownAs'] ?> <?= $user['lastname'] ?>
            </h3>
            <?php foreach ($events as $event) : ?>
                <div><span class="timestamp"><?= $event['timestamp'] ?> <?= $event['type'] ?></span></div>
            <?php endforeach; ?>

            <!-- For erach question show -->
            <?php foreach ($questions as $question) : ?>
                <div class="qcontainer">
                    <div class="about">
                        <div class="seq"><?= $question['seq'] ?></div>
                        Points Possible: <?= $question['points'] ?> <br />
                        <input class="points" autofocus type="number" value="<?= $answers[$question['id']]['points'] ?? '' ?>" step="0.01" max="<?= $question['points'] ?>" name="points" class="points" />
                    </div>
                    <div class="question textContainer" data-id="<?= $question['id'] ?>">
                        <div>Question Text:</div>
                        <div class="questionText">
                            <?php if ($question['hasMarkDown']) : ?>
                                <?= $parsedown->text($question['text']) ?>
                            <?php else : ?>
                                <pre><?= htmlspecialchars($question['text']) ?></pre>
                            <?php endif; ?>
                        </div>

                        <?php if ($answers[$question['id']] && $answers[$question['id']]['text']) : ?>

                            <div>
                                <strong>Student Answer:</strong>
                                <span class="timestamp"><?= $answers[$question['id']]['created'] ?></span>
                                <?php if ($answers[$question['id']]['updated']) : ?>
                                    <span class="timestamp">&nbsp;&nbsp;&nbsp;&nbsp;updated: <?= $answers[$question['id']]['updated'] ?></span>
                                <?php endif; ?>

                            </div>
                            <div class="answerText">

                                <?php if ($question['type'] == 'text') : ?>
                                    <?php if ($answers[$question['id']]['hasMarkDown']) : ?>
                                        <?= $parsedown->text($answers[$question['id']]['text']) ?>
                                    <?php else : ?>
                                        <pre><?= htmlspecialchars($answers[$question['id']]['text']) ?></pre>
                                    <?php endif; ?>
                                <?php elseif ($question['type'] == "image") : ?>
                                    <img src="<?= $answers[$question['id']]['text'] ?>" />
                                <?php endif; ?>

                            </div>

                        <?php else : ?>

                            <h3>Not Answered</h3>

                        <?php endif; ?>

                        <?php if ($question['modelAnswer']) : ?>
                            <div>Model Answer:</div>
                            <div class="answerText">
                                <?php if ($question['type'] == "text") : ?>
                                    <?php if ($question['mdlAnsHasMD']) : ?>
                                        <?= $parsedown->text($question['modelAnswer']) ?>
                                    <?php else : ?>
                                        <pre><?= htmlspecialchars($question['modelAnswer']) ?></pre>
                                    <?php endif; ?>
                                <?php elseif ($question['type'] == "image") : ?>
                                    <img src="<?= $question['modelAnswer'] ?>" />
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div>Grading Comment:</div>
                        <textarea class="comment" data-id="<?= $answers[$question['id']]['id'] ?>" placeholder="Use **markdown** syntax in your text like:&#10;&#10;```javascript&#10;const code = &quot;highlighted&quot;&semi;&#10;```"><?= $answers[$question['id']]['comment'] ?></textarea>
                        <div>
                            <div class="preview"><button class="previewBtn">Preview Markdown</button></div>
                            <div class="previewArea"></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="done user">

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
