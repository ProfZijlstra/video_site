<!DOCTYPE html>
<html>
    <head>
        <title><?= $block ?> <?= $day ?> Stats</title>
        <meta charset="utf-8" />
        <meta name=viewport content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="res/css/lib/font-awesome-all.min.css">
        <link rel="stylesheet" href="res/css/common-1.3.css">
        <link rel="stylesheet" href="res/css/adm-1.0.css">
        <link rel="stylesheet" href="res/css/lib/charts.css">
        <link rel="stylesheet" href="res/css/colCharts.css">
    </head>

    <body>
        <?php include 'header.php'; ?>
        <main>
            <nav class="areas">
                <div title="Back">
                    <a href="<?= "{$MY_BASE}/$course/$block/chart" ?>">
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>
                </div>
            </nav>
            <?php if (hasMinAuth('instructor')) { ?>
            <nav class="tools">
                <a href="userChart">
                    <i title="User Statistics" class="fa-solid fa-users"></i>
                </a>
            </nav>
            <?php } ?>

            <div id="content">
                <ul class="charts-css legend legend-square legend-inline">
                    <li>Video Duration</li>
                    <li>Average View Time</li>
                    <li>Your Viewed Time</li>
                </ul>
                <table class="charts-css column multiple show-labels">
                    <?php foreach ($videos['videos'] as $idx => $data) { ?>
                    <?php
                        $duration = number_format($videos['videos'][$idx]['duration'] / 360000, 2);
                        $average = $averages[$idx]['users'] != 0 ? number_format($averages[$idx]['time'] / $averages[$idx]['users'], 2) : '0';
                        $viewed = number_format($person[$idx]['time'], 2);
                        ?>
                    <tr>
                        <th scope="row"><?= $videos['videos'][$idx]['parts'][1] ?></th>
                        <td style="--size: calc(<?= $duration ?> / <?= $max ?>)" title="<?= $duration ?>"> 
                        </td>
                        <td style="--size: calc(<?= $average ?> / <?= $max ?>)"  title="<?= $average ?>" > 
                            <?php if (hasMinAuth('instructor') && $averages[$idx]['users']) { ?>
                            <span title="Amount of users for average">
                            <?= $averages[$idx]['users'] ?>
                            </span>
                            <?php } ?>
                        </td>
                        <td style="--size: calc(<?= $viewed ?> / <?= $max ?>)"   title="<?= $viewed ?>"  >
                        </td>
                    </tr>
                    <?php } ?>
                </table>
                <?php if ($total) { ?>
                <div class="totals">
                    <span title="Users"><i class="fa-regular fa-user"></i> <?= $total['users'] ?></span> 
                    <span title="Views"><i class="fa-regular fa-eye"></i> <?= $total['views'] ?></span>
                    <span title="Hours"><i class="fa-regular fa-clock"></i> <?= $total['time'] ?></span>
                </div>
                <?php } ?>
            </div>
        </main>
    </body>

</html>
