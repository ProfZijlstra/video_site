<div class="delivery" data-id="<?= $delivery['id'] ?>">
    <?php if ($deliverable['type'] == 'txt') { ?>
    <div class="textContainer">
        <?php stats($delivery)  ?>
        <textarea class="txt" placeholder="Write the text for your deliverable here." data-md="Use **markdown** syntax in your text like:&#10;&#10;```javascript&#10;const code = &quot;highlighted&quot;&semi;&#10;```" data-txt="Write the text for your deliverable here."><?= $delivery['text'] ?></textarea>

        <i title="Markdown" class="txt fa-brands fa-markdown <?= $delivery['hasMarkDown'] ? 'active' : '' ?>"></i>
        <div class="mdContainer <?= $delivery['hasMarkDown'] ? 'active' : '' ?>">
            <div class="preview"><button class="previewBtn">Preview Markdown</button></div>
            <div class="previewArea"></div>
        </div>
    </div>
    <?php } elseif ($deliverable['type'] == 'url') { ?>
    <div class="urlContainer">
        <?php stats($delivery)  ?>
        <input type="url" class="url" placeholder="https://github.com/student/project" value="<?= $delivery['text'] ?>" />
    </div>
    <?php } else { /* type is: img, pdf, zip */ ?>
    <div class="fileContainer camContainer" data-id="<?= $deliverable['id']?>">
        <i title="<?= $delivery['text'] ? 'Replace' : 'Upload' ?> <?= $deliverable['type'] ?>" class="upload fa-solid fa-upload"></i>
        <i class="spinner fa-solid fa-circle-notch"></i>
        <?php if ($deliverable['type'] == 'img') { ?>
        <span><i class="fa-solid fa-camera" title="Open Camera"></i></span>
        <?php } ?>

        <?php if ($deliverable['type'] == 'img') { ?>
        <input type="file" class="file fileUpload" accept=".png,.jpg,.gif">
        <?php } elseif ($deliverable['type'] == 'pdf') { ?>
        <input type="file" class="file fileUpload" accept=".pdf">
        <?php } elseif ($deliverable['type'] == 'zip') { ?>
        <input type="file" class="file fileUpload" accept=".zip">
        <?php } ?>

        <a class="fileLink" href="<?= $delivery['file'] ?>" target="_blank"><?= $delivery['name'] ?></a>
        <i title="Delete" class="fa-solid fa-trash-can <?= $delivery['file'] ? '' : 'hide' ?>" data-id="<?= $delivery['id']?>"></i>
        <span class="check"><i class="fa-solid fa-check"></i></span>
        <?php stats($delivery)  ?>

        <?php if ($deliverable['type'] == 'zip' && $checks[$deliverable['id']]) { ?>
        <?php
        $identity = false;
            $presents = [];
            $not_presents = [];
            $sizes = [];
            foreach ($checks[$deliverable['id']] as $check) {
                switch ($check['type']) {
                    case 'present':
                        $presents[] = $check;
                        break;
                    case 'not_present':
                        $not_presents[] = $check;
                        break;
                    case 'txt_wm':
                    case 'png_wm':
                        $identity = true;
                        break;
                    case 'size_lt':
                    case 'size_gt':
                        $sizes[] = $check;
                        break;
                }
            }
            ?>
        <?php if ($identity) { ?>
        <div class="checks identity">
            <h3>Uploader identity check</h3>
            <div class="zipCheck identity" id="c<?= $deliverable['id'] ?>"
                title="Based on slight differences in the code your identity is checked">
                <i class="fa-regular fa-user"></i>
                Code identity check
                <div class="errorMsg">Did you write this code?</div>
            </div>
        </div>
        <?php } ?>
        <?php if ($presents) { ?>
        <div class="checks presents">
            <h3>Should be in zip root:</h3>
            <?php foreach ($presents as $check) { ?>
            <div class="zipCheck present" id="c<?= $check['id']?>"
                title="This file should be present in your zip">
                <i class="fa-solid fa-check"></i>
                <?= $check['file'] ?>
                <div class="errorMsg">Please add</div>
            </div>
            <?php } ?>
        </div>
        <?php } ?>
        <?php if ($not_presents) { ?>
        <div class="checks notPresents">
            <h3>Should not be in zip:</h3>
            <?php foreach ($not_presents as $check) { ?>
            <div class="zipCheck notPresent" id="c<?= $check['id']?>"
                title="Your zip should not contain this file">
                <i class="fa-solid fa-xmark"></i>
                <?= $check['file'] ?>
                <div class="errorMsg">Please remove</div>
            </div>
            <?php } ?>
        </div>
        <?php } ?>
        <?php if ($sizes) { ?>
        <div class="checks sizes">
            <h3>File size checks:</h3>
            <?php foreach ($sizes as $check) { ?>
            <div class="zipCheck size" id="c<?= $check['id']?>"
                title="Your zip should be">
                <i class="fa-regular fa-zipper"></i>
                <?= $check['file'] ?> <?= $check['type'] == 'size_lt' ? 'or less' : 'or more' ?>
            </div>
            <?php } ?>
        <?php } ?>
        <?php } ?>

        <?php if ($deliverable['type'] == 'img') { ?>
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
        <img src="<?= $delivery['file'] ?>" class="answer <?= $delivery['file'] ? 'show' : 'hide' ?>" data-id="<?= $delivery['id'] ?>">
        <?php } elseif ($deliverable['type'] == 'zip') { ?>
        <div class="listing"><?= $delivery['text'] ?></div>
        <?php } ?>
    </div>
    <?php } ?>

    <div class="textContainer commentContainer">
        <textarea class="cmt <?= $deliverable['type'] == 'txt' ? '' : 'file' ?>" placeholder="Write any questions or comments about this deliverable here." data-md="Use **markdown** syntax in your text like:&#10;&#10;```javascript&#10;const code = &quot;highlighted&quot;&semi;&#10;```" data-txt="Write any questions or comments about this deliverable here."><?= $delivery['stuComment'] ?></textarea>

        <i title="Markdown" class="cmt <?= $deliverable['type'] == 'txt' ? 'stu' : 'file' ?> fa-brands fa-markdown <?= $delivery['stuCmntHasMD'] ? 'active' : '' ?>"></i>
        <div class="mdContainer <?= $delivery['stuCmntHasMD'] ? 'active' : '' ?>">
            <div class="preview"><button class="previewBtn">Preview Markdown</button></div>
            <div class="previewArea"></div>
        </div>
    </div>
</div> <!-- close delivery -->
