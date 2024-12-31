<!DOCTYPE html>
<html>

    <head>
        <title><?= strtoupper($course) ?> <?= $day ?> Lecture</title>
        <meta charset="utf-8" />
        <meta name=viewport content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="res/css/lib/font-awesome-all.min.css" />
        <link rel="stylesheet" href="res/css/common-1.3.css">
        <link rel="stylesheet" type="text/css" href="res/css/video-1.9.css" />
        <link rel="stylesheet" href="res/css/lib/prism.css" />
        <script src="res/js/markdown-1.8.js"></script>
        <script src="res/js/video-1.18.js"></script>
        <script src="res/js/lib/prism.js"></script>
        <?php if (hasMinAuth('instructor')) { ?>
        <script src="res/js/adm_video.js"></script>
        <link rel="stylesheet" href="res/css/adm-1.0.css" />
        <?php } ?>
    </head>

    <body>
        <?php include 'header.php'; ?>
        <i id="bars" class="fa-solid fa-bars"></i>
        <div id="container" data-oid="<?= $offering_id ?>">

            <!-- start sidebar -->
            <nav id="videos" class="<?= $theater ?>">
                <nav>
                    <?php if (hasMinAuth('instructor')) { ?>
                    <?php if (! $isRemembered) { ?>
                    <i title="Configure Videos" id="config-btn" class="fa-solid fa-gear"></i>
                    <?php } else { ?>
                    <a href="reAuth">
                        <i title="Configure Videos" id="config-btn" class="fa-solid fa-gear"></i>
                    </a>
                    <?php } // end is not remembered?>
                    <?php } // end is instructor?>

                    <!-- mini course calendar -->
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

                <!-- Lecture part navigation tabs -->
                <div id="tabs">
                    <?php include 'tabs.php' ?>
                </div>

                <div id="back">
                    <a href="../" title="Back to overview">
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>

                    <?php if (hasMinAuth('instructor')) { ?>
                    <span class="config">
                    <?php if (! $isRemembered) { ?>
                        <i title="Add Lesson Part" class="fa-solid fa-plus" id="add_part"></i>
                    <?php } else { ?>
                        <a href="reAuth">
                            <i title="Add Lesson Part" class="fa-solid fa-plus" id="add_part"></i>
                        </a>
                    <?php } ?>
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
                        <span title="Files"> 
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

            </nav> <!-- End sidebar -->

            <main id="day" data-id="<?= $days[$day]['id'] ?>">
                <div class="playSpeed">
                    <span class="slower">-</span>
                    <span class="curSpeed">1.0</span>
                    <span class="faster">+</span>
                </div>
                <?php
        $firstIdx = array_key_first($parts);
        $lastIdx = array_key_last($parts);
        $passed = 0;
        foreach ($parts as $idx => $part) {
            $has_pdf = false;
            $has_vid = false;
            if (isset($pdfs[$idx])) {
                $has_pdf = true;
                $info = $pdf_info = $pdfs[$idx];
            }
            if (isset($videos[$idx])) {
                $has_vid = true;
                $info = $vid_info = $videos[$idx];
            }

            if ($totalDuration == 0) {
                $passedPercent = 0;
                $currentPrecent = 0;
            } else {
                $passedPercent = ($passed / $totalDuration) * 100;
                $currentPrecent = $passedPercent + (($info['duration'] / $totalDuration) * 100);
            }
            ?>
                <?php include 'lessonPart.php' ?>

                <?php
            $passed += $info['duration'];
        } // end all articles (vid-pdfs)
        ?>
            </main>
        </div> <!-- close container-->

        <?php if (hasMinAuth('instructor')) { ?>

        <dialog id="editDialog" class="modal">
            <i id="closeEdit" class="fas fa-times-circle close"></i>

            <h3>Edit Lecture Part</h3>
            <form>
                <label>Title:</label>
                <input name="title" id="editTitle" value="">
                <div class="btn">
                    <button id="editBtn">
                        <i title="Submit Add" class="fa-solid fa-pen"></i>
                    </button>
                </div>
            </form>
        </dialog>

        <dialog id="addDialog" class="modal">
            <i id="closeAdd" class="fas fa-times-circle close"></i>

            <h3>Add Lecture Part</h3>
            <form>
                <label>Title:</label>
                <input name="title" id="addTitle" value="">
                <div class="btn">
                    <button id="addBtn">
                        <i title="Submit Edit" class="fa-solid fa-plus"></i>
                    </button>
                </div>
            </form>
        </dialog>

        <?php } ?>

    </body>

</html>
