<!DOCTYPE html> <?php global $MY_BASE ?>
<html>

    <head>
        <title><?= $block ?> Statistics</title>
        <meta charset="utf-8" />
        <meta name=viewport content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/lib/font-awesome-all.min.css">
        <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/common-1.3.css">
        <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/adm-1.0.css">
<style>
        div#statContainer {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            grid-auto-rows: 150px;
            gap: 20px;
        }
        div.stat {
            padding: 20px;
            text-align: center;
        }
        div.stat a {
            color: inherit;
            text-decoration: none;
        }
        div.stat i {
            font-size: 2em;
            margin-bottom: 10px;
        }

        @media (max-width: 900px) {
            div#content {
                margin-left: 100px;
                width: calc(100% - 100px);
            }
            div#statContainer {
                grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            }
            div.stat {
                padding: 15px;
            }
            div.stat i {
                font-size: 1.5em;
            }
        }
</style>
    </head>

    <body>
        <?php include 'header.php'; ?>
        <main>
            <?php include 'areas.php'; ?>

            <nav class="tools">
            </nav>

            <div id="content">
                <h1>Statistics</h1>
                <div id="statContainer">
                    <div class="stat">  
                        <a title="View Stats" href="chart">
                            <i class="fa-solid fa-eye"></i>
                            <div>View Stats</div>
                        </a>
                    </div>
                    <div class="stat">
                        <a title="Attendance Stats" href="attendance/chart">
                            <i class="fa-solid fa-user-check"></i>
                            <div>Attendance Stats</div>
                        </a>
                    </div>
                    <div class="stat">
                        <a title="Quiz Stats" href="quiz/chart">
                            <i class="fa-solid fa-vial"></i>
                            <div>Quiz Stats</div>
                        </a>
                    </div>
                    <div class="stat">
                        <a title="Lab Stats" href="lab/chart">
                            <i class="fa-solid fa-flask"></i>
                            <div>Lab Stats</div>
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </body>
</html>
