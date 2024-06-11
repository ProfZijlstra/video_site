<!DOCTYPE html>
<html>

<head>
    <title>Attendance Export</title>
    <meta charset="utf-8" />
    <meta name=viewport content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="res/css/lib/font-awesome-all.min.css">
    <link rel="stylesheet" href="res/css/common-1.3.css">
    <link rel="stylesheet" href="res/css/adm.css">
    <style>
        .session input[type=text] {
            width: 665px;
        }

        .session label {
            display: inline-block;
            width: 70px;
            text-align: left;
        }

        .cbox {
            text-align: center;
        }

        #content h3 {
            margin-bottom: 5px;
            position: relative;
        }

        #content h3 span {
            cursor: pointer;
            position: absolute;
            top: 5px;
            right: 0px;
        }
    </style>
    <script src="res/js/attendanceExport.js"></script>
    <script src="res/js/ensureSaved.js"></script>
</head>

<body>
    <?php include("header.php"); ?>
    <main>
        <nav class="back" title="Back">
            <a href="../../attendance"><i class="fa-solid fa-arrow-left"></i></a>
        </nav>
        <div id="content">
            <div class="error">
                <?= $error ?>
            </div>
            <!-- General Session Info here -->
            <div class="session" id="session" data-stype="<?= $stype ?>" data-day="<?= $day_abbr ?>">
                <h3>Session Details:</h3>
                <div>
                    <label>Status</label>
                    <input disabled type="text" name="title" value="<?= $session["status"] ?>" />
                </div>
                <div>
                    <label>Meetings</label>
                    <input disabled type="text" name="generated" value="<?= $session["generated"] ?>" />
                </div>
                <div>
                    <label>Date</label>
                    <input disabled type="text" value="<?= $date ?>" />
                </div>
                <div>
                    <label>Start</label>
                    <input disabled type="text" name="start" value="<?= $session["start"] ?>" />
                </div>
                <div>
                    <label>Stop</label>
                    <input disabled type="text" name="stop" value="<?= $session["stop"] ?>" />
                </div>
            </div>
            <form name="regenForm" method="post" action="<?= $stype ?>"></form>


            <h3>
                Export Data
                <span>
                    <i id="regenBtn" title="Regenerate Report" class="fa-solid fa-rotate-right"></i>
                    <i id="exportBtn" title="Export to CAMS" class="fa-solid fa-cloud-arrow-up"></i>
                </span>
            </h3>
            <table id="data">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th>InClass</th>
                    <th>Comments</th>
                </tr>
                <?php foreach ($exports as $export) : ?>
                    <tr data-id="<?= $export['id'] ?>">
                        <td><?= $export['studentID'] ?></td>
                        <td title="<?= $export['knownAs'] ?>">
                            <?= trim($export['lastname']) . ', ' . $export['firstname'] ?>
                        </td>
                        <td><?= $export['status'] ?></td>
                        <td class="cbox">
                            <input class="inClass" type="checkbox" <?= $export['inClass'] ? 'checked' : '' ?> />
                        </td>
                        <td>
                            <input class="comment" type="text" value="<?= $export['comment'] ?>">
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </main>
    <div id="overlay">
        <i id="close-overlay" class="fas fa-times-circle"></i>
        <div id="exportModal" class="modal">
            <h3>Export Attendance to CAMS</h3>
            <form action="export" method="post">
                <div>
                    <label>CAMS Pwd</label>
                    <input id="camsPwd" type="password" name="password" placeholder="Your CAMS Password" />
                </div>
                <div>
                    <label>Session Type</label>
                    <select name="stype" id="stype">
                        <option>AM</option>
                        <option>PM</option>
                        <option>SAT</option>
                    </select>
                </div>
                <div>
                    <label>Date</label>
                    <input id="date" type="date" name="date" value="<?= $date ?>" />
                </div>
                <div>
                    <label>Start</label>
                    <input id="start" type="time" name="start" value="<?= $session["start"] ?>" />
                </div>
                <div>
                    <label>Stop</label>
                    <input id="stop" type="time" name="stop" value="<?= $session["stop"] ?>" />
                </div>

                <div id="doExport" class="btn">
                    <i id="exportSpinner" class="fa-solid fa-circle-notch"></i>
                    <button>Export</button>
                </div>
            </form>
        </div>
    </div>

</body>

</html>
