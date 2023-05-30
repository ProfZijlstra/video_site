<!DOCTYPE html>
<html>
    <head>
        <title>Quiz Results</title>
        <meta charset="utf-8" />
        <meta name=viewport content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="res/css/lib/font-awesome-all.min.css">
        <link rel="stylesheet" href="res/css/common-1.1.css">
        <link rel="stylesheet" href="res/css/adm.css">
        <link rel="stylesheet" href="res/css/lib/prism.css">
        <link rel="stylesheet" href="res/css/quiz-1.3.css">
        <script src="res/js/lib/prism.js"></script>
    </head>
    <body>
        <?php include("header.php"); ?>
        <main>
            <nav class="back" title="Back">
                <a href="../quiz">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
            </nav>
            <div id="content">
                <!-- For erach question show -->
                <?php foreach($questions as $question): ?>
                    <div class="qcontainer">
                        <div class="about">
                            <div class="seq"><?= $question['seq'] ?></div>
                            <div class="points">
                                <?php if($answers[$question['id']] && $answers[$question['id']]['points']): ?>
                                Points Received: <br/>    
                                <strong><?= $answers[$question['id']]['points']?></strong>
                                of <?= $question['points'] ?>
                                <?php else: ?>
                                Points Possible: <?= $question['points'] ?>
                                <h3>Not Graded</h3>
                                <?php endif; ?>
                            </div>
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
                                <strong>Your Answer:</strong>                                 
                                <?php if($answers[$question['id']]['updated']): ?>
                                    <span class="timestamp"><?= $answers[$question['id']]['updated'] ?></span>
                                <?php else: ?>
                                    <span class="timestamp"><?= $answers[$question['id']]['created'] ?></span>
                                <?php endif; ?>
                            </div> 
                            <div class="answerText">
                                <?= $parsedown->text($answers[$question['id']]['text']) ?>
                            </div>

                            <?php else: ?>
                            <h3>Not Answered</h3>
                            <?php endif; ?>

                            <?php if ($answers[$question['id']] && $answers[$question['id']]['comment']): ?>
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
                    <strong>Total Score:</strong> <?= $received ?> out of <?= $possible ?>
                </div>


            </div>
        </main>
    </body>
</html>
