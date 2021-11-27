<?php if ($_SESSION['user']['type'] === 'admin') : ?>
<!DOCTYPE html>
<html>
    <head>
        <title><?= $course ?> Videos</title>
        <meta charset="utf-8" />
        <meta name=viewport content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="res/css/font-awesome-all.min.css">
		<link rel="stylesheet" href="res/css/offering.css">
		<link rel="stylesheet" href="res/css/adm.css">
        <style>
            th.name, td.name {
                width: 275px;
            }
            td.num {
                text-align: right;
            }
        </style>
    </head>
    <body>
        <header>
			<div id="controls" data-id="<?= $_SESSION['user']['id'] ?>">
				<a href="logout"><i title="Logout" class="fas fa-power-off"></i></a>
			</div>
            <div id="course">
                <a href=".." id="course_num"><?= strtoupper($course) ?>
                    <span data-id="<?= $offering['id']?>" id="offering"> <?= $offering['block'] ?> </span>
                </a>
            </div>
            <h1>
                <span class="title" >
					<?= $title ?> 
				</span>
            </h1>
        </header>
        <main>
            <div id="content">
            <h2>Views for <?= $user["firstname"] ?> <?= $user["lastname"]?></h2>
            <?php foreach($days as $day) : ?>
            <table id="<?= $day["day"]["abbr"] ?>">
                <caption><?= $day["day"]["abbr"] ?> <?= $day["day"]["description"] ?></caption>
                <thead>
                    <tr>
                        <th class="name">Video</th>
                        <th>Duration</th>
                        <th>Views</th>
                        <th>PDF</th>
                        <th>Hours</th>
                        <th>Too Long</th>
                        <th>Inc Long</th>
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
                            <td class="num"><?= $video["too_long"]?></td>
                            <td class="num"><?= $video["hours_long"]?></td>
                        </tr>
                    <?php endforeach; ?> 
                <tbody>
            </table>
            <?php endforeach; ?>
            </div>
        </main>
    </body>
</html>
<?php endif; ?>
