<!DOCTYPE html> <?php global $MY_BASE ?>
<html>
    <head>
        <title><?= $block ?> Data</title>
        <meta charset="utf-8" />
        <meta name=viewport content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/lib/font-awesome-all.min.css">
        <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/common-1.3.css">
        <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/adm-1.0.css">
        <style>
        div#content {
            width: 900px;

            table {
                width: 100%;
                border-collapse: collapse;
            }
        }
        </style>
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

            <div id="content">
                <table>
                    <tr>
                        <th>Start</th>
                        <th>User</th>
                        <?php if ($type != 'day') { ?>
                        <th>Day</th>
                        <?php } ?>
                        <th>Video</th>
                        <th>Duration</th>
                        <th>Speed</th>
                    </tr>
                    <?php foreach ($data as $d) { ?>
                    <tr>
                        <td><?= $d['start'] ?></td>
                        <td title="<?= $users[$d['user_id']]['firstname'] ?> <?= $users[$d['user_id']]['lastname'] ?>">
                            <?= $users[$d['user_id']]['knownAs'] ?>
                        </td>
                        <?php if ($type != 'day') { ?>
                        <td><?= $d['abbr'] ?></td>
                        <?php } ?>
                        <td><?= $d['video'] ?></td>
                        <td><?= $d['time'] ?></td>
                        <td><?= $d['speed'] ?></td>
                    </tr>
                    <?php } ?>
                </table>
            </div>
        </main>
    </body>

</html>
