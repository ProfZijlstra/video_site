<!DOCTYPE html>
<html>
    <head>
        <title><?= $title ?></title>
        <meta charset="utf-8" />
        <meta name=viewport content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="res/css/lib/font-awesome-all.min.css">
        <link rel="stylesheet" href="res/css/common-1.3.css">
        <link rel="stylesheet" href="res/css/adm-1.0.css">
        <link rel="stylesheet" href="res/css/lib/charts.css">
        <style>
        table.charts-css.bar {
            --labels-size: 150px;
        }
        table.charts-css.bar tr td {
            min-height: 35px;
        }
        table.observers {
            margin-top: 2em;
        }
        h3 {
            margin-bottom: 0px;
        }
        </style>
    </head>

    <body>
        <?php include 'header.php'; ?>
        <main>
            <nav class="areas">
                <div title="Back">
                    <a href="chart">
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>
                </div>
            </nav>

            <div id="content">
                <table class="charts-css bar show-labels show-heading">
                    <caption>Students</caption>
                    <?php foreach ($points as $user_id => $point) { ?>
                        <?php if (isset($students[$user_id])) { ?>
                        <tr>
                            <th scope="row" title="<?= $students[$user_id]['firstname'].' '.$students[$user_id]['lastname'] ?>">
                                <a href="chart/<?= $user_id ?>">
                                    <?= explode(' ', $students[$user_id]['knownAs'])[0] ?>
                                </a>
                            </th>
                            <td style="--size: calc(<?= $point ?> / <?= $max ?>)" title="<?= $point ?>"></td>
                        </tr>
                        <?php } ?>
                    <?php } ?>
                </table>
            </div>
        </main>
    </body>

</html>
