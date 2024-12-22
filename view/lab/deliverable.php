<div class="dcontainer deliverables" data-id="<?= $deliv['id'] ?>">
    <div class="about">
        <div class="meta">
            <span class="type" data-type="<?= $deliv['type'] ?>">
                <?= $deliv['type'] ?>
            </span>
        </div>
        <div>
            Points: <input class="points" type="number" value="<?= $deliv['points'] ?>" /><br />
            of <span class="labPoints"><?= $labPoints ?></span>
        </div>
        <?php if ($deliv['type'] == 'zip') { ?>
            <i title="Configure Upload Checks" class="fa-solid fa-gear zipCheckConfig"
            data-id="<?= $deliv['id'] ?>"></i>
        <?php } ?>
        <i title="Remove deliverable" class="far fa-trash-alt delDeliv" data-id="<?= $deliv['id'] ?>"></i>
    </div>
    <div class="deliv">
        <div>Deliverable description:</div>
        <div class="textContainer">
            <textarea class="text desc" placeholder="Write your deliverable description here" data-md="Use **markdown** syntax in your text like:&#10;&#10;```javascript&#10;const code = &quot;highlighted&quot;&semi;&#10;```" data-txt="Write your question text here"><?= $deliv['desc'] ?></textarea>

            <i title="Markdown" class="deliverable fa-brands fa-markdown <?= $deliv['hasMarkDown'] ? 'active' : '' ?>"></i>
            <div class="mdContainer <?= $deliv['hasMarkDown'] ? 'active' : '' ?>">
                <div class="preview"><button class="previewBtn">Preview Markdown</button></div>
                <div class="previewArea"></div>
            </div>

            <div class="attachContainer">
                <h3 class="<?= count($attachments) == 0 ? 'empty' : '' ?>">Attachments
                    <i title="Add attachment" class="fa-solid fa-paperclip attachBtn""></i>
                    <input type="file" name="attachment" class="attachment" 
                        data-deliverable_id="<?= $deliv['id']?>" />
                    <i class="fa-solid fa-circle-notch attachSpin"></i>
                </h3>
                <div class="attachments">
                    <?php foreach ($attachments as $attachment) { ?>
                        <?php if ($attachment['deliverable_id'] == $deliv['id']) { ?> 
                            <?php include 'view/lab/attachment.php'?>
                        <?php } // end if?>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>
