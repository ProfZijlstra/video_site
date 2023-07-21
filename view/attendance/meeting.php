<!DOCTYPE html>
<html>

<head>
    <title>Meeting: <?= $meeting["title"] ?></title>
    <meta charset="utf-8" />
    <meta name=viewport content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="res/css/lib/font-awesome-all.min.css">
    <link rel="stylesheet" href="res/css/common-1.1.css">
    <link rel="stylesheet" href="res/css/adm.css">
    <link rel="stylesheet" href="res/css/meeting-1.2.css">
   <script src="res/js/meeting-1.5.js"></script>
   <script src="res/js/lib/html5-qrcode.min.js"></script>
   <script src="res/js/sounds.js"></script>
   <script src="res/js/ensureSaved.js"></script>
</head>

<body>
    <?php include("header.php"); ?>
    <main>
        <nav class="back" title="Back">
            <i class="fa-solid fa-arrow-left"></i>
        </nav>

        <nav class="tools">
            <span class="iconContainer" id="barcodeReader" title="Start/Stop Camera Reader">
                <div class="stripe"></div>
                <i class="fa-solid fa-camera"></i>
            </span>
            <span class="iconContainer" id="barcodeScanner" title="Start/Stop Barcode Scanner">
                <div class="stripe"></div>
                <i class="fa-solid fa-barcode"></i>
            </span>
        </nav>

        <div id="scannerContainer" class="sideContainer hide">
            <div><i class="fa-solid fa-barcode"></i></div>
            <div><input id="barcode" placeholder="barcode number" /></div>
        </div> 

        <div id="readerContainer" class="sideContainer hide">
            <div id="rotate" class="hide">
                <i class="fas fa-sync"></i>
                <div id="camera_icon">
                    <i class="fas fa-camera"></i>
                </div>
            </div>
            <div id="readerStripe"></div>
            <div id="reader"></div>
        </div>

        <div id="msgContainer" class="hidden">
            <div id="attendMsg" class="msg hidden">
                Thanks <strong id="physicallyPresent"></strong> your attendance is recorded
            </div>
            <div id="registerMsg" class="msg hidden">
                <strong>Unknown badge:</strong> <span id="unknownBadge"></span>
                Register it to a user by marking them present
                <button id="cancelRegister">Cancel</button>
            </div>
        </div>

        <div id="content" class="">
            <!-- General Meeting Info here -->
            <div class="meeting">
                <h3>
                    Meeting Details:
                    <form id="regen_form" method="post" action="regen/<?= $meeting["id"] ?>">
                        <input type="hidden" name="session_id" value="<?= $meeting['session_id'] ?>" />
                        <input type="hidden" name="meeting_id" value="<?= $meeting["id"] ?>" id="meeting_id"/>
                        <input type="hidden" name="start" value="<?= $meeting["start"]?>" />
                        <input type="hidden" name="stop" value="<?= $meeting["stop"]?>" />
                        <i id="regen_meeting" class="fa-solid fa-rotate-right" title="Renerate Meeting Report"></i>
                    </form>

                    <form id="delete_form" method="post" action="<?= $meeting["id"]?>/delete"> 
                        <i id="delete_meeting" class="far fa-trash-alt" title="Delete Meeting"></i>
                    </form>
                </h3>
                <form method="post" id="meeting_form">
                    <input type="hidden" name="id" value="<?= $meeting["id"] ?>">
                    <div>
                        <label>Title</label>
                        <input type="text" name="title" value="<?= $meeting["title"] ?>" />
                    </div>
                    <div>
                        <label>Date</label>
                        <input type="date" name="date" value="<?= $meeting["date"] ?>" />
                    </div>
                    <div>
                        <label>Start</label>
                        <input type="text" name="start" value="<?= $meeting["start"] ?>" />
                    </div>
                    <div>
                        <label>Stop</label>
                        <input type="text" name="stop" value="<?= $meeting["stop"] ?>" />
                    </div>
                </form>
            </div>

            <div class="btns">
            </div>
            <form id="absentForm" method="post" action="<?= $meeting["id"]?>/absent">
                <input id="absent_id" type="hidden" name="attendance_id" value="" />
            </form>
            <form id="presentForm" method="post" action="<?= $meeting["id"]?>/present">
                <input id="present_id" type="hidden" name="attendance_id" value="" />
            </form>


            <!-- Absent -->
            <?php if ($absent) : ?>
                <h3>
                    Absent
                    <i id="email_absent" class="far fa-paper-plane" title="Email Unexcused Absent"></i>
                </h3>
                <table>
                    <tr>
                        <th>Name</th>
                        <th>Excu</th>
                    </tr>
                    <?php foreach ($absent as $missing) : ?>
                        <tr data-id="<?= $missing["id"] //is attendance id ?>" id="<?= $missing["id"] ?>">
                            <td class="name">
                                <a href="../../../user/<?= $missing["teamsName"] ?>"><?= $missing["teamsName"] ?></a>
                                <span class="right present">present</span>
                            </td>
                            <td class="cbox" title="Excused">
                                <input class="absent_excused" type="checkbox" name="excu" value="excu" <?= $missing["excused"] ? "checked" : "" ?> />
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>

            <!-- Present -->
            <?php if ($present) : ?>
                <h3>
                    Present
                    <i id="email_tardy" class="far fa-paper-plane" title="Email Unexcused Tardy"></i>
                </h3>
                <table id="present">
                    <tr>
                        <th title="Student ID">ID</th>
                        <th>Name</th>
                        <th title="Arrived at">Start</th>
                        <th title="Left at">Stop</th>
                        <th title="Arrived Late">Late</th>
                        <th title="Missed Middle">MisMid</th>
                        <th title="Left Early">Left</th>
                        <th title="In Physical Room">Phys</th>
                        <th title="Excused">Excu</th>
                    </tr>
                    <?php foreach ($present as $student) : ?>
                        <tr data-id="<?= $student["id"] //is attendance id, not student id ?>" id="<?= $student["id"]?>">
                            <td class="student_id">
                                <a href="../../../user/<?= $student["teamsName"] ?>"><?= $student["studentID"] ?></a>
                            </td>
                            <td class="name" title="<?= $student["start"] . " - " . $student["stop"] ?>">
                                <a href="../../../user/<?= $student["teamsName"] ?>"><?= $student["teamsName"] ?></a>
                                <span class="right absent">absent</span>
                            </td>
                            <td>
                                <input class="time start" value="<?= $student["start"] ?>">
                            </td>
                            <td>
                                <input class="time stop" value="<?= $student["stop"] ?>">
                            </td>
                            <td class="cbox" title="Arrived Late">
                                <input type="checkbox" name="late" value="late" <?= $student["arriveLate"] ? "checked" : "" ?> />
                            </td>
                            <td class="cbox" title="Missed Middle">
                                <input type="checkbox" name="mid" value="mid" <?= $student["middleMissing"] ? "checked" : "" ?> />
                            </td>
                            <td class="cbox" title="Left Early">
                                <input type="checkbox" name="left" value="left" <?= $student["leaveEarly"] ? "checked" : "" ?> />
                            </td>
                            <td class="cbox" title="In Physical Room">
                                <input id="<?= $student["badge"] ?>" type="checkbox" name="phys" value="phys" class="phys" <?= $student["inClass"] ? "checked" : "" ?> />
                            </td>
                            <td class="cbox" title="Excused">
                                <input type="checkbox" name="excu" value="excu" <?= $student["excused"] ? "checked" : "" ?> />
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>

            <!-- Visitors -->
            <?php if ($visitors) : ?>
                <h3>Not Enrolled</h3>
                <table>
                    <?php foreach ($visitors as $visitor) : ?>
                        <tr>
                            <td><?= $visitor["teamsName"] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>

        </div>
    </main>
</body>

</html>