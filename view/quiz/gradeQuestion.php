<!DOCTYPE html>
<html>

<head>
    <title>Grade Question</title>
    <meta charset="utf-8" />
    <meta name=viewport content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="res/css/lib/font-awesome-all.min.css">
    <link rel="stylesheet" href="res/css/common-1.2.css">
    <link rel="stylesheet" href="res/css/adm.css">
    <link rel="stylesheet" href="res/css/lib/prism.css">
    <link rel="stylesheet" href="res/css/quiz-1.4.css">
    <script src="res/js/lib/prism.js"></script>
    <script src="res/js/markdown-1.1.js"></script>
    <script src="res/js/quiz/gradeQuestion-1.0.js"></script>
    <script src="res/js/ensureSaved.js"></script>
    <style>
        div#content {
            width: 90%;
        }

        div.question div.text {
            border: 1px solid grey;
            padding: 5px;
            background-color: #FAFAFA;
        }

        div.question div.text img {
            max-width: 800px;
        }

        div.qcontainer {
            margin-bottom: 30px;
        }

        div#content div.done {
            margin-top: 30px;
            text-align: right;
        }

        td {
            padding: 3px;
        }

        td.comment {
            width: 30%;
            vertical-align: top;
        }

        td.comment textarea.comment {
            width: 95%;
            min-height: 50px;
        }

        td.points {
            width: 50px;
            vertical-align: top;
        }

        td.points input.points {
            width: 50px;
        }

        td.users {
            width: 300px;
            vertical-align: top;
        }

        td.answer img {
            width: 100%;
        }

        div.done {
            font-size: 24px;
        }
    </style>
</head>

<body>
    <?php include("header.php"); ?>
    <main>
        <nav class="back" title="Back">
            <a href="../grade">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
        </nav>

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
                            <pre><?= $question['text'] ?></pre>
                        <?php endif; ?>
                    </div>
                    <div>Model Answer:</div>
                    <div class="text">
                        <?php if ($question['type'] == 'text') : ?>
                            <?php if ($question['mdlAnsHasMD']) : ?>
                                <?= $parsedown->text($question['modelAnswer']) ?>
                            <?php else : ?>
                                <pre><?= $question['modelAnswer'] ?></pre>
                            <?php endif; ?>
                        <?php elseif ($question['type'] == "image") : ?>
                            <img src="<?= $question['modelAnswer'] ?>" />
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <table>
                <tr>
                    <th>User(s)</th>
                    <th>Answer</th>
                    <th>Comment</th>
                    <th>Points</th>
                </tr>
                <?php for ($i = 0; $i < count($answers); $i++) : ?>
                    <?php $answer = $answers[$i]; ?>
                    <tr>
                        <td class="users">
                            <span class="timestamp"><?= substr($answer['created'], 11) ?></span>
                            <?= $answer['knownAs'] . " " . $answer['lastname'] ?><br />
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
                        </td>
                        <td class="answer">
                            <?php if ($question['type'] == "text") : ?>
                                <?php if ($answer['hasMarkDown']) : ?>
                                    <?= $parsedown->text($answer['text']) ?>
                                <?php else : ?>
                                    <pre><?= htmlspecialchars($answer['text']) ?></pre>
                                <?php endif; ?>
                            <?php elseif ($question['type'] == "image") : ?>
                                <img src="<?= $answer['text'] ?>" />
                            <?php endif; ?>
                        </td>

                        <td class="comment">
                            <textarea class="comment" placeholder="Use **markdown** syntax in your text like:&#10;&#10;```javascript&#10;const code = &quot;highlighted&quot;&semi;&#10;```"><?= $answer['comment'] ?></textarea>
                        </td>
                        <td class="points">
                            <input type="number" value="<?= $answer['points'] ?? '' ?>" step="0.01" max="<?= $question['points'] ?>" name="points" class="points" />
                        </td>
                    </tr>
                <?php endfor; ?>
            </table>

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
            </div>
        </div>
    </main>
</body>

</html>
