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
    </head>

    <body>
        <?php include 'header.php'; ?>
        <main>
            <nav class="areas">
                <div title="Back">
                    <a href="../chart">
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
                        <th scope="row"><?= $students[$view['user_id']]['knownAs'] ?></th>
                        <td style="--size: <?= $view['time']?>" title="<?= $view['time'] ?>"></td>
                    </tr>
                    <?php } ?>
                    <?php } ?>
                </table>
                <?php if ($observers) { ?>
                <table class="charts-css bar show-labels show-heading">
                    <caption>Observers</caption>
                    <?php foreach ($views as $view) { ?>
                    <?php if (isset($observers[$view['user_id']])) { ?>
                    <tr>
                        <th scope="row"><?= $observers[$view['user_id']]['knownAs'] ?></th>
                        <td style="--size: <?= $view['time']?>" title="<?= $view['time'] ?>"></td>
                    </tr>
                    <?php } ?>
                    <?php } ?>
                </table>
                <?php } ?>
            </div>
        </main>
    </body>

</html>
