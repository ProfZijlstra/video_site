<?php require 'view/lab/typeDesc.php'; ?>
<?php
// helper function to display time and completion stats
// declared here instead of inisde the delivery.php file so that it doesn't
// get redeclared for each deliverable
function stats($delivery)
{ ?>
<div class="stats">
    <label title="Time spent creating this deliverable">Time Spent:
        <select class="duration" autofocus>
            <?php
            $now = new DateTime;
    $now->setTime(0, 0, 0);
    $interval = new DateInterval('PT15M');
    ?>
            <?php for ($i = 0.25; $i <= 23.75; $i += 0.25) { ?>
            <?php $time = $now->format('H:i'); ?>
            <option value="<?= $time ?>" <?= $delivery['duration'] == $time.':00' ? 'selected' : '' ?>>
                <?= $time ?>
            </option>
            <?php $now->add($interval); ?>
            <?php } ?>
            <option value="24:00" <?= $delivery['duration'] == '24:00' ? 'selected' : '' ?>>1 day</option>
            <?php for ($i = 2; $i <= 14; $i++) { ?>
            <option value="<?= $i * 24 ?>:00" <?= $delivery['duration'] == ($i * 24).':00' ? 'selected' : '' ?>>
                <?= $i ?> days
            </option>
            <?php } ?>
        </select>
    </label>

    <label title="Approximately how far you completed this deliverable" class="completion">Completed:
        <select class="completion">
            <option value="0">0%</option>
            <?php for ($i = 100; $i >= 10; $i -= 10) { ?>
            <option value="<?= $i ?>" <?= $delivery['completion'] == $i ? 'selected' : '' ?>>
                <?= $i ?>%
            </option>
            <?php } ?>
        </select>
    </label>
</div>
<?php } // end stats function?>
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
        <link rel="stylesheet" href="res/css/lab-1.9.css">
        <script src="res/js/lib/prism.js"></script>
        <script src="res/js/markdown-1.8.js"></script>
        <script src="res/js/countdown-1.1.js"></script>
        <script src="res/js/camera-1.5.js"></script>
        <script src="res/js/lab/lab-1.13.js"></script>
        <script src="res/js/ensureSaved.js"></script>
        <script src="res/js/lab_quiz_spa-1.3.js"></script>
    </head>

    <body id="doLab" class="lab labDeliverables" data-selected="<?= $selected ?>">
        <?php include 'header.php'; ?>
        <main>
            <nav class="back" title="Back">
                <a id="back" href="<?= $selected ? '../../lab' : '../lab' ?>">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
            </nav>
            <?php include 'areas.php'; ?>
            <nav class="tools">
                <i id="keyShortCuts" title="CTRL+> next, CTRL+< previous" class="fa-regular fa-keyboard"></i>
            </nav>
            <div id="content">
                <div class="about">
                    <?php if ($user_id) { ?>
                    <input type="hidden" id="user_id" value="<?= $user_id ?>">
                    <?php } ?>
                    <h3 class="time" title="Time Remaining">
                        <span id="days"><?= $stop->format('%a') ?></span> Day(s)
                        <span id="hours"><?= $stop->format('%H') ?></span>:<span id="minutes"><?= $stop->format('%I') ?></span>:<span id="seconds"><?= $stop->format('%S') ?></span>
                    </h3>
                    <div><label>Start:</label> <?= $lab['start'] ?></div>
                    <div><label>Stop:</label> <?= $lab['stop'] ?></div>
                    <?php if ($lab['type'] == 'group') { ?>
                    <div id="labGroup" data-id="<?= $group ?>">
                        <label>Group:</label> <?= $group ?>
                    </div>
                    <?php } ?>
                </div>

                <h1 id="lab_id" data-id="<?= $lab['id'] ?>">
                    <?= $lab['name'] ?>
                </h1>


                <div id="submission"
                    data-id="<?= isset($submission) ? $submission['id'] : ''  ?>">
                    <h2 class="multi">
                        <span class="mobileBlock">Deliverable</span>
                        <i id="chevLeft" class="fa-solid fa-chevron-left <?= $selected && $selected > 1 ? 'active' : '' ?>"></i>
                        <?php for ($i = 1; $i <= count($deliverables); $i++) { ?>
                        <span id="db<?= $i ?>" class="delivNum <?= $i == $selected ? 'active' : '' ?>"><?= $i ?></span>
                        <?php } ?>
                        <i id="chevRight" class="fa-solid fa-chevron-right <?= $selected && $selected < count($deliverables) ? 'active' : '' ?>"></i>
                    </h2>
                    <?php $i = 0; ?>
                    <?php foreach ($deliverables as $deliverable) { ?>
                    <?php $i++; ?>
                    <?php $delivery = $deliveries[$deliverable['id']] ?? [] ?>
                    <div id="d<?= $i ?>" class="dcontainer deliverables <?= $selected ? ($selected == $i ? '' : 'hide') : '' ?>"
                    data-type="<?= $deliverable['type'] ?>">
                        <div class="about">
                            <div class="meta" title="<?= $typeDesc[$deliverable['type']] ?> to complete this deliverable">
                                <span class="type">
                                    <?= $deliverable['type'] ?>
                                </span>
                            </div>
                            <div title="The lab total is <?= $labPoints ?>, this deliverable is <?= $deliverable['points'] ?> of that total">
                                Points: <?= $deliverable['points'] ?>
                            </div>
                        </div>

                        <div class="deliverable" data-id="<?= $deliverable['id'] ?>">
                            <div class="attachments">
                                <?php foreach ($attachments as $attachment) { ?>
                                <?php if ($attachment['deliverable_id'] == $deliverable['id']) { ?>
                                <div class="attachment">
                                    <?php if ($attachment['type'] == 'zip') { ?>
                                    <a target="_blank" href="<?= $lab['id'].'/download/'.$attachment['id'] ?>">
                                        <?php } else { ?>
                                        <a target="_blank" href="<?= $attachment['file'] ?>">
                                            <?php } ?>
                                            <i class="fa-solid fa-paperclip"></i>
                                            <?= $attachment['name'] ?>
                                        </a>
                                </div>
                                <?php } ?>
                                <?php } ?>
                            </div>

                            <div class="description">
                                <?php if ($deliverable['hasMarkDown']) { ?>
                                <?= $parsedown->text($deliverable['desc']) ?>
                            <?php } else { ?>
                            <pre><?= htmlspecialchars($deliverable['desc']) ?></pre>
                                <?php } ?>
                            </div>

                        </div> <!-- close deliverable -->

                        <?php include 'delivery.php'?>

                    </div>
                    <?php } ?>
                </div>

                <div class="done">
                    <div class="note">Deliverables are saved automatically</div>
                        <a href="../../lab" title="Back to overview">
                            <i class="fa-solid fa-arrow-left"></i>
                        </a>
                    </nav>
                </div>
            </div>
        </main>
    </body>

</html>
