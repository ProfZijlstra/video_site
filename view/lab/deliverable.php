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
        <i title="Remove deliverable" class="far fa-trash-alt delDeliv" data-id="<?= $deliv['id'] ?>"></i>
    </div>
    <div class="deliv">
        <div>Deliverable description:</div>
        <div class="textContainer">
            <textarea class="text desc" placeholder="Write your deliverable description here" data-md="Use **markdown** syntax in your text like:&#10;&#10;```javascript&#10;const code = &quot;highlighted&quot;&semi;&#10;```" data-txt="Write your question text here"><?= $deliv['desc'] ?></textarea>

            <i title="Markdown" class="deliverable fa-brands fa-markdown <?= $deliv['hasMarkDown'] ? "active" : "" ?>"></i>
            <div class="mdContainer <?= $deliv['hasMarkDown'] ? "active" : "" ?>">
                <div class="preview"><button class="previewBtn">Preview Markdown</button></div>
                <div class="previewArea"></div>
            </div>
        </div>
        <?php if ($deliv['type'] == "zip") : ?>
            <div class="delivSrc" title="This deliverable will be based on the following zip attachment">
                <label>Based on:</label>
                <select class="zipAttachment">
                    <option value="">None</option>
                    <?php foreach ($labzips as $labzip) : ?>
                        <option value="<?= $labzip['id'] ?>" <?= $labzip['id'] == $deliv['zipAttachment_id'] ? "selected" : "" ?>><?= $labzip['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>
    </div>
</div>
