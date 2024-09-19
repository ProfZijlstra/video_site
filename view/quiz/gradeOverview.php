<!DOCTYPE html>
<html>

<head>
    <title>Grade Quiz Overview</title>
    <meta charset="utf-8" />
    <meta name=viewport content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="res/css/lib/font-awesome-all.min.css">
    <link rel="stylesheet" href="res/css/common-1.3.css">
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

        td.start,
        td.stop,
        td.answers,
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
            <a href="../../quiz">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
        </nav>
        <div id="content">
            <h2>Quiz: <?= $quiz['name'] ?></h2>
            <!-- Links to grade by question -->
            <div id="questions">
                <strong>Grade Question:</strong>
                <?php $count = 1; ?>
                <?php foreach ($questions as $question) : ?>
                    <?php
                    $gradeStatus = '';
                    if ($question['answers'] != 0) {
                        $gradeStatus = 'graded';
                        if ($question['ungraded'] != 0) {
                            $gradeStatus = 'ungraded';
                        }
                    }
                    ?>
                    <a href="question/<?= $question['id'] ?>" class="<?= $gradeStatus ?>" title="<?= $gradeStatus ?>">
                        Q<?= $count++ ?>(<?= number_format($question['avgPoints'], 1) ?>)
                    </a>
                <?php endforeach; ?>
            </div>

            <?php function answerTable($list, $detail, $title, $longTitle, $starts, $stops) { ?>
                <?php if ($list) : ?>
                    <!-- Table showing results of enrolled students -->
                    <h3 title="<?= $longTitle ?>"><?= $title ?></h3>
                    <table>
                        <tr>
                            <th>Name</th>
                            <?php if ($detail) : ?>
                                <th>Start</th>
                                <th>Stop</th>
                                <th>Answers</th>
                                <th>Points</th>
                            <?php endif; ?>
                        </tr>
                        <?php foreach ($list as $result) : ?>
                            <?php
                            $gradeStatus = '';
                            if ($result['answers'] != 0) {
                                $gradeStatus = 'graded';
                                if ($result['ungraded'] != 0) {
                                    $gradeStatus = 'ungraded';
                                }
                            }
                            ?>
                            <tr data-user_id="<?= $result['id'] ?>">
                                <td>
                                    <a href="user/<?= $result['id'] ?>" class="<?= $gradeStatus ?>" title="<?= $gradeStatus ?>">
                                        <?= $result['knownAs'] ?> <?= $result['lastname'] ?>
                                    </a>
                                </td>
                                <?php if ($detail) : ?>
                                    <td class="start" title="<?= $starts[$result['id']] ?>"><?= substr($starts[$result['id']], 11) ?></td>
                                    <td class="stop" title="<?= $stops[$result['id']] ?>"><?= substr($stops[$result['id']], 11) ?></td>
                                    <td class="answers"><?= $result['answers'] ?></td>
                                    <td class="points"><?= $result['points'] == floor($result['points']) ? $result['points'] : number_format($result['points'], 2) ?></td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php endif; ?>
            <?php }  ?>

            <?php answerTable($absent, false, "No Submission", "", $starts, $stops); ?>
            <?php answerTable($taken, true, "Results", "Submissions from enrolled students", $starts, $stops); ?>
            <?php answerTable($extra, true, "Extra", "Submissions from unenrolled students", $starts, $stops); ?>

        </div>
    </main>
</body>

</html>
