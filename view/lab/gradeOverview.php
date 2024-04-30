<!DOCTYPE html>
<html>

<head>
    <title>Grade Quiz Overview</title>
    <meta charset="utf-8" />
    <meta name=viewport content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="res/css/lib/font-awesome-all.min.css">
    <link rel="stylesheet" href="res/css/common-1.2.css">
    <link rel="stylesheet" href="res/css/adm.css">
    <link rel="stylesheet" href="res/css/lab.css">
</head>

<body id="gradeLab">
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
                    <a href="deliverable/<?= $deliv['id'] ?>">D<?= $count++ ?></a>
                <?php endforeach; ?>
            </div>

            <?php function shortDate(string $date)
            {
                $day = substr($date, 8, 2);
                $time = substr($date, 11, 5);
                return "{$day}<sup>th</sup> {$time}";
            } ?>
            <?php function submissionTable($list, $title, $titleDetail, $showDetails, $students, $members)
            { ?>

                <?php if ($list) : ?>
                    <!-- Table showing results of enrolled students -->
                    <h3 title="<?= $titleDetail ?>"><?= $title ?></h3>
                    <table>
                        <tr>
                            <th>Name</th>
                            <?php if ($showDetails) : ?>
                                <th>Start</th>
                                <th>Stop</th>
                                <th>Delivs</th>
                                <th>Points</th>
                            <?php endif; ?>
                        </tr>
                        <?php foreach ($list as $submission) : ?>
                            <tr>
                                <td>
                                    <a href="submission/<?= $submission['id'] ?>">
                                        <?php if ($submission['group']) : ?>
                                            Group <?= $submission['group'] ?>:
                                            <span class="members">
                                                <?php foreach ($members[$submission['group']] as $member) : ?>
                                                    <?= $member['knownAs'] ?> <?= $member['lastname'] ?>,
                                                <?php endforeach; ?>
                                            </span>
                                        <?php else : ?>
                                            <?php $student = $students[$submission['user_id']] ?>
                                            <?= $student['knownAs'] ?> <?= $student['lastname'] ?>
                                        <?php endif; ?>
                                    </a>
                                </td>
                                <?php if ($showDetails) : ?>
                                    <td class="start" title="<?= $submission['start'] ?>"><?= shortDate($submission['start']) ?> </td>
                                    <td class="stop" title="<?= $submission['stop'] ?> "><?= shortDate($submission['stop']) ?> </td>
                                    <td class="delivs"><?= $submission['delivs'] ?></td>
                                    <td class="points">
                                        <?php if ($submission['points']) : ?>
                                            <?= $submission['points'] == floor($submission['points']) ? $submission['points'] : number_format($submission['points'], 2) ?>
                                        <?php else : ?>
                                            0
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php endif; ?>
            <?php } ?>
            <?php submissionTable($none, "No Submission", "", false, $students, $groups); ?>
            <?php submissionTable($taken, "Results", "Submissions from enrolled students", true, $students, $members); ?>
            <?php submissionTable($extra, "Extra", "Submissions from unenrolled students", true, $students, $members); ?>
        </div>
    </main>
</body>

</html>
