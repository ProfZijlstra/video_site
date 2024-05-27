<div class="attachment" data-type="<?= $attachment['type'] ?>" data-aid="<?= $attachment['id'] ?>">
    <a href="<?= $attachment['file'] ?>" target="_blank"><?= $attachment['name'] ?></a>
    <?php if ($attachment['type'] == 'zip') : ?>
        <i class="fa-solid fa-gear zipActionConfig"></i>
    <?php endif; ?>
    <i data-id="<?= $attachment['id'] ?>" title="Remove Attachment" class="remove fa-solid fa-xmark"></i>
</div>
