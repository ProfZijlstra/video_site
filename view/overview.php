<?php
$now = mktime();
?>
<!DOCTYPE html>
<html>
    <head>
        <title><?= $course ?> Videos</title>
        <meta charset="utf-8" />
        <style>
            body {
                position: absolute;
                margin: 0px;
                width: 100%;
                height: 100%;
            }
            h1 {
                margin-top: 75px;
                text-align: center;
            }
            table {
                border-collapse: collapse;
                margin-left: auto;
                margin-right: auto;
            }
            td {
                cursor: pointer;
                border: 1px solid black;
                height: 150px;
                width: 150px;
                padding: 15px;
                text-align: center;
                position: relative;
                background: linear-gradient(to bottom, #eeeeee 0%,#cccccc 100%); 
            }
            .done {
                background: linear-gradient(to bottom, #ccc 0%,#aaa 100%); 
            }
            .curr {
                background: linear-gradient(to bottom, #ffc 0%,#cca 100%); 
            }
            time {
                display: block;
                margin-top: 10px;
                font-size: 75%;
                position: absolute;
                bottom: 3px;
                width: 130px;
                text-align: center;
            }
            a {
                text-decoration: none;
                color: black;
            }
        </style>
         <script>
            window.onload = function() {
                document.getElementById("days").onclick = function(e) {
                    if (e.target.tagName == "TD") {
                        e.target.querySelector("a").click();
                    }
                }
            };
         </script>
    </head>
    <body>
        <header>
            <h1><?= $course ?>: <?= $title ?></h1>
        </header>
        <main>
            <table id="days">
                <tr><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th><th>Sun</th></tr>
            <?php for ($w = 1; $w <= 4; $w++): ?>
                <tr>
                <?php for ($d = 1; $d <= 7; $d++): ?>
                    <?php $date = $start + ($w - 1)*60*60*24*7 + ($d - 1)*60*60*24; ?>
                    <td class="<?= $date < $now ? "done" : "" ?> <?= date("z", $date) == date("z", $now)? "curr" : ""?>">
                        <a href="W<?= $w ?>D<?= $d ?>">
                            <?= $days["W{$w}D{$d}"]["desc"] ?>
                            <time><?= date("M j Y", $date);?></time>
                        </a>
                    </td>
                <?php endfor ?>
                </tr>
            <?php endfor ?>
            </table>
        </main>
    </body>
</html>
