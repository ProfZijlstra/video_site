<!DOCTYPE html>
<html>
    <head>
        <title><?= $course ?> Videos</title>
        <meta charset="utf-8" />
        <meta name=viewport content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="res/css/lib/font-awesome-all.min.css">
        <link rel="stylesheet" href="res/css/common-1.1.css">
		<link rel="stylesheet" href="res/css/adm.css">
        <style>
            th.name, td.name {
                width: 275px;
            }
            td.num {
                text-align: right;
            }
        </style>
        <script src="res/js/back.js"></script>
    </head>
    <body>
        <?php include("header.php");?>
        <main>
            <nav class="back" title="Back">
                <i class="fa-solid fa-arrow-left"></i>
            </nav>
            <div id="content">
            <h2>Views for <?= $user["firstname"] ?> <?= $user["lastname"]?></h2>
            <?php foreach($days as $day) : ?>
                <?php if ($day["day"]["abbr"]): ?>
            <table id="<?= $day["day"]["abbr"] ?>">
                <caption><?= $day["day"]["abbr"] ?> <?= $day["day"]["description"] ?></caption>
                <thead>
                    <tr>
                        <th class="name">Video</th>
                        <th>Duration</th>
                        <th>Views</th>
                        <th>PDF</th>
                        <th>Hours</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($day["video"]["file_info"] as $name => $video) : ?>
                        <tr>
                            <td class="name"><?= $name ?></td>
                            <td class="num"><?= number_format($video["duration"] / 360000,4) ?></td>
                            <td class="num"><?= $video["video_views"]?></td>
                            <td class="num"><?= $video["pdf"]?></td>
                            <td class="num"><?= $video["hours"]?></td>
                        </tr>
                    <?php endforeach; ?> 
                <tbody>
            </table>
                <?php endif; ?>
            <?php endforeach; ?>
            </div>
        </main>
    </body>
</html>