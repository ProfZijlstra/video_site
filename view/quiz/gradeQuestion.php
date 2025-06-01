<!DOCTYPE html>
<html>

<head>
    <title>Grade Question</title>
    <meta charset="utf-8" />
    <meta name=viewport content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="res/css/lib/font-awesome-all.min.css">
    <link rel="stylesheet" href="res/css/common-1.3.css">
    <link rel="stylesheet" href="res/css/adm-1.0.css">
    <link rel="stylesheet" href="res/css/lib/prism.css">
    <link rel="stylesheet" href="res/css/quiz-1.10.css">
    <script src="res/js/lib/prism.js"></script>
    <script src="res/js/markdown-1.8.js"></script>
    <script src="res/js/quiz/gradeQuestion-1.0.js"></script>
    <script src="res/js/ensureSaved.js"></script>
</head>

<body id="gradeQuestion">
    <?php include("header.php"); ?>
    <main>
        <nav class="back" title="Back">
            <a href="../grade">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
        </nav>
        <?php include("areas.php"); ?>

        <nav class="tools">
            <?php if ($prev_id) : ?>
                <a href="<?= $prev_id ?>"><i title="Previous Question" class="fa-solid fa-arrow-left"></i></a>
            <?php endif; ?>
            <?php if ($next_id) : ?>
                <a href="<?= $next_id ?>"><i title="Next Question" class="fa-solid fa-arrow-right"></i></a>
            <?php endif; ?>
        </nav>
        <div id="content">
            <div class="qcontainer">
                <div class="about">
                    <div class="seq"><?= $question['seq'] ?></div>
                    <div class="points">Points: <?= $question['points'] ?></div>
                </div>
                <div class="question" data-id="<?= $question['id'] ?>">
                    <div>Question Text:</div>
                    <div class="text">
                        <?php if ($question['hasMarkDown']) : ?>
                            <?= $parsedown->text($question['text']) ?>
                        <?php else : ?>
                            <pre><?= htmlspecialchars($question['text']) ?></pre>
                        <?php endif; ?>
                    </div>
                    <div>Model Answer:</div>
                    <div class="text">
                        <?php if ($question['type'] == 'text') : ?>
                            <?php if ($question['mdlAnsHasMD']) : ?>
                                <?= $parsedown->text($question['modelAnswer']) ?>
                            <?php else : ?>
                                <pre><?= htmlspecialchars($question['modelAnswer']) ?></pre>
                            <?php endif; ?>
                        <?php elseif ($question['type'] == "image") : ?>
                            <img src="<?= $question['modelAnswer'] ?>" />
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="gradeContainer">
                <div class="header">User(s)</div>
                <div class="header">Answer</div>
                <div class="header">Comment</div>
                <div class="header">Points</div>

                <?php for ($i = 0; $i < count($answers); $i++) : ?>
                    <?php $answer = $answers[$i]; ?>
                    <div class="users">
                        <span class="timestamp"><?= substr($answer['created'], 11) ?></span>
                        <a href="../user/<?= $answer['user_id']?>">
                            <?= $answer['knownAs'] . " " . $answer['lastname'] ?><br />
                        </a>
                        <?php $ids = [];
                        $ids[] = $answer['id']; ?>
                        <?php while ($i < count($answers) - 1 && $answers[$i + 1]['text'] == $answer['text']) : ?>
                            <?php $i++;
                            $answer = $answers[$i];
                            $ids[] = $answer['id'] ?>
                            <span class="timestamp"><?= substr($answer['created'], 11) ?></span>
                            <?= $answer['knownAs'] . " " . $answer['lastname'] ?><br />
                        <?php endwhile; ?>
                        <input type="hidden" name="answer_ids" class="answer_ids" value="<?= implode(",", $ids) ?>" />
                    </div>
                    <div class="answer">
                        <?php if ($question['type'] == "text") : ?>
                            <?php if ($answer['hasMarkDown']) : ?>
                                <?= $parsedown->text($answer['text']) ?>
                            <?php else : ?>
                                <pre><?= htmlspecialchars($answer['text']) ?></pre>
                            <?php endif; ?>
                        <?php elseif ($question['type'] == "image") : ?>
                            <img src="<?= $answer['text'] ?>" />
                        <?php endif; ?>
                    </div>

                    <div class="comment">
                        <textarea class="comment" autofocus placeholder="Use **markdown** syntax in your text like:&#10;&#10;```javascript&#10;const code = &quot;highlighted&quot;&semi;&#10;```"><?= $answer['comment'] ?></textarea>

                        <i title="Markdown" class="txt fa-brands fa-markdown <?= $answer['cmntHasMD'] ? "active" : ""?>"></i>
                        <div class="mdContainer <?= $answer['cmntHasMD'] ? "active" : ""?>">
                            <i title="Preview Markdown" class="fa-solid fa-eye previewBtn"></i>
                            <div class="previewArea"></div>
                        </div>
                    </div>
                    <div class="points">
                        <input type="number" value="<?= $answer['points'] ?? '' ?>" step="0.01" max="<?= $question['points'] ?>" min="0" name="points" class="points" />
                    </div>
                <?php endfor; ?>
            </div>

            <div class="done">
                <?php if ($prev_id) : ?>
                    <a href="<?= $prev_id ?>">
                        <i title="Previous Question" class="fa-solid fa-arrow-left"></i>
                    </a>
                <?php endif; ?>
                <?php if ($next_id) : ?>
                    <a href="<?= $next_id ?>">
                        <i title="Next Question" class="fa-solid fa-arrow-right"></i>
                    </a>
                <?php endif; ?>
                <a href="../grade">
                    <i title="Finish Grading" class="fa-solid fa-check"></i>
                </a>
            <footer>
                <i class="fa-solid fa-keyboard"></i> Pressing N or P inside a points field takes you to the next / previous field
            </footer>
            </div>
        </div>
    </main>
</body>

</html>
