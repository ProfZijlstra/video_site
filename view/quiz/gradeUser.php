<!DOCTYPE html>
<html>
    <head>
        <title>Grade Quiz by Student</title>
        <meta charset="utf-8" />
        <meta name=viewport content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="res/css/font-awesome-all.min.css">
        <link rel="stylesheet" href="res/css/common-1.1.css">
        <link rel="stylesheet" href="res/css/adm.css">
        <link rel="stylesheet" href="res/css/prism.css">
        <link rel="stylesheet" href="res/css/quiz.css">
        <style>
            #content > h3 {
                margin-bottom: 0px;
            }
        </style>
        <script src="res/js/prism.js"></script>
        <script src="res/js/quiz/markdown.js"></script>
        <script>
window.addEventListener("load", () => {   
    // focus first comment area to get started
    document.querySelector('textarea.comment').focus();

    // hookup markdown previews
    MARKDOWN.enablePreview("../../markdown");

    // hookup comment and point submission 
    const user_id = document.getElementById('user').dataset.user_id;
    function saveGrading() {
        const qc = this.parentNode.parentNode;
        const commentArea = qc.querySelector('textarea.comment');
        const comment = encodeURIComponent(commentArea.value);
        const points = qc.querySelector('input.points').value;
        const question_id = qc.querySelector('div.question').dataset.id
        const answer_id = commentArea.dataset.id;

        fetch(`grade`, {
            method : "POST",
            body : `comment=${comment}&points=${points}&answer_id=${answer_id}&question_id=${question_id}&user_id=${user_id}`,
            headers :
                {'Content-Type' : 'application/x-www-form-urlencoded'},
        })
        .then((response) => response.json())
        .then((data) => {
            commentArea.dataset.id = data.answer_id;
        });

    }
    const areas = document.querySelectorAll('div.qcontainer textarea.comment');
    for (const area of areas) {
        area.onchange = saveGrading;
    }
    const inputs = document.querySelectorAll('div.qcontainer input.points');
    for (const input of inputs) {
        input.onchange = saveGrading;
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
            <div id="content">
                <!-- Student Being Graded -->
                <h3 id="user" data-user_id="<?= $user['id'] ?>">
                    <?= $user['studentID'] ?> <?= $user['knownAs']?> <?= $user['lastname'] ?>
                </h3>
                <?php foreach($events as $event): ?>
                    <div><span class="timestamp"><?= $event['timestamp'] ?> <?= $event['type']?></span></div>
                <?php endforeach; ?>

                <!-- For erach question show -->
                <?php $tabindex = 1; ?>
                <?php foreach($questions as $question): ?>
                    <div class="qcontainer">
                        <div class="about">
                            <div class="seq"><?= $question['seq'] ?></div>
                            Points Possible: <?= $question['points'] ?> <br />
                            <input    class="points"
                                type="number" 
                                value="<?= $answers[$question['id']]['points'] ? $answers[$question['id']]['points'] : 0 ?>" 
                                step="0.01" 
                                max="<?= $question['points'] ?>"
                                name="points" 
                                class="points" 
                                tabindex="<?= $tabindex + 1?>"/>
                        </div>
                        <div class="question" data-id="<?= $question['id']?>">
                            <div>Question Text:</div> 
                            <div class="questionText">
                                <?= $parsedown->text($question['text']) ?>
                            </div>
                            <?php if($question['modelAnswer']): ?>
                            <div>Model Answer:</div> 
                            <div class="answerText">
                                <?= $parsedown->text($question['modelAnswer']) ?>
                            </div>
                            <?php endif; ?>

                            <?php if($answers[$question['id']] && $answers[$question['id']]['text']): ?>

                            <div>
                                <strong>Student Answer:</strong>
                                <span class="timestamp"><?= $answers[$question['id']]['created'] ?></span>
                                <?php if($answers[$question['id']]['updated']): ?>
                                    <span class="timestamp">&nbsp;&nbsp;&nbsp;&nbsp;updated: <?= $answers[$question['id']]['updated'] ?></span>
                                <?php endif; ?>

                            </div> 
                            <div class="answerText">
                                <?= $parsedown->text($answers[$question['id']]['text']) ?>
                            </div>
                            
                            <?php else: ?>

                            <h3>Not Answered</h3>

                            <?php endif; ?>

                            <div>Grading Comment:</div> 
                            <textarea class="comment" 
                                tabindex="<?= $tabindex ?>"
                                data-id="<?= $answers[$question['id']]['id'] ?>" 
                                placeholder="Use **markdown** syntax in your text like:&#10;&#10;```javascript&#10;const code = &quot;highlighted&quot;&semi;&#10;```"
                                ><?= $answers[$question['id']]['comment']?></textarea>
                            <div class="preview">
                                <div class="previewBtn"><button>Preview Markdown</button></div>
                                <div class="previewArea"></div>
                            </div>
                        </div>
                    </div>
                    <?php $tabindex += 2; ?>
                <?php endforeach; ?>

            </div>
        </main>
    </body>
</html>
