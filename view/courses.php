<!DOCTYPE html>
<html>
    <head>
        <title>Course Offerings</title>
        <meta charset="utf-8" />
        <meta name=viewport content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="res/css/font-awesome-all.min.css">
        <link rel="stylesheet" href="res/css/common.css">
		<link rel="stylesheet" href="res/css/adm.css">
        <style>
            main div#content {
                margin: 50px auto;
                width: 750px;
            }

            div#content div.offering {
                border-bottom: 1px solid black;
                padding: 20px;
            }
            div.offering span {
                display: inline-block;
            }
            div.offering span.number {
                min-width: 60px;
            }
            div.offering span.block { 
                min-width: 80px;
            }
            span.faculty {
                float: right;
            }
            @media screen and (max-width: 900px) {
                .fa-flask-vial, .fa-users {
                    display: none;
                }
                div#controls {
                    top: 22px;
                    right: 15px;
                }
                main div#content {
                    width: 90%;
                }
                div#content div.offering {
                    text-align: center;
                }
                div.offering span {
                    display: inline;
                }
                div.offering span.name {
                    display: block;
                }
                span.faculty {
                    float: none;
                }
            }
        </style>
    </head>
    <body>
        <?php include("header.php"); ?>
        <main>
            <div id="content">

            <?php foreach($offerings as $offering): ?>
                <div class="offering">
                    <a href="<?= $offering["number"] . "/" . $offering["block"] . "/" ?>">
                        <span class="number"><?= strtoupper($offering["number"]) ?></span>
                        <span class="block"><?= $offering["block"]?></span>
                        <span class="name"><?= $offering["name"]?></span>
                        <span class="faculty"><?= $offering["knownAs"] ?> <?= $offering["lastname"] ?></span>
                    </a>
                </div>
            <?php endforeach; ?>

            </div>
        </main>
    </body>
</html>