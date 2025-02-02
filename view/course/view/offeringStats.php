<!DOCTYPE html>
<html>

<head>
    <title><?= $block ?> Settings</title>
    <meta charset="utf-8" />
    <meta name=viewport content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="res/css/lib/font-awesome-all.min.css">
    <link rel="stylesheet" href="res/css/common-1.3.css">
    <link rel="stylesheet" href="res/css/adm-1.0.css">
    <link rel="stylesheet" href="res/css/lib/charts.css">
    <style>
        div#content {
            width: 80vw;
        }
        div.stats {
            display: grid;
            grid-template-columns: auto auto;
        }
    </style>
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
                        <table class="charts-css column multiple show-heading hide-data">
                            <caption><?= "Week $week" ?></caption>
                            <?php for ($day = 1; $day <= $offering['lessonsPerPart']; $day++) { ?>
                            <?php
                            $duration = number_format($videos["W{$week}D{$day}"]['totalDuration'] / 360000, 2);
                                $average = $averages["W{$week}D{$day}"]['users'] != 0 ? number_format($averages["W{$week}D{$day}"]['time'] / $averages["W{$week}D{$day}"]['users'], 2) : '0';
                                $viewed = number_format($person["W{$week}D{$day}"]['time'], 2);
                                ?>
                            <tr>
                                <td style="--size: <?= $duration ?>" title="<?= $duration ?>"> <span class="data"><?= $duration ?></span></td>
                                <td style="--size: <?= $average ?>"  title="<?= $average ?>" > <span class="data"><?= $average ?> </span></td>
                                <td style="--size: <?= $viewed ?>"   title="<?= $viewed ?>"  > <span class="data"><?= $viewed ?>  </span></td>
                            </tr>
                            <?php } ?>

                        </table>
                    </div>
                    <?php } ?> 
                </div>
        </div>
    </main>
</body>

</html>
