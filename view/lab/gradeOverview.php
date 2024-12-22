<!DOCTYPE html>
<html>

<head>
    <title>Grade Lab Overview</title>
    <meta charset="utf-8" />
    <meta name=viewport content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="res/css/lib/font-awesome-all.min.css">
    <link rel="stylesheet" href="res/css/common-1.3.css">
    <link rel="stylesheet" href="res/css/adm-1.0.css">
    <link rel="stylesheet" href="res/css/lab-1.6.css">
</head>

<body id="gradeLab" class="lab">
    <?php include 'header.php'; ?>
    <main>
        <nav class="back" title="Back">
            <a href="../../lab">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
        </nav>
        <?php include 'areas.php'; ?>
        <div id="content">
            <h2>Lab: <?= $lab['name'] ?></h2>
            <!-- Links to grade by question -->
            <div id="deliverables">
                <strong>Grade Deliverable:</strong>
                <?php $count = 1; ?>
                <?php foreach ($deliverables as $deliv) { ?>
                    <?php
                    $gradeStatus = '';
                    if ($deliv['answers'] != 0) {
                        $gradeStatus = 'graded';
                        if ($deliv['ungraded'] != 0) {
                            $gradeStatus = 'ungraded';
                        }
                    }
                    ?>
                    <a href="deliverable/<?= $deliv['id'] ?>" class="<?= $gradeStatus ?>" title="<?= $gradeStatus ?>">
                        D<?= $count++ ?>(<?= number_format($deliv['avgPoints'], 1) ?>)
                    </a>
                <?php } ?>
            </div>

            <?php if ($absent) { ?>
                <h3 title="Students that have not started yet">Not Started</h3>
                <table>
                    <tr>
                        <th>Name</th>
                    </tr>
                    <?php foreach ($absent as $student) { ?>
                        <tr>
                            <td>
                                <?php if ($lab['type'] == 'individual') { ?>
                                <a href="user/<?= $student['id'] ?>">
                                    <?= $student['knownAs'] ?> <?= $student['lastname'] ?>
                                </a>
                                <?php } else { ?>
                                    <a href="group/<?= $student ?>">
                                        <?= $student ?>
                                    </a>
                                <?php } ?>
                            </td>
                        </t>
                    <?php } ?>
                </table>
            <?php } ?>

            <?php function shortDate($date)
            {
                if (! $date) {
                    return '';
                }
                $day = substr($date, 8, 2);
                $time = substr($date, 11, 5);

                return "{$day}<sup>th</sup> {$time}";
            } ?>
            <?php function submissionTable($list, $title, $titleDetail, $showDetails, $students, $members)
            { ?>

                <?php if ($list) { ?>
                    <!-- Table showing results of enrolled students -->
                    <h3 title="<?= $titleDetail ?>"><?= $title ?></h3>
                    <table>
                        <tr>
                            <th>Name</th>
                            <?php if ($showDetails) { ?>
                                <th>Start</th>
                                <th>Stop</th>
                                <th>Delivs</th>
                                <th>Points</th>
                            <?php } ?>
                        </tr>
                        <?php foreach ($list as $submission) { ?>
                            <tr>
                                <td>
                                    <?php
                                    $gradeStatus = '';
                            if ($submission['delivs'] != 0) {
                                $gradeStatus = 'graded';
                                if ($submission['ungraded'] != 0) {
                                    $gradeStatus = 'ungraded';
                                }
                            }
                            ?>
                                    <a href="submission/<?= $submission['id'] ?>" class="<?= $gradeStatus ?>" title="<?= $gradeStatus ?>">
                                        <?php if ($submission['group']) { ?>
                                            <?= $submission['group'] ?>:
                                            <span class="members">
                                                <?php foreach ($members[$submission['group']] as $member) { ?>
                                                    <?= $member['knownAs'] ?> <?= $member['lastname'] ?>,
                                                <?php } ?>
                                            </span>
                                        <?php } else { ?>
                                            <?php $student = $students[$submission['user_id']] ?>
                                            <?= $student['knownAs'] ?> <?= $student['lastname'] ?>
                                        <?php } ?>
                                    </a>
                                </td>
                                <?php if ($showDetails) { ?>
                                    <td class="start" title="<?= $submission['start'] ?>"><?= shortDate($submission['start']) ?> </td>
                                    <td class="stop" title="<?= $submission['stop'] ?> "><?= shortDate($submission['stop']) ?> </td>
                                    <td class="delivs"><?= $submission['delivs'] ?></td>
                                    <td class="points">
                                        <?php if ($submission['points']) { ?>
                                            <?= $submission['points'] == floor($submission['points']) ? $submission['points'] : number_format($submission['points'], 2) ?>
                                        <?php } else { ?>
                                            0
                                        <?php } ?>
                                    </td>
                                <?php } ?>
                            </tr>
                        <?php } ?>
                    </table>
                <?php } ?>
            <?php } ?>
            <?php submissionTable($none, 'Started', '', false, $students, $groups); ?>
            <?php submissionTable($taken, 'Results', 'Submissions from enrolled students', true, $students, $members); ?>
            <?php submissionTable($extra, 'Extra', 'Submissions from unenrolled students', true, $observers, $members); ?>
        </div>
    </main>
</body>

</html>
