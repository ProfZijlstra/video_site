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

        td {
            cursor: pointer;
        }

        td.delivs,
        td.points {
            width: 75px;
            text-align: right;
            padding-right: 5px !important;
        }
    </style>
    <script>
        window.addEventListener("load", () => {
            const tds = document.querySelectorAll('td');

            function goToUser() {
                const tr = this.parentNode;
                const user_id = tr.dataset.user_id;
                window.location = `user/${user_id}`
            }
            for (const td of tds) {
                td.onclick = goToUser;
            }
        });
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
            <h2>Quiz: <?= $lab['name'] ?></h2>
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
                                                <?= $member['knowAs'] ?> <?= $member['lastname'] ?>,
                                            <?php endforeach; ?>
                                        </span>
                                    <?php else : ?>
                                        <?= $students[$id]['knowAs'] ?> <?= $students[$id]['lastname'] ?>
                                    <?php endif; ?>
                                </a>
                            </td>
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
                                                <?= $member['knowAs'] ?> <?= $member['lastname'] ?>,
                                            <?php endforeach; ?>
                                        </span>
                                    <?php else : ?>
                                        <?= $students[$id]['knowAs'] ?> <?= $students[$id]['lastname'] ?>
                                    <?php endif; ?>
                                </a>
                            </td>
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
