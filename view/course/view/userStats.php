<!DOCTYPE html>
<html>
    <head>
        <title><?= $block ?> User Stats</title>
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
                    <?php foreach ($views as $view) { ?>
                    <?php if (isset($students[$view['user_id']])) { ?>
                    <tr>
                        <th scope="row" title="<?= $students[$view['user_id']]['firstname'].' '.$students[$view['user_id']]['lastname'] ?>">
                            <?= explode(' ', $students[$view['user_id']]['knownAs'])[0] ?>
                        </th>
                        <td style="--size: calc(<?= $view['time']?> / <?= $max ?>)" title="<?= $view['time'] ?>"></td>
                    </tr>
                    <?php } ?>
                    <?php } ?>
                </table>
                <?php if ($no_view) { ?>
                <h3>Students with no views:</h3>
                <ul>
                    <?php foreach ($no_view as $id => $user) { ?>
                    <li><?= $students[$id]['firstname'].' '.$students[$id]['lastname'].' '.$id ?></li>
                    <?php } ?>
                </ul>
                <?php } ?>
                <?php if ($observers) { ?>
                <table class="charts-css bar show-labels show-heading observers">
                    <caption>Observers</caption>
                    <?php foreach ($views as $view) { ?>
                    <?php if (isset($observers[$view['user_id']])) { ?>
                    <tr>
                        <th scope="row" title="<?= $observers[$view['user_id']]['firstname'].' '.$observers[$view['user_id']]['lastname']?>">
                            <?= explode(' ', $observers[$view['user_id']]['knownAs'])[0] ?>
                        </th>
                        <td style="--size: calc(<?= $view['time']?> / <?= $max ?>)" title="<?= $view['time'] ?>"></td>
                    </tr>
                    <?php } ?>
                    <?php } ?>
                </table>
                <?php } ?>
            </div>
        </main>
    </body>

</html>
