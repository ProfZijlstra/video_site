<?php
    $desc = [];
    $desc['text'] = "Watermark text file: ";
    $desc['png'] = "Watermark png file: ";
?>
<?php if($actions == null): ?>
    <h4>No Zip Actions Yet!</h4>
<?php else : ?>
    <?php foreach($actions as $action): ?>
        <div class="zipAction" data-id="<?= $action['id'] ?>">
            <?= $desc[$action['type']] ?><strong><?= $action['file'] ?></strong> at byte <strong><?= $action['byte'] ?></strong>
            <i data-id="<?= $zipAction['id'] ?>" title="Remove Zip Action" class="remove fa-solid fa-xmark"></i>
        </div>
    <?php endforeach; ?>
<?php endif; ?>