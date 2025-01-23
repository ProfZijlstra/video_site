<article id="a<?= $idx ?>" class="<?= $idx == $file_idx ? 'selected' : '' ?>" 
    data-name="<?= $part ?>">
    <h2><?= $part ?></h2>

    <div class="media <?= $config ? 'hide' : '' ?>">
        <i class="fa-solid video fa-video<?= $has_vid ? ' available hide' : '-slash'?>" 
            title="Switch to video <?= $has_vid ? '' : 'not available'?>"></i>
        <div class="pdf <?= ! $has_vid ? 'hide' : '' ?>">
            <i class="far fa-file-pdf pdf <?= $has_pdf ? 'available ' : '' ?>"
                title="Switch to PDF <?= $has_pdf ? '' : 'not available'?>"
                data-file="<?= $part ?>"></i>
            <?php if (! $has_pdf) { ?>
            <i title="Switch to PDF not available" class="fa-solid fa-slash"></i>
            <?php } ?>
        </div>
    </div>
    <?php if (hasMinAuth('instructor')) { ?>
    <div class="media upload <?= $config ? '' : 'hide' ?>" data-part="<?= "{$idx}_{$part}_on"?>"}>
        <i title="Upload a .mp4 and/or .pdf" class="fa-solid fa-upload available"></i>
    </div>
    <?php } ?>

    <?php if ($has_vid) { ?>
    <video controls controlslist="nodownload" 
        <?php if ($idx == $file_idx) { ?>
        src="<?= "res/course/{$course}/{$block}/lecture/{$day}/{$idx}_{$part}_on/{$vid_info['file']}" ?>" 
        <?php } ?>
        data-src="<?= "res/course/{$course}/{$block}/lecture/{$day}/{$idx}_{$part}_on/{$vid_info['file']}" ?>">
    </video>
    <?php } ?>

    <?php if ($has_pdf) { ?>
    <object class="<?= $has_vid ? 'hide' : ''?>" 
        type="application/pdf" 
        data="<?= "res/course/{$course}/{$block}/lecture/{$day}/{$idx}_{$part}_on/{$pdf_info['file']}" ?>">
        <div class="noVid">
            <i class="fa-solid fa-video" title="Video"></i>
            <div>Your browser doesn't seem to support PDF previews</div>
            <p>
                <a target="_blank" href="<?= "res/course/{$course}/{$block}/lecture/{$day}/{$idx}_{$part}_on/{$pdf_info['file']}" ?>">
                    Click here to download the PDF
                </a>
            </p>
        </div>
    </object>
    <?php } ?>

    <?php if (! $has_vid && ! $has_pdf) { ?>
    <div class="noVid">
        This lesson part <br>
        does not have a Video or PDF file <br>
        (yet).
    </div>
    <?php } ?>

    <?php if ($totalDuration) { ?>
    <div class="progress">
        <?php
    $progClass = 'passed';
        foreach ($parts as $idxx => $content) {
            if ($progClass == 'current') {
                $progClass = 'future';
            }
            if ($idx == $idxx) {
                $progClass = 'current';
            }
            ?>
        <?php if (isset($videos[$idxx])) { ?>
        <?php $vid = $videos[$idxx]; ?>
        <?php $matches = [];
            preg_match("/.*(\d\d):(\d\d):(\d\d)\.(\d\d)\.mp4/", $vid['file'], $matches); ?>
        <div data-vid="<?= $idxx ?>" 
            title="<?= "{$content} ({$matches[2]}:{$matches[3]})"?>"
            class="tab <?= $progClass ?>" 
            style="width: <?= number_format(($vid['duration'] / $totalDuration) * 100, 2) ?>%"></div>
        <?php } ?>
        <?php } // end foreach files?>

        <div class="time">Total time: <?= $totalTime ?></div>
        <div class="autoplay">autoplay <i class="auto_toggle fas fa-toggle-off"></i></div>
        <div title="Keyboard Shortcuts"><i class="fa-solid fa-keyboard shortcuts"></i></div>
    </div>
    <?php } ?>

    <?php if ($firstIdx && $lastIdx) { ?>
    <nav class="mobileNav">
        <div class="prev">
            <?php if ($idx > $first_idx) { ?>
            <a href="<?= $idx <= 10 ? '0'.($idx - 1) : $idx ?>" title="Previous Video"
                data-video="<?= $idx <= 10 ? '0'.($idx - 1) : $idx ?>">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <?php } else { ?>
            <i class="fa-solid fa-arrow-left disabled"></i>
            <?php } ?>
        </div>
        <div class="next">
            <?php if ($idx < $last_idx) { ?>
            <a href="<?= $idx < 9 ? '0'.($idx + 1) : $idx ?>" title="Next Video"
                data-video="<?= $idx < 9 ? '0'.($idx + 1) : $idx ?>">
                <i class="fa-solid fa-arrow-right"></i>
            </a>
            <?php } else { ?>
            <i class="fa-solid fa-arrow-right disabled"></i>
            <?php } ?>
        </div>
    </nav>
    <?php } ?>

    <div class="keyboard hidden">
        <section>
            <h5>Playback</h5>
            <div>
                <span class="key">Space</span>
                <span class="action">Play / Pause</span>
            </div>
            <div>
                <span class="key"><i class="fa-solid fa-arrow-left"></i></span>
                <span class="action">Back 10 secconds</span>
            </div>
            <div>
                <span class="key"><i class="fa-solid fa-arrow-right"></i></span>
                <span class="action">Forward 10 seconds</span>
            </div>
        </section>
        <section>
            <h5>Alternate Playback</h5>
            <div>
                <span class="key">K</span>
                <span class="action">Play / Pause</span>
            </div>
            <div>
                <span class="key">J</span>
                <span class="action">Back 5 Secconds</span>
            </div>
            <div>
                <span class="key">L</span>
                <span class="action">Forward 5 Seconds</span>
            </div>
        </section>
        <section>
            <h5>Content Control</h5>
            <div>
                <span class="key">N</span>
                <span class="action">Next Video</span>
            </div>
            <div>
                <span class="key">P</span>
                <span class="action">Previous Video</span>
            </div>
            <div>
                <span class="key">V</span>
                <span class="action">View PDF / Video</span>
            </div>
        </section>
        <section>
            <h5>Speed</h5>
            <div>
                <span class="key">[</span>
                <span class="action">Decrease Speed</span>
            </div>
            <div>
                <span class="key">]</span>
                <span class="action">Increase Speed</span>
            </div>
            <div>
                <span class="key">0</span>
                <span class="action">Normal Speed</span>
            </div>
        </section>
        <section>
            <h5>Modes</h5>
            <div>
                <span class="key">A</span>
                <span class="action">Toggle Auto Play</span>
            </div>
            <div>
                <span class="key">T</span>
                <span class="action">Toggle Theater Mode</span>
            </div>
            <div>
                <span class="key">F</span>
                <span class="action">Toggle Full Screen</span>
            </div>
        </section>
    </div>

    <?php include 'comments.php'; ?>
</article>
