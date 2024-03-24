<!DOCTYPE html>
<html>

<head>
    <title>Grade Quiz Overview</title>
    <meta charset="utf-8" />
    <meta name=viewport content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="res/css/lib/font-awesome-all.min.css">
    <link rel="stylesheet" href="res/css/common-1.2.css">
    <link rel="stylesheet" href="res/css/adm.css">
    <style>
        #content h2 {
            margin-bottom: 15px;
        }

        h3 {
            margin-bottom: 0px;
        }

        td.start,
        td.stop,
        td.delivs,
        td.points {
            width: 75px;
            text-align: right;
            padding-right: 5px !important;
        }
    </style>
    <script>
        window.addEventListener("load", () => {});
    </script>
</head>

<body>
    <?php include("header.php"); ?>
    <main>
        <nav class="back" title="Back">
            <a href="../../lab">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
        </nav>
        <div id="content">
            <h2>Lab: <?= $lab['name'] ?></h2>
            <!-- Links to grade by question -->
            <div id="deliverables">
                <strong>Grade Deliverable:</strong>
                <?php $count = 1; ?>
                <?php foreach ($deliverables as $deliv) : ?>
                    <a href="deliverable/<?= $deliv['id'] ?>">Q<?= $count++ ?></a>
                <?php endforeach; ?>
            </div>

            <?php if ($absent) : ?>
                <!-- Table showing absent students -->
                <h3>No Submission:</h3>
                <table>
                    <tr>
                        <th>Name</th>
                    </tr>
                    <?php foreach ($absent as $id => $submission) : ?>
                        <tr>
                            <td>
                                <a href="submission/<?= $submission['id'] ?>">
                                    <?php if ($type == 'group') : ?>
                                        Group <?= $id ?>:
                                        <span class="members">
                                            <?php foreach ($groups[$id]['members'] as $member) : ?>
                                                <?= $member['knownAs'] ?> <?= $member['lastname'] ?>,
                                            <?php endforeach; ?>
                                        </span>
                                    <?php else : ?>
                                        <?= $students[$id]['knownAs'] ?> <?= $students[$id]['lastname'] ?>
                                    <?php endif; ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>

            <?php if ($taken) : ?>
                <!-- Table showing results of enrolled students -->
                <h3 title="Submission from enrolled students">Results</h3>
                <table>
                    <tr>
                        <th>Name</th>
                        <th>Start</th>
                        <th>Stop</th>
                        <th>Delivs</th>
                        <th>Points</th>
                    </tr>
                    <?php foreach ($taken as $id => $submission) : ?>
                        <tr>
                            <td>
                                <a href="submission/<?= $submission['id'] ?>">
                                    <?php if ($type == 'group') : ?>
                                        Group <?= $id ?>:
                                        <span class="members">
                                            <?php foreach ($groups[$id]['members'] as $member) : ?>
                                                <?= $member['knownAs'] ?> <?= $member['lastname'] ?>,
                                            <?php endforeach; ?>
                                        </span>
                                    <?php else : ?>
                                        <?= $students[$id]['knownAs'] ?> <?= $students[$id]['lastname'] ?>
                                    <?php endif; ?>
                                </a>
                            </td>
                            <td class="start" title="<?= $submission['start'] ?>">d<?= substr($submission['start'], 8, 8) ?> </td>
                            <td class="stop" title="<?= $submission['stop'] ?> ">d<?= substr($submission['stop'], 8, 8) ?> </td>
                            <td class="delivs"><?= $submission['delivs'] ?></td>
                            <td class="points"><?= $submission['points'] == floor($submission['points']) ? $submission['points'] : number_format($submission['points'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>

            <?php if ($extra) : ?>
                <!-- Table showing results of extra students -->
                <h3 title="Submissions from unenrolled students">Extra</h3>
                <table>
                    <tr>
                        <th>Name</th>
                        <th>Start</th>
                        <th>Stop</th>
                        <th>Delivs</th>
                        <th>Points</th>
                    </tr>
                    <?php foreach ($taken as $id => $submission) : ?>
                        <tr>
                            <td>
                                <a href="submission/<?= $submission['id'] ?>">
                                    <?php if ($type == 'group') : ?>
                                        Group <?= $id ?>:
                                        <span class="members">
                                            <?php foreach ($groups[$id]['members'] as $member) : ?>
                                                <?= $member['knownAs'] ?> <?= $member['lastname'] ?>,
                                            <?php endforeach; ?>
                                        </span>
                                    <?php else : ?>
                                        <?= $students[$id]['knownAs'] ?> <?= $students[$id]['lastname'] ?>
                                    <?php endif; ?>
                                </a>
                            </td>
                            <td class="start"><?= $submission['start'] ?></td>
                            <td class="stop"><?= $submission['stop'] ?></td>
                            <td class="delivs"><?= $submission['delivs'] ?></td>
                            <td class="points"><?= $submission['points'] == floor($submission['points']) ? $submission['points'] : number_format($submission['points'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>
    </main>
</body>

</html>
