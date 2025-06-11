<!DOCTYPE html> <?php global $MY_BASE ?>
<html>
    <head>
        <title>Edit Quiz</title>
        <meta charset="utf-8" />
        <meta name=viewport content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/lib/font-awesome-all.min.css">
        <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/common-1.3.css">
        <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/adm-1.0.css">
        <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/lib/prism.css">
        <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/quiz-1.11.css">
        <script src="<?= $MY_BASE ?>/res/js/lib/prism.js"></script>
        <script src="<?= $MY_BASE ?>/res/js/markdown-1.8.js"></script>
        <script src="<?= $MY_BASE ?>/res/js/camera-1.5.js"></script>
        <script src="<?= $MY_BASE ?>/res/js/quiz/edit-1.6.js"></script>
    </head>
    <body id="editQuiz">
        <?php include 'header.php'; ?>
        <main>
            <nav class="back" title="Back">
                <a href="../../quiz">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
            </nav>
            <?php include 'areas.php'; ?>
            <nav class="tools">
                <a title="Preview" href="../preview?q=<?= $quiz['id'] ?>">
                    <i id="previewBtn" class="fa-solid fa-eye"></i>
                </a>
                <form id="delQuiz" data-qcount="<?= $questions ? count($questions) : 0 ?>" action="del" method="POST"><i id="delBtn" title="Delete Quiz" class="far fa-trash-alt"></i></form>
            </nav>
            <div id="content">
                <div class="quiz">
                    <div class="status" data-id="<?= $quiz['id']?>">
                        <label><input id="visible" type="checkbox" class="visible" value="<?= $quiz['visible'] ?>" <?= $quiz['visible'] ? 'checked' : '' ?> /> Visible</label>
                    </div>
                    <form id="updateQuiz" action="<?= '../'.$quiz['id'] ?>" method="POST" data-id="<?= $quiz['id']?>">
                        <div>
                            <label>Day:</label>
                            <select name="day_id" id="day_id">
                                <?php foreach ($days as $day) { ?>
                                <option value="<?= $day['id'] ?>" <?= $day['id'] == $quiz['day_id'] ? 'selected' : '' ?>>
                                    <?= $day['abbr'].' - '.$day['desc'] ?>
                                </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div>
                            <label>Name:</label>
                            <input type="text" name="name" class="name" value="<?= $quiz['name'] ?>" /> 
                        </div>
                        <div>
                            <label>Start:</label>
                            <input type="date" name="startdate" class="startdate" value="<?= substr($quiz['start'], 0, 10) ?>" />
                            <input type="time" name="starttime" class="starttime" value="<?= substr($quiz['start'], 11) ?>" />
                        </div>
                        <div>
                            <label>Stop:</label>
                            <input type="date" name="stopdate" class="stopdate" value="<?= substr($quiz['stop'], 0, 10) ?>" />
                            <input type="time" name="stoptime" class="stoptime" value="<?= substr($quiz['stop'], 11) ?>" />
                        </div>
                    </form>
                </div>


                <h3 class="<?= count($questions) == 0 ? 'empty' : '' ?>">
                    Questions
                    <i id="addQuestion" title="Add Question" class="far fa-plus-square"></i>
                </h3>
                <?php if (! $questions) { ?>
                <div id="noQuestions">
                    <h2>No Questions Yet!</h2>
                    <p class="warning">
                        Click the <i class="far fa-plus-square"></i> button in the top right to add at least one question.
                    </p>
                </div>
                <?php } ?>
                <div id="questions">
                <?php foreach ($questions as $question) { ?>
                    <?php include 'question.php'?>
                <?php } ?>
                </div>
            </div>
        </main>

        <dialog id="addQuestionDialog" class="modal">
            <i id="closeAddDialog" class="fas fa-times-circle close"></i>
            <h3>Add Question</h3>
            <label>Type:</label>
            <select id="questionType">
                <option value="text">Text</option>
                <option value="image">Image</option>
            </select>
            <div class="btn">
                <button id="addQuestBtn" data-seq="<?= count($questions) + 1 ?>"
                    data-quiz_id="<?= $quiz['id'] ?>">Add</button>
            </div>
        </dialog>
    </body>
</html>
