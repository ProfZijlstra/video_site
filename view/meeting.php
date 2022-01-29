<!DOCTYPE html>
<html>

<head>
    <title><?= $offering['block'] ?> Attendance</title>
    <meta charset="utf-8" />
    <meta name=viewport content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="res/css/font-awesome-all.min.css">
    <link rel="stylesheet" href="res/css/offering.css">
    <link rel="stylesheet" href="res/css/adm.css">
    <style>
        h3 {
            margin-bottom: 0px;
        }
        label {
            display: inline-block;
            width: 100px;
        }
        input[type=text],
        input[type=date] {
            width: 638px;
        }
        div.btns {
            margin-top: 5px;
            text-align: right;
        }
        td.cbox {
            width: 50px;
            text-align: center;
        }
        .btns button {
            width: 200px;
        }
        td.name {
            position: relative;
        }
        td.name span.right {
            position: absolute;
            right: 3px;
            color: gray;
            cursor: pointer;
        }
        td.student_id {
            text-align: center;
        }
        .meeting {
            position: relative;
        }
        #delete_meeting {
            position: absolute;
            top: 0px;
            right: 0px;
            cursor: pointer;
        }
    </style>
    <script src="res/js/meeting.js"></script>
</head>

<body>
    <header>
        <div id="controls" data-id="<?= $_SESSION['user']['id'] ?>">
            <a href="/videos/user" title="Users"><i class="fas fa-users"></i></a>
            <a href="logout" title="Logout"><i title="Logout" class="fas fa-power-off"></i></a>
        </div>
        <div id="course">
            <div id="course">
                <a href=".."><?= strtoupper($course) ?> <?= $block ?></a>
            </div>
        </div>
        <h1>
            <span class="title">
                Meeting: <?= $meeting["title"] ?>
            </span>
        </h1>
    </header>
    <main>
        <div id="content">
            <!-- General Meeting Info here -->
            <div class="meeting">
                <h3>Meeting Details:</h3>
                <form id="delete_form" method="post" action="<?= $meeting["id"]?>/delete"> 
                    <i id="delete_meeting" class="far fa-trash-alt"></i>
                </form>
                <form method="post">
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
                    <div>
                        <label>Weight</label>
                        <input type="text" name="weight" value="<?= $meeting["sessionWeight"] ?>" />
                    </div>
                    <div class="btns">
                        <button type="submit">Update</button>
                    </div>
                </form>
            </div>

            <div class="btns">
                <form method="post" action="regen/<?= $meeting["id"] ?>">
                    <input type="hidden" name="offering_id" value="<?= $offering_id ?>" />
                    <input type="hidden" name="meeting_id" value="<?= $meeting["id"] ?>" />
                    <input type="hidden" name="start" value="<?= $meeting["start"]?>" />
                    <input type="hidden" name="stop" value="<?= $meeting["stop"]?>" />
                    <button id="regen">Regenerate Report</button>
                </form>
            </div>

            <!-- Absent -->
            <?php if ($absent) : ?>
                <form id="presentForm" method="post" action="<?= $meeting["id"]?>/present">
                    <input id="present_id" type="hidden" name="attendance_id" value="" />
                </form>
                <h3>Absent</h3>
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
                <form id="absentForm" method="post" action="<?= $meeting["id"]?>/absent">
                    <input id="absent_id" type="hidden" name="attendance_id" value="" />
                </form>
                <h3>Present</h3>
                <table id="present">
                    <tr>
                        <th title="Student ID">ID</th>
                        <th>Name</th>
                        <th title="Arrived Late">Late</th>
                        <th title="Missed Middle">MisMid</th>
                        <th title="Left Early">Left</th>
                        <th title="Excused">Excu</th>
                        <th title="In Physical Room">Phys</th>
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
                            <td class="cbox" title="Arrived Late">
                                <input type="checkbox" name="late" value="late" <?= $student["arriveLate"] ? "checked" : "" ?> />
                            </td>
                            <td class="cbox" title="Missed Middle">
                                <input type="checkbox" name="mid" value="mid" <?= $student["middleMissing"] ? "checked" : "" ?> />
                            </td>
                            <td class="cbox" title="Left Early">
                                <input type="checkbox" name="left" value="left" <?= $student["leaveEarly"] ? "checked" : "" ?> />
                            </td>
                            <td class="cbox" title="Excused">
                                <input type="checkbox" name="excu" value="excu" <?= $student["excused"] ? "checked" : "" ?> />
                            </td>
                            <td class="cbox" title="In Physical Room">
                                <input type="checkbox" name="phys" value="phys" class="phys" <?= $student["inClass"] ? "checked" : "" ?> />
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