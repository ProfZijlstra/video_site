<!DOCTYPE html>
<html>

    <head>
        <title><?= strtoupper($course) ?> <?= $day ?> Videos</title>
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
        <link rel="stylesheet" href="res/css/adm-1.0.css">
        <script src="https://unpkg.com/react@17/umd/react.production.min.js" crossorigin></script>
        <script src="https://unpkg.com/react-dom@17/umd/react-dom.production.min.js" crossorigin></script>
        <script src="res/js/info.js"></script>
        <script src="res/js/adm_video.js"></script>
        <?php } ?>
    </head>

    <body>
        <?php include 'header.php'; ?>
        <i id="bars" class="fa-solid fa-bars"></i>
        <div id="container" data-oid="<?= $offering_id ?>">
            <?php include 'sidebar.php'; ?>
            <main id="day" data-id="<?= $days[$day]['id'] ?>">
                <div class="playSpeed">
                    <span class="slower">-</span>
                    <span class="curSpeed">1.0</span>
                    <span class="faster">+</span>
                </div>
                <?php
                $passed = 0;
        foreach ($files as $idx => $pdf_vid) {
            $has_pdf = false;
            $has_vid = false;
            if (isset($pdf_vid['pdf'])) {
                $has_pdf = true;
                $info = $pdf_info = $pdf_vid['pdf'];
            }
            if (isset($pdf_vid['vid'])) {
                $has_vid = true;
                $info = $vid_info = $pdf_vid['vid'];
            }

            if ($totalDuration == 0) {
                $passedPercent = 0;
                $currentPrecent = 0;
            } else {
                $passedPercent = ($passed / $totalDuration) * 100;
                $currentPrecent = $passedPercent + (($info['duration'] / $totalDuration) * 100);
            } ?>

                <article id="a<?= $idx ?>" class="<?= $idx == $file_idx ? 'selected' : '' ?>" 
                    data-name="<?= $info['parts'][0].'_'.$info['parts'][1] ?>">
                    <h2><?= $info['parts'][1] ?></h2>

                    <div class="media">
                        <i class="fa-solid video fa-video<?= $has_vid ? ' available hide' : '-slash'?>" 
                            title="Switch to video <?= $has_vid ? '' : 'not available'?>"></i>
                        <div class="pdf <?= ! $has_vid ? 'hide' : '' ?>">
                            <i class="far fa-file-pdf pdf <?= $has_pdf ? 'available ' : '' ?>"
                                title="Switch to PDF <?= $has_pdf ? '' : 'not available'?>"
                                data-file="<?= $pdf_info['parts'][0].'_'.$pdf_info['parts'][1] ?>"></i>
                            <?php if (! $has_pdf) { ?>
                            <i title="Switch to PDF not available" class="fa-solid fa-slash"></i>
                            <?php } ?>
                        </div>
                    </div>

                    <?php if ($has_vid) { ?>
                    <video controls controlslist="nodownload" 
                        <?php if ($idx == $file_idx) { ?>
                        src="<?= "res/course/{$course}/{$block}/lecture/{$day}/vid/{$vid_info['file']}" ?>" 
                        <?php } ?>
                        data-src="<?= "res/course/{$course}/{$block}/lecture/{$day}/vid/{$vid_info['file']}" ?>">
                    </video>
                    <?php } ?>

                    <?php if ($has_pdf) { ?>
                    <object class="<?= $has_vid ? 'hide' : ''?>" 
                        type="application/pdf" 
                        data="res/<?= $course?>/<?= $block?>/lecture/<?= $day?>/pdf/<?= $pdf_info['file'] ?>">
                        <div class="noVid">
                            <i class="fa-solid fa-video" title="Video"></i>
                            <div>Your browser doesn't seem to support PDF previews</div>
                            <p>
                                <a href="res/<?= $course?>/<?= $block?>/lecture/<?= $day?>/pdf/<?= $pdf_info['file'] ?>">
                                    Click here to download the PDF
                                </a>
                            </p>
                        </div>
                    </object>
                    <?php } ?>

                    <?php if ($totalDuration) { ?>
                    <div class="progress">
                        <?php
                        $progClass = 'passed';
                        foreach ($files as $idxx => $content) {
                            if ($progClass == 'current') {
                                $progClass = 'future';
                            }
                            if ($idx == $idxx) {
                                $progClass = 'current';
                            }
                            ?>
                        <?php if (isset($content['vid'])) { ?>
                        <?php $vid = $content['vid']; ?>
                        <div title="<?= $content['vid']['parts'][1].' ('.substr($content['vid']['parts'][3], 3, 5).')'  ?>"
                            data-vid="<?= $content['vid']['parts'][0] ?>" 
                            class="tab <?= $progClass ?>" 
                            style="width: <?= number_format(($content['vid']['duration'] / $totalDuration) * 100, 2) ?>%"></div>
                        <?php } ?>
                        <?php } // end foreach files?>

                        <div class="time">Total time: <?= $totalTime ?></div>
                        <div class="autoplay">autoplay <i class="auto_toggle fas fa-toggle-off"></i></div>
                        <div title="Keyboard Shortcuts"><i class="fa-solid fa-keyboard shortcuts"></i></div>
                    </div>
                    <?php } ?>

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
                            <?php if ($idx < $last_id) { ?>
                            <a href="<?= $idx < 9 ? '0'.($idx + 1) : $idx ?>" title="Next Video"
                                data-video="<?= $idx < 9 ? '0'.($idx + 1) : $idx ?>">
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                            <?php } else { ?>
                            <i class="fa-solid fa-arrow-right disabled"></i>
                            <?php } ?>
                        </div>
                    </nav>

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
                <?php
            $passed += $info['duration'];
        } // end all articles (vid-pdfs)
        ?>
            </main>
        </div> <!-- close container-->

        <?php if (hasMinAuth('instructor')) { ?>
        <div id="overlay">
            <i id="close-overlay" class="fas fa-times-circle"></i>

            <div id="edit_modal" class="modal hide">
                <h2>Edit Video Title</h2>
                <form method="POST" action="title">
                    <input type="hidden" name="file" id="video_file" value="" />
                    <div class="line">
                        <label>Title:</label>
                        <input name="title" id="video_title" value="" />
                    </div>
                    <div class="submit">
                        <button>Submit</button>
                    </div>
                </form>
            </div>

            <div id="content"></div>

            <form method="post" action="decrease" id="decreaseSequence">
                <input type="hidden" name="file" id="up_file" />
                <input type="hidden" name="prev_file" id="prev_file" />
            </form>

            <form method="post" action="increase" id="increaseSequence">
                <input type="hidden" name="file" id="down_file" />
                <input type="hidden" name="next_file" id="next_file" />
            </form>

            <div id="add_modal" class="modal hide">
                <h2>Add Video</h2>
                <form method="POST" action="add" enctype="multipart/form-data">
                    <div class="line">
                        <label>File:</label>
                        <input name="file" type="file" id="add_file" value="" />
                    </div>
                    <div class="line">
                        <label>Title:</label>
                        <input name="title" id="video_title" value="" />
                    </div>
                    <div class="submit">
                        <button>Submit</button>
                    </div>
                </form>
            </div>

        </div>
        <?php } ?>

    </body>

</html>
