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
        <link rel="stylesheet" href="res/css/quiz-1.3.css">
        <style>
            #content > h3 {
                margin-bottom: 0px;
            }
        </style>
        <script src="res/js/prism.js"></script>
        <script src="res/js/markdown.js"></script>
        <script src="res/js/quiz/gradeUser.js"></script>
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
                                <?php if($question['type'] == 'markdown'): ?>
                                <?= $parsedown->text($question['modelAnswer']) ?>
                                <?php elseif ($question['type'] == "image"): ?>
                                <img src="<?= $question['modelAnswer'] ?>" />
                                <?php endif; ?>
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

                                <?php if($question['type'] == 'markdown'): ?>
                                <?= $parsedown->text($answers[$question['id']]['text']) ?>
                                <?php elseif ($question['type'] == "image"): ?>
                                <img src="<?= $answers[$question['id']]['text'] ?>" />
                                <?php endif; ?>

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
                            <div>
                                <div class="preview"><button class="previewBtn">Preview Markdown</button></div>
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
