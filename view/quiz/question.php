
<div class="qcontainer">
    <div class="about">
        <div class="qType" data-type="<?= $question['type'] ?>">
            <?php if ($question['type'] == 'text') { ?>
            txt
            <?php } elseif ($question['type'] == 'image') { ?>
            img
            <?php } ?>
        </div>

        <div class="points">Points: <input type="number" value="<?= $question['points'] ?>" /></div>
        <form action="<?= 'question/'.$question['id'].'/del' ?>" method="POST">
            <i class="far fa-trash-alt"></i>
        </form>
    </div>
    <div class="question" data-id="<?= $question['id']?>">
        <div>Question Text:</div> 
        <div class="textContainer">
            <textarea class="text" 
                placeholder="Write your question text here" 
                data-md="Use **markdown** syntax in your text like:&#10;&#10;```javascript&#10;const code = &quot;highlighted&quot;&semi;&#10;```"
                data-txt="Write your question text here" ><?= $question['text']?></textarea>

            <i title="Markdown" class="txt fa-brands fa-markdown <?= $question['hasMarkDown'] ? 'active' : ''?>"></i>
            <div class="mdContainer <?= $question['hasMarkDown'] ? 'active' : ''?>">
                <div class="preview"><button class="previewBtn">Preview Markdown</button></div>
                <div class="previewArea"></div>
            </div>
        </div>
    </div>
    <div class="answer camContainer" data-id="<?= $question['id'] ?>">
        <div>Model Answer:</div> 
        <?php if ($question['type'] == 'text') { ?>
        <div class="textContainer">
            <textarea class="model_answer" 
                placeholder="Write your model answer here"
                data-md="Use **markdown** syntax in your text like:&#10;&#10;```javascript&#10;const code = &quot;highlighted&quot;&semi;&#10;```"
                data-txt="Write your model answer here"><?= $question['modelAnswer']?></textarea>

            <i title="Markdown" class="mdl fa-brands fa-markdown <?= $question['mdlAnsHasMD'] ? 'active' : ''?>"></i>
            <div class="mdContainer <?= $question['mdlAnsHasMD'] ? 'active' : ''?>">
                <div class="preview"><button class="previewBtn">Preview Markdown</button></div>
                <div class="previewArea"></div>
            </div>
        </div>
        <?php } elseif ($question['type'] == 'image') { ?>
        <div class="camera">
            <video></video>
            <div title="Close Camera" class="closeCamera hide">
                <i class="fa-solid fa-xmark"></i>
            </div>
            <div title="[Space] Take picture" class="takePicture hide"></div>
            <div title="Switch Camera" class="switchCamera hide">
                <i class="fas fa-sync"></i>
                <div class="camera_icon">
                    <i class="fas fa-camera"></i>
                </div>
            </div>
            <canvas></canvas>
        </div>
        <img src="<?= $question['modelAnswer'] ?>" class="answer">
        <div>
            <i title="Upload image" class="upload fa-solid fa-upload"></i>
            <span><input type="file" class="img_replace" /></span>
            <i class="fa-solid fa-circle-notch"></i>
            <span><i title="Open Camera" class="fa-solid fa-camera"></i></span>
        </div>                            
        <?php } ?>
    </div>
</div>
