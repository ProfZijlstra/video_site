<!DOCTYPE html>
<html>
    <head>
        <title>Edit Quiz</title>
        <meta charset="utf-8" />
        <meta name=viewport content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="res/css/lib/font-awesome-all.min.css">
        <link rel="stylesheet" href="res/css/common-1.3.css">
        <link rel="stylesheet" href="res/css/adm.css">
        <link rel="stylesheet" href="res/css/lib/prism.css">
        <link rel="stylesheet" href="res/css/quiz-1.4.css">
        <script src="res/js/lib/prism.js"></script>
        <script src="res/js/markdown-1.7.js"></script>
        <script src="res/js/quiz/edit-1.5.js"></script>
    </head>
    <body id="editQuiz">
        <?php include("header.php"); ?>
        <main>
            <nav class="back" title="Back">
                <a href="../../quiz">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
            </nav>
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
                    <form id="updateQuiz" action="<?= "../" . $quiz['id'] ?>" method="POST" data-id="<?= $quiz['id']?>">
                        <div>
                            <label>Day:</label>
                            <select name="day_id" id="day_id">
                                <?php foreach ($days as $day): ?>
                                <option value="<?= $day['id'] ?>" <?= $day['id'] == $quiz['day_id'] ? "selected" : "" ?>>
                                    <?= $day['abbr'] . " - " . $day['desc'] ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
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


                <h3 class="<?= count($questions) == 0 ? 'empty' : '' ?>">
                    Questions
                    <i id="addQuestion" title="Add Question" class="far fa-plus-square"></i>
                </h3>
                <?php if(!$questions): ?>
                    <h2>No Questions Yet!</h2>
                    <p class="warning">Click the <i class="far fa-plus-square"></i> button in the top right to add at least one question.</p>
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
                            <div class="qType" data-type="<?= $question['type'] ?>">
                                Type: 
                                <?php if($question['type'] == "text"): ?>
                                    Text
                                <?php elseif ($question['type'] == "image"): ?>
                                    Image Upload
                                <?php endif; ?>
                            </div>
                            <div>Question Text:</div> 
                            <div class="textContainer">
                                <textarea class="text" 
                                    placeholder="Write your question text here" 
                                    data-md="Use **markdown** syntax in your text like:&#10;&#10;```javascript&#10;const code = &quot;highlighted&quot;&semi;&#10;```"
                                    data-txt="Write your question text here" ><?= $question['text']?></textarea>

                                <i title="Markdown" class="txt fa-brands fa-markdown <?= $question['hasMarkDown'] ? "active" : ""?>"></i>
                                <div class="mdContainer <?= $question['hasMarkDown'] ? "active" : ""?>">
                                    <div class="preview"><button class="previewBtn">Preview Markdown</button></div>
                                    <div class="previewArea"></div>
                                </div>
                            </div>
                            <div>Model Answer:</div> 
                            <?php if($question['type'] == 'text'): ?>
                                <div class="textContainer">
                                    <textarea class="model_answer" 
                                    placeholder="Write your model answer here"
                                    data-md="Use **markdown** syntax in your text like:&#10;&#10;```javascript&#10;const code = &quot;highlighted&quot;&semi;&#10;```"
                                    data-txt="Write your model answer here"><?= $question['modelAnswer']?></textarea>

                                    <i title="Markdown" class="mdl fa-brands fa-markdown <?= $question['mdlAnsHasMD'] ? "active" : ""?>"></i>
                                    <div class="mdContainer <?= $question['mdlAnsHasMD'] ? "active" : ""?>">
                                        <div class="preview"><button class="previewBtn">Preview Markdown</button></div>
                                        <div class="previewArea"></div>
                                    </div>
                                </div>
                            <?php elseif ($question['type'] == "image"): ?>
                                <img src="<?= $question['modelAnswer'] ?>" />
                                <div>
                                    Upload Replacement: 
                                    <input type="file" class="img_replace" />
                                    <i class="fa-solid fa-circle-notch"></i>
                                </div>                            
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
        <div id="overlay">
            <i id="close-overlay" class="fas fa-times-circle"></i>
            <div id="add_question_modal" class="modal hide">
                <h3>Add Question</h3>
                <div id="typeSelect">
                    Type:
                    <select>
                        <option value="text">Text</option>
                        <option value="image">Image</option>
                    </select>
                </div>
                <form action="question" method="post" id="add_form" enctype="multipart/form-data">
                    <input type="hidden" name="quiz_id" value="<?= $quiz['id'] ?>" id="quiz_id"/>
                    <input type="hidden" name="type" value="text" />
                    <input type="hidden" name="seq" value="<?= $questions ? count($questions) + 1 : 1?>" />
                    Points: <input type="number" name="points" value="1" />
                    <div>Question Text:</div>
                    <textarea id="addQuestionText" name="text" placeholder="Use **markdown** syntax in your text like:&#10;&#10;```javascript&#10;const code = &quot;highlighted&quot;&semi;&#10;```"></textarea>
                    <div>Model Answer:</div> 
                    <textarea id="md_answer" name="model_answer" 
                        data-ph="Use **markdown** syntax in your text like:&#10;&#10;```javascript&#10;const code = &quot;highlighted&quot;&semi;&#10;```"
                        placeholder="Use **markdown** syntax in your text like:&#10;&#10;```javascript&#10;const code = &quot;highlighted&quot;&semi;&#10;```"></textarea>
                    <input id="img_answer" type="file" name="image" />
                    <div class="btn"><button>Add Question</button></div>
                </form>
            </div>
            <div id="camera_modal" class="modal hide">
                <!-- TODO add open camera functionality (see git issue #14) -->
                <div id="video_view">
                    <video id="video"></video>
                    <button>Take Photo</button>
                </div>
                <div id="photo_view" class="hide">
                    <canvas id="photo"></canvas>
                    <button>Retake</button>
                    <button>Upload</button>
                </div>
            <div>
        </div>
    </body>
</html>
