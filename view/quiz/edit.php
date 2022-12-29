<!DOCTYPE html>
<html>
    <head>
        <title>Edit Quiz</title>
        <meta charset="utf-8" />
        <meta name=viewport content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="res/css/font-awesome-all.min.css">
        <link rel="stylesheet" href="res/css/common-1.1.css">
        <link rel="stylesheet" href="res/css/adm.css">
        <link rel="stylesheet" href="res/css/prism.css">
        <link rel="stylesheet" href="res/css/quiz.css">
        <script src="res/js/prism.js"></script>
        <script src="res/js/quiz/markdown.js"></script>
        <script src="res/js/quiz/edit.js"></script>
    </head>
    <body>
        <?php include("header.php"); ?>
        <main>
            <nav class="back" title="Back">
                <a href="../../quiz">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
            </nav>
            <nav class="tools">
                <i id="addQuestion" title="Add Question" class="far fa-plus-square"></i>
                <form id="delQuiz" data-qcount="<?= $questions ? count($questions) : 0 ?>" action="del" method="POST"><i id="delBtn" class="far fa-trash-alt"></i></form>
            </nav>
            <div id="content">
                <div class="quiz">
                    <div class="status" data-id="<?= $quiz['id']?>">
                        <label><input id="visible" type="checkbox" class="visible" value="<?= $quiz['visible'] ?>" <?= $quiz['visible'] ? 'checked' : '' ?> /> Visible</label>
                    </div>
                    <form id="updateQuiz" action="<?= "../" . $quiz['id'] ?>" method="POST" data-id="<?= $quiz['id']?>">
                        <div>
                            <label>Name:</label>
                            <input type="text" name="name" class="name" value="<?= $quiz['name'] ?>" /> 
                        </div>
                        <div>
                            <label>Start:</label>
                            <input type="date" name="startdate" class="startdate" value="<?= substr($quiz['start'],0,10) ?>" /> 
                            <input type="time" name="starttime" class="starttime" value="<?= substr($quiz['start'],11) ?>" /> 
                        </div>
                        <div>
                            <label>Stop:</label>
                            <input type="date" name="stopdate" class="stopdate" value="<?= substr($quiz['stop'],0,10) ?>" /> 
                            <input type="time" name="stoptime" class="stoptime" value="<?= substr($quiz['stop'],11) ?>" /> 
                        </div>
                    </form>
                </div>

                <?php if(!$questions): ?>
                    <h2>No Questions Yet</h2>
                <?php endif; ?>
                <?php foreach ($questions as $question): ?>
                    <div class="qcontainer">
                        <div class="about">
                            <div class="seq"><?= $question['seq'] ?></div>
                            <div class="points">Points: <input type="number" value="<?= $question['points'] ?>" /></div>
                            <form action="<?= "question/" . $question['id'] . "/del" ?>" method="POST">
                                <i class="far fa-trash-alt"></i>
                            </form>
                        </div>
                        <div class="question" data-id="<?= $question['id']?>">
                            <div>Question Text:</div> 
                            <textarea class="text" placeholder="Use **markdown** syntax in your text like:&#10;&#10;```javascript&#10;const code = &quot;highlighted&quot;&semi;&#10;```"><?= $question['text']?></textarea>
                            <div class="preview">
                                <div class="previewBtn"><button>Preview Markdown</button></div>
                                <div class="previewArea"></div>
                            </div>
                            <div>Model Answer:</div> 
                            <textarea class="model_answer" placeholder="Use **markdown** syntax in your text like:&#10;&#10;```javascript&#10;const code = &quot;highlighted&quot;&semi;&#10;```"><?= $question['modelAnswer']?></textarea>
                            <div class="preview">
                                <div class="previewBtn"><button>Preview Markdown</button></div>
                                <div class="previewArea"></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
        <div id="overlay">
            <i id="close-overlay" class="fas fa-times-circle"></i>
            <div id="add_question_modal" class="modal hide">
                <h3>Add Question</h3>
                <form action="question" method="post" id="add_form">
                    <input type="hidden" name="quiz_id" value="<?= $quiz['id'] ?>" id="quiz_id"/>
                    <input type="hidden" name="type" value="markdown" />
                    <input type="hidden" name="seq" value="<?= $questions ? count($questions) + 1 : 1?>" />
                    Points: <input type="number" name="points" value="1" />
                    <div>Question Text:</div>
                    <textarea id="addQuestionText" name="text" placeholder="Use **markdown** syntax in your text like:&#10;&#10;```javascript&#10;const code = &quot;highlighted&quot;&semi;&#10;```"></textarea>
                    <div>Model Answer:</div> 
                    <textarea name="model_answer" placeholder="Use **markdown** syntax in your text like:&#10;&#10;```javascript&#10;const code = &quot;highlighted&quot;&semi;&#10;```"></textarea>
                    <div class="btn"><button>Add Question</button></div>
                </form>
            </div>
        </div>
    </body>
</html>
