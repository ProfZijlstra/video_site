<?php require("view/lab/typeDesc.php"); ?>
<!DOCTYPE html>
<html>

<head>
    <title>Lab: <?= $lab['name'] ?></title>
    <meta charset="utf-8" />
    <meta name=viewport content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="res/css/lib/font-awesome-all.min.css">
    <link rel="stylesheet" href="res/css/common-1.3.css">
    <link rel="stylesheet" href="res/css/adm-1.0.css">
    <link rel="stylesheet" href="res/css/lib/prism.css">
    <link rel="stylesheet" href="res/css/lab-1.5.css">
    <script src="res/js/lib/prism.js"></script>
    <script src="res/js/markdown-1.8.js"></script>
    <script src="res/js/countdown-1.1.js"></script>
    <script src="res/js/back.js"></script>
    <script src="res/js/camera-1.3.js"></script>
    <script src="res/js/lab/lab-1.9.js"></script>
    <script src="res/js/ensureSaved.js"></script>
</head>

<body id="doLab" class="lab labDeliverables">
    <?php include("header.php"); ?>
    <main>
        <nav id="back" class="back" title="Back">
            <i class="fa-solid fa-arrow-left"></i>
        </nav>
        <?php include("areas.php"); ?>
        <nav class="tools">
            <h3 title="Time Remaining">
                <span id="days"><?= $stop->format("%a") ?></span> Day(s)
                <span id="hours"><?= $stop->format("%H") ?></span>:<span id="minutes"><?= $stop->format("%I") ?></span>:<span id="seconds"><?= $stop->format("%S") ?></span>
            </h3>
        </nav>
        <div id="content">
            <div class="about">
                <?php if ($user_id): ?>
                    <input type="hidden" id="user_id" value="<?= $user_id ?>">
                <?php endif; ?>
                <div><label>Start:</label> <?= $lab['start'] ?></div>
                <div><label>Stop:</label> <?= $lab['stop'] ?></div>
                <?php if ($lab['type'] == "group") : ?>
                    <div id="labGroup" data-id="<?= $group ?>">
                        <label>Group:</label> <?= $group ?>
                    </div>
                <?php endif; ?>
            </div>

            <h1 id="lab_id" data-id="<?= $lab['id'] ?>">
                <?= $lab['name'] ?>
            </h1>

            <div class="description">
                <?php if ($lab['hasMarkDown']) : ?>
                    <?= $parsedown->text($lab['desc']) ?>
                <?php else : ?>
                    <pre><?= htmlspecialchars($lab['desc']) ?></pre>
                <?php endif; ?>
            </div>

            <div class="attachments">
                <?php foreach ($attachments as $attachment) : ?>
                    <div class="attachment">
                        <?php if ($attachment['type'] == "zip") : ?>
                            <a target="_blank" href="<?= $lab['id'] . '/download/' . $attachment['id'] ?>">
                            <?php else : ?>
                                <a target="_blank" href="<?= $attachment['file'] ?>">
                                <?php endif; ?>
                                <i class="fa-solid fa-paperclip"></i>
                                <?= $attachment['name'] ?>
                                </a>
                    </div>
                <?php endforeach; ?>
            </div>

            <div id="submission" data-id="<?= isset($submission) ? $submission['id'] : ''  ?>">
                <h2><?= count($deliverables) ?> Deliverable(s) </h2>
                <?php foreach ($deliverables as $deliv) : ?>
                    <?php $delivery = $deliveries[$deliv['id']] ?? [] ?>
                    <div class="dcontainer deliverables camContainer" data-id="<?= $deliv['id'] ?>" data-type="<?= $deliv['type'] ?>">
                        <div class="about">
                            <div class="meta" title="<?= $typeDesc[$deliv['type']] ?> to complete this deliverable">
                                <span class="type">
                                    <?= $deliv['type'] ?>
                                </span>
                            </div>
                            <div title="The lab total is <?= $labPoints ?>, this deliverable is <?= $deliv['points'] ?> of that total">
                                Points: <?= $deliv['points'] ?>
                            </div>
                        </div>
                        <div class="deliv" data-id="<?= $delivery['id'] ?>">
                            <div class="stats">
                                <label title="Time spent creating this deliverable">Time Spent:
                                    <select class="duration" autofocus>
                                        <?php
                                        $now = new DateTime();
                                        $now->setTime(0, 0, 0);
                                        $interval = new DateInterval('PT15M');
                                        ?>
                                        <?php for ($i = 0.25; $i <= 23.75; $i += 0.25) : ?>
                                            <?php $time = $now->format('H:i'); ?>
                                            <option value="<?= $time ?>" <?= $delivery['duration'] == $time . ":00" ? 'selected' : '' ?>>
                                                <?= $time ?>
                                            </option>
                                            <?php $now->add($interval); ?>
                                        <?php endfor; ?>
                                    </select>
                                </label>

                                <label title="Approximately how far you completed this deliverable" class="completion">Completion:
                                    <select class="completion">
                                        <option value="0">0%</option>
                                        <?php for ($i = 100; $i >= 10; $i -= 10) : ?>
                                            <option value="<?= $i ?>" <?= $delivery['completion'] == $i ? 'selected' : '' ?>>
                                                <?= $i ?>%
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </label>
                            </div>

                            <div class="description">
                                <?php if ($deliv['hasMarkDown']) : ?>
                                    <?= $parsedown->text($deliv['desc']) ?>
                                <?php else : ?>
                                    <pre><?= htmlspecialchars($deliv['desc']) ?></pre>
                                <?php endif; ?>
                            </div>
                            <?php if ($deliv['type'] == "zip" && $checks[$deliv['id']]): ?>
                                <?php 
                                    $presents = [];
                                    $not_presents = [];
                                    foreach ($checks[$deliv['id']] as $check) {
                                        if ($check['type'] == 'present') {
                                            $presents[] = $check;
                                        } else if ($check['type'] == 'not_present') {
                                            $not_presents[] = $check;
                                        }
                                    }
                                ?>
                                <?php if ($presents): ?>
                                <div class="checks presents">
                                    <h3>Should be present in zip file root:</h3>
                                    <?php foreach($presents as $check): ?>
                                        <div class="zipCheck present" id="c<?= $check['id']?>">
                                            <i class="fa-solid fa-check"></i>
                                            <?= $check['file'] ?>
                                            <div class="errorMsg">Please add</div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                                <?php if ($not_presents): ?>
                                <div class="checks notPresents">
                                    <h3>Should not be in zip file root:</h3>
                                    <?php foreach($not_presents as $check): ?>
                                        <div class="zipCheck notPresent" id="c<?= $check['id']?>">
                                            <i class="fa-solid fa-xmark"></i>
                                            <?= $check['file'] ?>
                                            <div class="errorMsg">Please remove</div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if ($deliv['type'] == 'txt') : ?>
                                <div class="textContainer">
                                    <textarea class="txt" placeholder="Write the text for your deliverable here." data-md="Use **markdown** syntax in your text like:&#10;&#10;```javascript&#10;const code = &quot;highlighted&quot;&semi;&#10;```" data-txt="Write the text for your deliverable here."><?= $delivery['text'] ?></textarea>

                                    <i title="Markdown" class="txt fa-brands fa-markdown <?= $delivery['hasMarkDown'] ? "active" : "" ?>"></i>
                                    <div class="mdContainer <?= $delivery['hasMarkDown'] ? "active" : "" ?>">
                                        <div class="preview"><button class="previewBtn">Preview Markdown</button></div>
                                        <div class="previewArea"></div>
                                    </div>
                                </div>
                            <?php else : ?>
                                <?php if ($deliv['type'] == 'url') : ?>
                                    <div class="urlContainer">
                                        <input type="url" class="url" placeholder="https://github.com/student/project" value="<?= $delivery['text'] ?>" />
                                    </div>
                                <?php else : /* type is: img, pdf, zip */ ?>
                                    <div class="fileContainer">
                                        <i title="<?= $delivery['text'] ? 'Replace' : 'Upload' ?> <?= $deliv['type'] ?>" class="upload fa-solid fa-upload"></i>
                                        <i class="spinner fa-solid fa-circle-notch"></i>
                                        <?php if ($deliv['type'] == 'img') : ?>
                                            <span><i class="fa-solid fa-camera" title="Open Camera"></i></span>
                                        <?php endif; ?>
                                        <input type="file" class="file fileUpload" />
                                        <a class="fileLink" href="<?= $delivery['file'] ?>" target="_blank"><?= $delivery['name'] ?></a>
                                        <i title="Delete" class="fa-solid fa-trash-can <?= $delivery['file'] ? '' : 'hide' ?>" data-id="<?= $delivery['id']?>"></i>
                                        <span class="check"><i class="fa-solid fa-check"></i></span>
                                        <?php if ($deliv['type'] == 'img') : ?>
                                            <div class="camera">
                                                <video></video>
                                                <div title="Close Camera" class="closeCamera hide">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </div>
                                                <div title="[Space] Take picture" class="takePicture hide"></div>
                                                <div title="Switch Camera" class="switchCamera hide">
                                                    <i class="fas fa-sync"></i>
                                                    <div class="camera_icon">
                                                        <i class="fas fa-camera"></i>
                                                    </div>
                                                </div>
                                                <canvas></canvas>
                                            </div>
                                            <img src="<?= $delivery['file'] ?>" class="answer <?= $delivery['file'] ? 'show' : 'hide' ?>" data-id="<?= $delivery['id'] ?>">
                                        <?php elseif ($deliv['type'] == "zip") : ?>
                                            <div class="listing"><?= $delivery['text'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                            <div class="textContainer">
                                <textarea class="cmt <?= $deliv['type'] == 'txt' ? '' : 'file' ?>" placeholder="Write any questions or comments about this deliverable here." data-md="Use **markdown** syntax in your text like:&#10;&#10;```javascript&#10;const code = &quot;highlighted&quot;&semi;&#10;```" data-txt="Write any questions or comments about this deliverable here."><?= $delivery['stuComment'] ?></textarea>

                                <i title="Markdown" class="cmt <?= $deliv['type'] == 'txt' ? 'stu' : 'file' ?> fa-brands fa-markdown <?= $delivery['stuCmntHasMD'] ? "active" : "" ?>"></i>
                                <div class="mdContainer <?= $delivery['stuCmntHasMD'] ? "active" : "" ?>">
                                    <div class="preview"><button class="previewBtn">Preview Markdown</button></div>
                                    <div class="previewArea"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="done">
                <div class="note">Deliverables are saved automatically</div>
                <nav class="back" title="Back">
                    <i class="fa-solid fa-arrow-left"></i>
                </nav>
            </div>
        </div>
    </main>
</body>

</html>
