<?php
global $MY_BASE;
$icons = [
    '.pdf' => 'fa-file-pdf',
    '.zip' => 'fa-file-zipper',
    '.png' => 'fa-file-image',
    '.jpg' => 'fa-file-image',
    'webp' => 'fa-file-image',
    '.mp4' => 'fa-file-video',
    'webm' => 'fa-file-video',
];
$server = $_SERVER['SERVER_NAME'];
$proto = $_SERVER['https'] ? 'https://' : 'http://';
?>
<div class="listing">
    <?php foreach ($dirs as $dir) { ?>
    <div class="dir" data-dir="<?= $dir ?>">
        <span class="dir" data-dir="<?= "{$parent}/{$dir}" ?>">
            <i class="fa-regular fa-folder"></i>
            <?= $dir ?><i class="spinner fa-solid fa-circle-notch"></i>
        </span>
        <span class="ops">
            <i title="Refresh" class="fa-solid fa-arrows-rotate refresh hide"></i>
            <?php if (hasMinAuth('instructor')) { ?>
            <?php if ((isset($norem) && ! in_array($dir, $norem) || ! isset($norem))) { ?>
            <i title="Rename" class="fa-regular fa-pen-to-square rename"
                data-loc="<?= "{$parent}/{$dir}" ?>"></i>
            <i title="Remove Directory" class="fa-solid fa-trash-can remDir" 
                data-loc="<?= "{$parent}/{$dir}" ?>"></i>
            <?php } ?>
            <i title="Upload File" class="fa-solid fa-upload upload" data-loc="<?= "{$parent}/{$dir}" ?>"></i>
            <?php } ?>
        </span>
    </div>
    <?php } ?>
    <?php foreach ($files as $file) {
        $icon = 'fa-file';
        $ext = strtolower(substr($file, -4));
        if (array_key_exists($ext, $icons)) {
            $icon = $icons[$ext];
        }
        ?>
    <div class="file">
        <span class="file">
            <a target="_blank" href="<?= "res/{$course}/{$block}/{$parent}/{$file}" ?>">
                <i class="fa-regular <?= $icon ?>"></i>
                <?= $file ?>
            </a>
        </span>
        <span class="ops">
            <?php if (hasMinAuth('instructor')) { ?>
            <i title="Rename" class="fa-regular fa-pen-to-square rename"
                data-loc="<?= "{$parent}/{$file}" ?>"></i>
            <i title="Remove File" class="fa-solid fa-trash-can remFile" 
                data-loc="<?= "{$parent}/{$file}" ?>"></i>
            <?php } ?>
            <i title="Copy Link" 
                data-link="<?= "{$proto}{$server}{$MY_BASE}/res/{$course}/{$block}/{$parent}/{$file}"?>" 
                class="fa-solid fa-link"></i>
        </span>
    </div>
    <?php } ?>
</div>
