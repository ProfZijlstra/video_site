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
            h1 {
                text-align: center;
                font-size: 45px;
                margin-bottom: 0px;
            }
            .course {
                border: 1px solid black;
                margin-bottom: 3em;
            }
            .course label {
                display: inline-block;
                width: 60px;
            }
            .title {
                font-weight: bold;
                font-size: 30px;
            }
            .title, .latest, .offerings {
                padding: 5px;
                border-bottom: 1px solid black;
            }
            .offerings {
                border-bottom: none;
            }
            #tables a {
                display: inline-block;
                border: 1px solid black;
                border-radius: 5px;
                padding: 1px 6px;
                font-family: monospace;
                font-size: 15px;
                margin-left: 5px;
                background: linear-gradient(to bottom, #eee 0%,#ccc 100%) 
            }
            @media screen and (max-width: 900px) {
                #tables {
                    width: 100%;
                }
                #course_name {
                    display: none;
                }
            }
        </style>
    </head>
    <body>
        <header>
			<div id="controls" data-id="<?= $_SESSION['user']['id'] ?>">
            <?php if ($_SESSION['user']['type'] === 'admin') : ?>
                <a href="/videos/user"><i class="fas fa-users"></i></a>
            <?php endif; ?>
				<a href="logout"><i class="fas fa-power-off"></i></a>
			</div>
            <h1>Courses</h1>
        </header>
        <main>
            <div id="tables">
            <?php foreach ($courses as $course) : ?>
            <div class="course" id="<?= $course["number"] ?>">
                <div class="title">
                    <?= strtoupper($course["number"]) ?>
                    <span id="course_name">: <?= $course["name"] ?></span>
                </div>
                <div class="latest">
                    <label>Latest:</label> 
                    <a class="offering" href="<?= $course["number"]?>/<?= $latest[$course["number"]] ?>/">
                        <?= $latest[$course["number"]] ?>
                    </a>
                </div>
                <div class="offerings">
                    <label>Other:</label> 
                <?php foreach($course_offerings[$course["number"]] as $offering) : ?>
                    <?php if ($latest[$course["number"]] != $offering["block"]) : ?>
                    <a class="offering" href="<?= $offering["course_number"]?>/<?= $offering["block"]?>/">
                        <?= $offering["block"]?>
                    </a>
                    <?php endif; ?>
                <?php endforeach; ?>    
                </div>
            </div>
            <?php endforeach; ?>
            </div>
        </main>
    </body>
</html>