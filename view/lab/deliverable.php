<div class="dcontainer deliverables">
    <div class="about">
        <div class="meta">
            <span class="type" data-type="<?= $deliv['type'] ?>">
                <?= $deliv['type'] ?>
            </span>
        </div>
        <div class="points">
            Points: <input type="number" value="<?= $deliv['points'] ?>" /><br />
            of <span class="labPoints"><?= $lab['points'] ?></span>
        </div>
        <i class="far fa-trash-alt delDeliv" data-id="<?= $deliv['id'] ?>"></i>
    </div>
    <div class="deliv" data-id="<?= $deliv['id'] ?>">
        <div>Deliverable description:</div>
        <div class="textContainer">
            <textarea class="text" placeholder="Write your deliverable description here" data-md="Use **markdown** syntax in your text like:&#10;&#10;```javascript&#10;const code = &quot;highlighted&quot;&semi;&#10;```" data-txt="Write your question text here"><?= $deliv['text'] ?></textarea>

            <i title="Markdown" class="txt fa-brands fa-markdown <?= $deliv['hasMarkDown'] ? "active" : "" ?>"></i>
            <div class="mdContainer <?= $deliv['hasMarkDown'] ? "active" : "" ?>">
                <div class="preview"><button class="previewBtn">Preview Markdown</button></div>
                <div class="previewArea"></div>
            </div>
        </div>
    </div>
</div>
