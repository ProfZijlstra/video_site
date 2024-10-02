<?php
$not_graded = false;
?>
<!DOCTYPE html>
<html>

<head>
    <title>Quiz Results</title>
    <meta charset="utf-8" />
    <meta name=viewport content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="res/css/lib/font-awesome-all.min.css">
    <link rel="stylesheet" href="res/css/common-1.3.css">
    <link rel="stylesheet" href="res/css/adm.css">
    <link rel="stylesheet" href="res/css/lib/prism.css">
    <link rel="stylesheet" href="res/css/quiz-1.5.css">
    <script src="res/js/lib/prism.js"></script>
    <script src="res/js/markdown-1.8.js"></script>
    <script>
window.addEventListener("load", () => {    
    document.getElementById("total2").innerHTML = document.getElementById("total").innerHTML;
});
    </script>
</head>

<body>
    <?php include("header.php"); ?>
    <main>
        <nav class="back" title="Back">
            <a href="../quiz">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
        </nav>
        <?php include("areas.php"); ?>
        <div id="content">
            <div id="total2">

            </div>
            <!-- For erach question show -->
            <?php foreach ($questions as $question) : ?>
                <div class="qcontainer">
                    <div class="about">
                        <div class="seq"><?= $question['seq'] ?></div>
                        <div class="points">
                            <?php if ($answers[$question['id']] && $answers[$question['id']]['points']) : ?>
                                Points Received: <br />
                                <strong><?= $answers[$question['id']]['points'] ?></strong>
                                of <?= $question['points'] ?>
                            <?php else : ?>
                                <?php $not_graded = true; ?>
                                Points Possible: <?= $question['points'] ?>
                                <h3>Not Graded</h3>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="question" data-id="<?= $question['id'] ?>">
                        <div>Question Text:</div>
                        <div class="questionText">
                            <?php if ($question['hasMarkDown']) : ?>
                                <?= $parsedown->text($question['text']) ?>
                            <?php else : ?>
                                <pre><?= htmlspecialchars($question['text']) ?></pre>
                            <?php endif; ?>
                        </div>
                        <?php if ($question['modelAnswer']) : ?>
                            <div>Model Answer:</div>

                            <?php if ($question['type'] == "text") : ?>
                                <div class="answerText">
                                    <?php if ($question['mdlAnsHasMD']) : ?>
                                        <?= $parsedown->text($question['modelAnswer']) ?>
                                    <?php else : ?>
                                        <pre><?= htmlspecialchars($question['modelAnswer']) ?></pre>
                                    <?php endif; ?>
                                </div>
                            <?php elseif ($question['type'] == "image") : ?>
                                <img src="<?= $question['modelAnswer'] ?>" />
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if ($answers[$question['id']] && $answers[$question['id']]['text']) : ?>

                            <div>
                                <strong>Your Answer:</strong>
                                <?php if ($answers[$question['id']]['updated']) : ?>
                                    <span class="timestamp"><?= $answers[$question['id']]['updated'] ?></span>
                                <?php else : ?>
                                    <span class="timestamp"><?= $answers[$question['id']]['created'] ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if ($question['type'] == "text") : ?>
                                <div class="answerText">
                                    <?php if ($answers[$question['id']]['hasMarkDown']) : ?>
                                        <?= $parsedown->text($answers[$question['id']]['text']) ?>
                                    <?php else : ?>
                                        <pre><?= htmlspecialchars($answers[$question['id']]['text']) ?></pre>
                                    <?php endif; ?>
                                </div>
                            <?php elseif ($question['type'] == "image") : ?>
                                <img src="<?= $answers[$question['id']]['text'] ?>" />
                            <?php endif; ?>

                        <?php else : ?>
                            <h3>Not Answered</h3>
                        <?php endif; ?>

                        <?php if ($answers[$question['id']] && $answers[$question['id']]['comment']) : ?>
                            <div>Grading Comment:</div>
                            <div class="answerText">
                                <?= $parsedown->text($answers[$question['id']]['comment']) ?>
                            </div>
                        <?php endif; ?>


                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Total points received out of total possible -->
            <div id="total">
                <?php if ($not_graded) : ?>
                    <div>Not all questions have been graded yet</div>
                <?php endif; ?>
                <strong>Total Score:</strong> <?= $received ?> out of <?= $possible ?>
            </div>
        </div>
    </main>
</body>

</html>
