<!DOCTYPE html>
<html>
    <head>
        <title><?= $block ?> Stats</title>
        <meta charset="utf-8" />
        <meta name=viewport content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="res/css/lib/font-awesome-all.min.css">
        <link rel="stylesheet" href="res/css/common-1.3.css">
        <link rel="stylesheet" href="res/css/adm-1.0.css">
        <link rel="stylesheet" href="res/css/lib/charts.css">
        <link rel="stylesheet" href="res/css/colCharts.css">
        <style>
        table tr {
            cursor: pointer;
        }
        div.stats {
            display: grid;
            grid-template-columns: auto auto;
        }
        </style>
        <script>
        window.addEventListener('load', () => {
            function clickRow() {
                window.location.href = `${this.getAttribute('data-href')}/chart`;
            }
            document.querySelectorAll('tr').forEach(tr => {
                tr.addEventListener('click', clickRow);
            });
        });
        </script>
    </head>

    <body>
        <?php include 'header.php'; ?>
        <main>
            <nav class="areas">
                <div title="Back">
                    <a href="../<?= $block ?>/">
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>
                </div>
            </nav>

            <div id="content">
                <ul class="charts-css legend legend-square legend-inline">
                    <li>Video Duration</li>
                    <li>Average View Time</li>
                    <li>Your Viewed Time</li>
                </ul>
                <div class="stats">
                    <?php for ($week = 1; $week <= $offering['lessonParts']; $week++) { ?>
                    <div>
                        <table class="charts-css column multiple show-heading">
                            <caption><?= "Week $week" ?></caption>
                            <?php for ($day = 1; $day <= $offering['lessonsPerPart']; $day++) { ?>
                            <?php
                                $duration = number_format($videos["W{$week}D{$day}"]['totalDuration'] / 360000, 2);
                                $average = $averages["W{$week}D{$day}"]['users'] != 0 ? number_format($averages["W{$week}D{$day}"]['time'] / $averages["W{$week}D{$day}"]['users'], 2) : '0';
                                $viewed = number_format($person["W{$week}D{$day}"]['time'], 2);
                                ?>
                            <tr data-href="<?= "W{$week}D{$day}" ?>"}>
                                <td style="--size: <?= $duration ?>" title="<?= $duration ?>"> 
                                </td>
                                <td style="--size: <?= $average ?>"  title="<?= $average ?>" > 
                                    <?php if (hasMinAuth('instructor') && $averages["W{$week}D{$day}"]['users']) { ?>
                                    <span><a title="Amount of users for average" href="<?= "W{$week}D{$day}/userChart" ?>"}>
                                    <?= $averages["W{$week}D{$day}"]['users'] ?>
                                    </a></span>
                                    <?php } ?>
                                </td>
                                <td style="--size: <?= $viewed ?>"   title="<?= $viewed ?>"  >
                                </td>
                            </tr>
                            <?php } ?>
                        </table>
                    </div>
                    <?php } ?> 
                </div>
                <div class="totals">
                    <?php if (hasMinAuth('instructor')) { ?>
                    <a href="userChart">
                        <span title="Users"><i class="fa-regular fa-user"></i> <?= $total['users'] ?></span> 
                        <span title="Views"><i class="fa-regular fa-eye"></i> <?= $total['views'] ?></span>
                        <span title="Hours"><i class="fa-regular fa-clock"></i> <?= $total['time'] ?></span>
                    </a>
                    <?php } else { ?>
                        <span title="Users"><i class="fa-regular fa-user"></i> <?= $total['users'] ?></span> 
                        <span title="Views"><i class="fa-regular fa-eye"></i> <?= $total['views'] ?></span>
                        <span title="Hours"><i class="fa-regular fa-clock"></i> <?= $total['time'] ?></span>
                    <?php } ?>
                </div>
            </div>
        </main>
    </body>

</html>
