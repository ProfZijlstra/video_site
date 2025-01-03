<div id="<?= $idx ?>"
    class="video_link <?= $idx == $file_idx ? 'selected' : '' ?> <?= $config ? 'config' : '' ?>"
    data-seq="<?= $idx ?>"
    data-show="<?= $idx ?>">
    <a href="<?= $idx ?>">
        <i title="Drag to reorder" class="fa-solid fa-grip grip"></i>
        <?= $part ?>
    </a>
    <?php if (hasMinAuth('instructor')) { ?>
    <span class="config" data-file="<?= "{$idx}_{$part}_on" ?>">
        <i title="Edit title" class="fa-regular fa-pen-to-square" ></i>
        <i title="Delete lesson part" class="fa-regular fa-trash-can"></i>
    </span>
    <?php } ?>
</div>
