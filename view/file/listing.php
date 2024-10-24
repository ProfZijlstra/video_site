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
    <div class="file">
        <span class="dir" data-dir="<?= "{$parent}/{$dir}" ?>">
            <i class="fa-regular fa-folder"></i>
            <?= $dir ?>
        </span>
        <span class="ops">
            <?php if (isset($norem) && ! in_array($dir, $norem) && hasMinAuth('instructor')) { ?>
            <i title="Remove Directory" class="fa-solid fa-trash-can"></i>
            <?php } ?>
            <i title="Upload File" class="fa-solid fa-upload"></i>
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
            <a href="<?= "res/{$course}/{$block}/{$parent}/{$file}" ?>">
                <i class="fa-regular <?= $icon ?>"></i>
                <?= $file ?>
            </a>
        </span>
        <span class="ops">
            <?php if (hasMinAuth('instructor')) { ?>
            <i title="Remove File" class="fa-solid fa-trash-can"></i>
            <?php } ?>
            <i title="Copy Link" 
                data-link="<?= "{$proto}{$server}{$MY_BASE}/res/{$course}/{$block}/{$parent}/{$file}"?>" 
                class="fa-solid fa-link"></i>
        </span>
    </div>
    <?php } ?>
</div>
