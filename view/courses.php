<!DOCTYPE html>
<html>
    <head>
        <title><?= $course ?> Videos</title>
        <meta charset="utf-8" />
        <meta name=viewport content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="res/css/font-awesome-all.min.css">
        <link rel="stylesheet" href="res/css/common.css">
		<link rel="stylesheet" href="res/css/adm.css">
        <style>
            header #course {
                display: none;
            }
            .course {
                border: 1px solid black;
                margin-bottom: 3em;
            }
            .course label {
                display: inline-block;
                width: 60px;
            }
            .course .title {
                font-weight: bold;
                font-size: 30px;
            }
            .course .title, .latest, .offerings {
                padding: 5px;
                border-bottom: 1px solid black;
            }
            .offerings {
                border-bottom: none;
            }
            #content a {
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
                #content {
                    width: 100%;
                }
                #course_name {
                    display: none;
                }
            }
        </style>
    </head>
    <body>
        <?php include("header.php"); ?>
        <main>
            <div id="content">
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