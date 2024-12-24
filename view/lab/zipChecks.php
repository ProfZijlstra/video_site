<?php
$desc = [];
$desc['present'] = 'File present: ';
$desc['not_present'] = 'File not present: ';
$desc['txt_wm'] = 'Text WM: ';
$desc['png_wm'] = '.png WM: ';
?>
<?php if ($checks == null) { ?>
    <h4>No Zip Checks Yet!</h4>
<?php } else { ?>
    <?php foreach ($checks as $check) { ?>
        <div class="zipCheck" data-id="<?= $check['id'] ?>">
            <?php if ($check['block'] == 1) { ?>
                <i title="Publicly announce and report" class="fa-solid fa-bullhorn"></i>
            <?php } ?>
            <?= $desc[$check['type']] ?><strong><?= $check['file'] ?></strong>
            <?php if (str_ends_with($check['type'], 'wm')) { ?>
                at byte <strong><?= $check['byte'] ?></strong>
            <?php } ?>
            <i data-id="<?= $check['id'] ?>" title="Remove Zip Check" class="remove fa-solid fa-xmark"></i>
        </div>
    <?php } ?>
<?php } ?>
