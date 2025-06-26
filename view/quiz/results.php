<?php
$not_graded = false;
?>
<!DOCTYPE html> <?php global $MY_BASE ?>
<html>

<head>
    <title>Quiz Results</title>
    <meta charset="utf-8" />
    <meta name=viewport content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/lib/font-awesome-all.min.css">
    <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/common-1.3.css">
    <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/adm-1.0.css">
    <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/lib/prism.css">
    <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/quiz-1.11.css">
    <script src="<?= $MY_BASE ?>/res/js/lib/prism.js"></script>
    <script src="<?= $MY_BASE ?>/res/js/markdown-1.8.js"></script>
    <script>
window.addEventListener("load", () => {    
    document.getElementById("total2").innerHTML = document.getElementById("total").innerHTML;
});
    </script>
</head>

<body>
    <?php include 'header.php'; ?>
    <main>
        <nav class="back" title="Back">
            <a href="../../">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
        </nav>
        <nav class="tools">
            <?php if (hasMinAuth('instructor')) { ?>
            <a href="edit">
                <i title="Configure Quiz" class="fa-solid fa-gear"></i>
            </a>
            <a href="grade">
                <i title="Grade Quiz" class="fa-solid fa-magnifying-glass"></i>
            </a>
            <?php } ?>
        </nav>
        <?php include 'areas.php'; ?>
        <div id="content">
            <div id="total2">

            </div>
            <!-- For erach question show -->
            <?php foreach ($questions as $question) { ?>
                <div class="qcontainer">
                    <div class="about">
                        <div class="seq"><?= $question['seq'] ?></div>
                        <div class="points">
                            <?php if ($answers[$question['id']] && ! is_null($answers[$question['id']]['points'])) { ?>
                                Points Received: <br />
                                <strong><?= $answers[$question['id']]['points'] ?></strong>
                                of <?= $question['points'] ?>
                            <?php } else { ?>
                                <?php $not_graded = true; ?>
                                Points Possible: <?= $question['points'] ?>
                                <h3>Not Graded</h3>
                            <?php } ?>
                        </div>
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
                        <?php if ($question['modelAnswer']) { ?>
                            <div>Model Answer:</div>

                            <?php if ($question['type'] == 'text') { ?>
                                <div class="answerText">
                                    <?php if ($question['mdlAnsHasMD']) { ?>
                                        <?= $parsedown->text($question['modelAnswer']) ?>
                                    <?php } else { ?>
                                        <pre><?= htmlspecialchars($question['modelAnswer']) ?></pre>
                                    <?php } ?>
                                </div>
                            <?php } elseif ($question['type'] == 'image') { ?>
                                <img src="<?= $question['modelAnswer'] ?>" />
                            <?php } ?>
                        <?php } ?>

                        <?php if ($answers[$question['id']] && $answers[$question['id']]['text']) { ?>

                            <div>
                                <strong>Your Answer:</strong>
                                <?php if ($answers[$question['id']]['updated']) { ?>
                                    <span class="timestamp"><?= $answers[$question['id']]['updated'] ?></span>
                                <?php } else { ?>
                                    <span class="timestamp"><?= $answers[$question['id']]['created'] ?></span>
                                <?php } ?>
                            </div>
                            <?php if ($question['type'] == 'text') { ?>
                                <div class="answerText">
                                    <?php if ($answers[$question['id']]['hasMarkDown']) { ?>
                                        <?= $parsedown->text($answers[$question['id']]['text']) ?>
                                    <?php } else { ?>
                                        <pre><?= htmlspecialchars($answers[$question['id']]['text']) ?></pre>
                                    <?php } ?>
                                </div>
                            <?php } elseif ($question['type'] == 'image') { ?>
                                <img src="<?= $answers[$question['id']]['text'] ?>" />
                            <?php } ?>

                        <?php } else { ?>
                            <h3>Not Answered</h3>
                        <?php } ?>

                        <?php if ($answers[$question['id']] && $answers[$question['id']]['comment']) { ?>
                            <div>Grading Comment:</div>
                            <div class="answerText">
                                <?= $parsedown->text($answers[$question['id']]['comment']) ?>
                            </div>
                        <?php } ?>


                    </div>
                </div>
            <?php } ?>

            <!-- Total points received out of total possible -->
            <div id="total">
                <?php if ($not_graded) { ?>
                    <div>Not all questions have been graded yet</div>
                <?php } ?>
                <strong>Total Score:</strong> <?= $received ?> out of <?= $possible ?>
            </div>
        </div>
    </main>
</body>

</html>
