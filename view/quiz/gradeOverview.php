<!DOCTYPE html>
<html>
    <head>
        <title>Grade Quiz Overview</title>
        <meta charset="utf-8" />
        <meta name=viewport content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="res/css/font-awesome-all.min.css">
        <link rel="stylesheet" href="res/css/common-1.1.css">
        <link rel="stylesheet" href="res/css/adm.css">
        <style>
            h3 {
                margin-bottom: 0px;
            }
            td {
                cursor: pointer;
            }
            
            td.start, td.stop, td.answers, td.points {
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
        window.location = `user/{$user_id}`
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
                <!-- Links to grade by question -->
                <div id="questions">
                    <strong>Grade Question:</strong>
                    <?php $count = 1; ?>
                    <?php foreach($questions as $question): ?>
                        <a href="question/<?= $question['id'] ?>">Q<?= $count++ ?></a>
                    <?php endforeach; ?>
                </div>

                <?php if($absent): ?>
                <!-- Table showing absent students -->
                <h3>No Submission:</h3>
                <table>
                    <tr>
                        <th>Name</th>
                    </tr>
                    <?php foreach ($absent as $id => $result): ?>
                    <tr data-user_id="<?= $result['id'] ?>">
                        <td>
                            <a href="user/<?= $id ?>">
                                <?= $result['knownAs'] ?> <?= $result['lastname'] ?>
                            </a>                        
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <?php endif; ?>

                <?php if($taken): ?>
                <!-- Table showing results of enrolled students -->
                <h3 title="Submission from enrolled students">Results</h3>
                <table>
                    <tr>
                        <th>Name</th>
                        <th>Start</th>
                        <th>Stop</th>
                        <th>Answers</th>
                        <th>Points</th>
                    </tr>
                    <?php foreach ($taken as $result): ?>
                    <tr data-user_id="<?= $result['id'] ?>">
                        <td><?= $result['knownAs'] ?> <?= $result['lastname'] ?></td>
                        <td class="start" title="<?= $starts[$result['id']] ?>"><?= substr($starts[$result['id']], 11) ?></td>
                        <td class="stop" title="<?= $stops[$result['id']] ?>"><?= substr($stops[$result['id']],11) ?></td>
                        <td class="answers"><?= $result['answers'] ?></td>
                        <td class="points"><?= $result['points'] == floor($result['points']) ? $result['points'] : number_format($result['points'],2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <?php endif; ?>

                <?php if($extra): ?>
                <!-- Table showing results of extra students -->
                <h3 title="Submissions from unenrolled students">Extra</h3>
                <table>
                    <tr>
                        <th>Name</th>
                        <th>Start</th>
                        <th>Stop</th>
                        <th>Answers</th>
                        <th>Points</th>
                    </tr>
                    <?php foreach ($extra as $result): ?>
                    <tr data-user_id="<?= $result['id'] ?>">
                        <td><?= $result['knownAs'] ?> <?= $result['lastname'] ?></td>
                        <td class="start" title="<?= $starts[$result['id']] ?>"><?= substr($starts[$result['id']], 11) ?></td>
                        <td class="stop" title="<?= $stops[$result['id']] ?>"><?= substr($stops[$result['id']],11) ?></td>
                        <td class="answers"><?= $result['answers'] ?></td>
                        <td class="points"><?= $result['points'] == floor(floatval($result['points'])) ? $result['points'] : number_format($result['points'],2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <?php endif; ?>
            </div>
        </main>
    </body>
</html>
