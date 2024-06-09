<?php
    $desc = [];
    $desc['present'] = "File present: ";
    $desc['not_present'] = "File not present: ";
    $desc['txt_wm'] = "Text Watermark: ";
    $desc['png_wm'] = ".png Watermark: ";
?>
<?php if($checks == null): ?>
    <h4>No Zip Checks Yet!</h4>
<?php else : ?>
    <?php foreach($chekcs as $check): ?>
        <div class="zipCheck" data-id="<?= $check['id'] ?>">
            <?= $desc[$check['type']] ?><strong><?= $check['file'] ?></strong> at byte <strong><?= $check['byte'] ?></strong>
            <i data-id="<?= $check['id'] ?>" title="Remove Zip Check" class="remove fa-solid fa-xmark"></i>
        </div>
    <?php endforeach; ?>
<?php endif; ?>