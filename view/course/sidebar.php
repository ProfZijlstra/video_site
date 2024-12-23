<nav id="videos" class="<?= $theater ?>">
    <nav>
        <?php if (hasMinAuth('instructor')) { ?>
        <?php if (! $isRemembered) { ?>
        <i title="View Info" id="info-btn" class="fas fa-info-circle"></i>
        <i title="Configure Videos" id="config-btn" class="fa-solid fa-gear"></i>
        <?php } else { ?>
        <a href="reAuth">
            <i title="View Info" id="info-btn" class="fas fa-info-circle"></i>
        </a>
        <a href="reAuth">
            <i title="Configure Videos" id="config-btn" class="fa-solid fa-gear"></i>
        </a>
        <?php } // end is not remembered?>
        <?php } // end is instructor?>
        <table id="days">
            <?php for ($w = 1; $w <= $offering['lessonParts']; $w++) { ?>
            <tr>
                <?php for ($d = 1; $d <= $offering['lessonsPerPart']; $d++) { ?>
                <td class="<?= $offering['showDates'] && $w < $curr_w || ($w == $curr_w && $d <= $curr_d) ? 'done' : '' ?>
                    <?= $offering['showDates'] && $w == $page_w && $d == $page_d ? 'curr' : '' ?>">
                    <a href="../W<?= $w ?>D<?= $d ?>/">&nbsp;</a>
                </td>
                <?php } // td loop?>
            </tr>
            <?php } // tr loop?>
        </table>
    </nav>
    <div id="tabs">
        <?php
        $file_count = 0;
$first_idx = -1;
?>
        <?php foreach ($files as $idx => $pdf_vid) {
            if ($first_idx == -1) {
                $first_idx = $idx;
            }
            if (isset($pdf_vid['vid'])) {
                $info = $pdf_vid['vid'];
            } else {
                $info = $pdf_vid['pdf'];
            }
            ?>

        <div class='video_link <?= $idx == $file_idx ? 'selected' : '' ?>' data-show="<?= $idx ?>" id="<?= $idx ?>">
            <div>
                <a href="<?= $idx ?>"><?= $info['parts'][1] ?></a>
                <?php if (hasMinAuth('instructor')) { ?>
                <span class="config">
                    <?php
                        $decrease = true;
                    $increase = true;
                    if ($file_count == 0) {
                        $decrease = false;
                    }
                    if ($file_count == (count($files) - 1)) {
                        $increase = false;
                    }
                    ?>
                    <i title="Move video up" class="fa-solid fa-arrow-up <?= ! $decrease ? 'disabled' : '' ?>" 
                        <?php if ($increase) { ?> data-file="<?= $info['file'] ?>" data-prev_file="<?= $files[$file_count - 1]['file'] ?>" <?php } ?>>
                    </i>
                    <i title="Move video down" class="fa-solid fa-arrow-down <?= ! $increase ? 'disabled' : '' ?>" 
                        <?php if ($decrease) { ?> data-file="<?= $info['file'] ?>" data-next_file="<?= $files[$file_count + 1]['file'] ?>" <?php } ?>>
                    </i>
                    <i title="Edit title" class="fa-regular fa-pen-to-square" data-title="<?= $info['parts'][1] ?>" data-file="<?= $info['file'] ?>"></i>
                </span>
                <?php } ?>
            </div>
            <div class="info"></div>
        </div>
        <?php $file_count++ ?>
        <?php } // end foreach files?>
        <?php $last_id = $idx; ?>
    </div>
    <div id="total" data-day="<?= $day ?>" data-day_id="<?= $days[$day]['id'] ?>" data-text="<?= $days[$day]['desc'] ?>"></div>
    <div id="back">
        <a href="../" title="Back to overview">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <?php if (hasMinAuth('instructor')) { ?>
        <span class="config">
            <i title="Add Video" class="fa-solid fa-plus" id="add_video"></i>
        </span>
        <?php } ?>

        <div class="otherAreas">
            <?php if (hasMinAuth('student') && $offering['hasQuiz']) { ?>
            <span title="Quizzes">
                <a href="../quiz"><i class="fas fa-vial"></i></a>
            </span>
            <?php } ?>
            <?php if (hasMinAuth('student') && $offering['hasLab']) { ?>
            <span title="Labs">
                <a href="../lab"><i class="fas fa-flask"></i></a>
            </span>
            <?php } ?>
            <?php if (hasMinAuth('student')) { ?>
            <span title="Files" class="<?= $area == 'file' ? 'active' : ''?>">
                <a href="<?= "{$MY_BASE}/{$course}/{$block}" ?>/file"><i class="fa-solid fa-hard-drive"></i></a>
            </span>
            <?php } ?>
            <?php if (hasMinAuth('assistant')) { ?>
            <span title="Attendance">
                <a href="../attendance"><i class="fas fa-user-check"></i></a>
            </span>
            <?php } ?>
            <?php if (hasMinAuth('instructor')) { ?>
            <span title="Enrolled">
                <a href="../enrolled"><i class="fas fa-user-friends"></i></a>
            </span>
            <?php } ?>
        </div>

    </div>
</nav>
