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
        #back {
            cursor: pointer;
        }
    </style>
    <script>
        window.addEventListener("load", () => {
            document.getElementById("back").onclick = () => {
                window.history.go(-1);
            };

            const present = document.getElementById("present");
            if (present) {
                present.onclick = (evt) => {
                    if (evt.target.tagName === "INPUT") {
                        doUpdate(evt);
                    } 
                };
            }
            function doUpdate(evt) {
                const tr = evt.target.parentNode.parentNode;
                const id = tr.dataset.id;
                const boxes = tr.getElementsByTagName("input");
                const update = {
                    "id": id, "late":0, "mid":0, "left":0, "phys":0
                };
                for (const box of boxes) {
                    if (box.checked) {
                        const name = box.getAttribute("name");
                        update[name] = 1;
                    }
                }
                console.log(update);

                fetch(`attend/${id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(update)
                });
            }

            document.getElementById("regen").onclick = () => {
                const boxes = present.getElementsByClassName("phys");
                let has_phys = false;
                for (const box of boxes) {
                    if (box.checked) {
                        has_phys = true;
                        break;
                    }
                }
                if (has_phys && !confirm("Regenerate and delete all physical attendance?")) {
                    return false;
                }
                return true;
            };
        });
    </script>
</head>

<body>
    <header>
        <div id="controls" data-id="<?= $_SESSION['user']['id'] ?>">
            <a href="/videos/user" title="Users"><i class="fas fa-users"></i></a>
            <a href="logout" title="Logout"><i title="Logout" class="fas fa-power-off"></i></a>
        </div>
        <div id="course">
            <i class="fas fa-arrow-left" id="back"></i>
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

            <!-- Absent -->
            <?php if ($absent) : ?>
                <h3>Absent</h3>
                <table>
                    <?php foreach ($absent as $missing) : ?>
                        <tr>
                            <td><a href="../../../user/<?= $missing["teamsName"] ?>"><?= $missing["teamsName"] ?></a></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>

            <!-- Present -->
            <?php if ($present) : ?>
                <h3>Present</h3>
                <table id="present">
                    <tr>
                        <th>Name</th>
                        <th title="Arrived Late">Late</th>
                        <th title="Missed Middle">MisMid</th>
                        <th title="Left Early">Left</th>
                        <th title="In Physical Room">Phys</th>
                    </tr>
                    <?php foreach ($present as $student) : ?>
                        <tr data-id="<?= $student["id"] //is attendance id, not student id ?>">
                            <td>
                                <a href="../../../user/<?= $student["teamsName"] ?>"><?= $student["teamsName"] ?></a>
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
                                <input type="checkbox" name="phys" value="phys" class="phys" <?= $student["inClass"] ? "checked" : "" ?> />
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>

        </div>
    </main>
</body>

</html>