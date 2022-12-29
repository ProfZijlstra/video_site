<!DOCTYPE html>
<html>
    <head>
        <title>Grade Question</title>
        <meta charset="utf-8" />
        <meta name=viewport content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="res/css/font-awesome-all.min.css">
        <link rel="stylesheet" href="res/css/common-1.1.css">
        <link rel="stylesheet" href="res/css/adm.css">
        <link rel="stylesheet" href="res/css/prism.css">
        <link rel="stylesheet" href="res/css/quiz.css">
        <script src="res/js/prism.js"></script>
        <style>
            div#content {
                width: 90%;
            }
            div.question div.text {
                border: 1px solid grey;
                padding: 5px;
                background-color: #FAFAFA;
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
            }
        </style>
        <script>
window.addEventListener("load", () => {    
    // automatically save changes to comments or points
    function saveGrading() {
        const tr = this.parentNode.parentNode;
        const comment = encodeURIComponent(tr.querySelector('textarea.comment').value);
        const points = tr.querySelector('input.points').value;
        const answer_ids = tr.querySelector('input.answer_ids').value;

        fetch(`grade`, {
            method : "POST",
            body : `comment=${comment}&points=${points}&answer_ids=${answer_ids}`,
            headers :
                {'Content-Type' : 'application/x-www-form-urlencoded'},
        });
    }
    const areas = document.querySelectorAll('table textarea.comment');
    for (const area of areas) {
        area.onchange = saveGrading;
    }
    const inputs = document.querySelectorAll('table input.points');
    for (const input of inputs) {
        input.onchange = saveGrading;
    }

    // start focus on first comment textarea
    const start = document.querySelector('textarea.comment');
    if (start) {
        start.focus();
    } else {
        document.getElementById('finish').focus();
    }
});            
        </script>
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
                <?php if ($prev_id): ?>
                <a href="<?= $prev_id ?>"><i title="Previous Question" class="fa-solid fa-arrow-left"></i></a>
                <?php endif; ?>
                <?php if ($next_id): ?>
                <a href="<?= $next_id ?>"><i title="Next Question" class="fa-solid fa-arrow-right"></i></a>
                <?php endif; ?>
            </nav>
            <div id="content">
                <div class="qcontainer">
                    <div class="about">
                        <div class="seq"><?= $question['seq'] ?></div>
                        <div class="points">Points: <?= $question['points'] ?></div>
                    </div>
                    <div class="question" data-id="<?= $question['id']?>">
                        <div>Question Text:</div> 
                        <div class="text">
                            <?= $parsedown->text($question['text']) ?>
                        </div>
                        <div>Model Answer:</div> 
                        <div class="text">
                            <?= $parsedown->text($question['modelAnswer']) ?>
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
                <?php for ($i = 0; $i < count($answers); $i++): ?>
                    <?php $answer = $answers[$i]; ?>
                    <tr>
                        <td class="users">
                            <span class="timestamp"><?= substr($answer['created'],11) ?></span>
                            <?= $answer['knownAs'] . " " . $answer['lastname'] ?><br />
                            <?php $ids = []; $ids[] = $answer['id']; ?>
                            <?php while($i < count($answers) -1 && $answers[$i + 1]['text'] == $answer['text']): ?>
                                <?php $i++; $answer = $answers[$i]; $ids[] = $answer['id'] ?>
                                <span class="timestamp"><?= substr($answer['created'],11) ?></span>
                                <?= $answer['knownAs'] . " " . $answer['lastname'] ?><br />
                            <?php endwhile; ?>
                            <input type="hidden" name="answer_ids" class="answer_ids" value="<?= implode(",", $ids) ?>" />
                        </td>
                        <td class="answer">
                            <?= $parsedown->text($answer['text']) ?>
                        </td>
                            
                        <td class="comment">
                            <textarea class="comment" placeholder="Use **markdown** syntax in your text like:&#10;&#10;```javascript&#10;const code = &quot;highlighted&quot;&semi;&#10;```"><?= $answer['comment'] ?></textarea>
                        </td>
                        <td class="points">
                            <input type="number" value="<?= $answer['points'] ? $answer['points'] : 0 ?>" step="0.01" max="<?= $question['points'] ?>" name="points" class="points" />
                        </td>
                    </tr>
                <?php endfor; ?>
                </table>

                <div class="done">
                    <?php if ($next_id): ?>
                    <a href="<?= $next_id ?>">
                        <button>Next Question</button>
                    </a>
                    <?php endif; ?>

                    <?php if ($prev_id): ?>
                        <a href="<?= $prev_id ?>">
                            <button>Previous Question</button>
                        </a>
                    <?php endif; ?>


                    <a href="../grade">
                        <button id="finish">Finish Grading</button>
                    </a>
                </div>
            </div>
        </main>
    </body>
</html>
