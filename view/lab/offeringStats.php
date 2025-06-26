<!DOCTYPE html> <?php global $MY_BASE ?>
<html>
    <head>
        <title><?= $block ?> Stats</title>
        <meta charset="utf-8" />
        <meta name=viewport content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/lib/font-awesome-all.min.css">
        <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/common-1.3.css">
        <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/adm-1.0.css">
        <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/lib/charts.css">
        <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/colCharts.css">
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
                const parts = window.location.href.split('/');
                const last = parts.pop();
                let location = `${this.getAttribute('data-href')}/userChart`;
                if (last.match(/^\d+$/)) {
                    location = "../" + location;
                }
                window.location.href = location;
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
                    <a href="<?= "{$MY_BASE}/$course/$block/stat" ?>">
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>
                </div>
            </nav>
            <?php if (hasMinAuth('instructor')) { ?>
            <nav class="tools">
                <a href="<?= $type == 'normal' ? 'userChart' : '../userChart'?> ">
                    <i title="User Statistics" class="fa-solid fa-users"></i>
                </a>
            </nav>
            <?php } ?>

            <div id="content">
                <ul class="charts-css legend legend-square legend-inline">
                    <li>Possible</li>
                    <li>Average</li>
                    <li>Your</li>
                </ul>
                <div class="stats">
                    <?php for ($week = 1; $week <= $offering['lessonParts']; $week++) { ?>
                    <div>
                        <table class="charts-css column multiple show-heading show-labels">
                            <caption><?= "W$week" ?></caption>
                            <?php for ($day = 1; $day <= $offering['lessonsPerPart']; $day++) { ?>
                            <?php
                                $avaiable = number_format($possible["W{$week}D{$day}"]['points'], 2);
                                $average = $averages["W{$week}D{$day}"]['points'] != 0 ? number_format($averages["W{$week}D{$day}"]['points'], 2) : '0';
                                $your = number_format($person["W{$week}D{$day}"]['points'], 2);
                                ?>
                            <tr data-href="<?= "W{$week}D{$day}" ?>"}>
                                <th><?= "D$day" ?></th>
                                <td style="--size: calc(<?= $avaiable ?> / <?= $max ?>)" title="<?= $avaiable ?>"> 
                                </td>
                                <td style="--size: calc(<?= $average ?> / <?= $max ?>)"  title="<?= $average ?>" > 
                                    <?php if ($type == 'normal' && hasMinAuth('instructor') && $averages["W{$week}D{$day}"]['users']) { ?>
                                    <span><a title="Amount of users for average" href="<?= "W{$week}D{$day}/userChart" ?>"}>
                                    <?= $averages["W{$week}D{$day}"]['users'] ?>
                                    </a></span>
                                    <?php } ?>
                                </td>
                                <td style="--size: calc(<?= $your ?> / <?= $max ?>)"   title="<?= $your ?>"  >
                                </td>
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
