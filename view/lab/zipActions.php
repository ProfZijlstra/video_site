<?php
$desc = [];
$desc['text'] = 'WM text file: ';
$desc['png'] = 'WM png file: ';
?>
<?php if ($actions == null) { ?>
    <h4>No Zip Actions Yet!</h4>
<?php } else { ?>
    <?php foreach ($actions as $action) { ?>
        <div class="zipAction" data-id="<?= $action['id'] ?>">
            <?= $desc[$action['type']] ?><strong><?= $action['file'] ?></strong> at byte <strong><?= $action['byte'] ?></strong>
            <i data-id="<?= $action['id'] ?>" title="Remove Zip Action" class="remove fa-solid fa-xmark"></i>
        </div>
    <?php } ?>
<?php } ?>
