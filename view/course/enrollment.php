<!DOCTYPE html>
<html>

<head>
    <title><?= $block ?> Enrollment</title>
    <meta charset="utf-8" />
    <meta name=viewport content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="res/css/lib/font-awesome-all.min.css">
    <link rel="stylesheet" href="res/css/common-1.3.css">
    <link rel="stylesheet" href="res/css/adm.css">
    <style>
        #content {
            width: 1200px;
        }

        td.center {
            text-align: center;
        }

        p.error {
            color: red;
        }

        i.fa-gear {
            cursor: pointer;
        }

        i.fa-trash-can {
            position: absolute;
            top: 50px;
            right: 35px;
            font-size: 20px;
            cursor: pointer;
        }

        #enroll_modal label {
            width: 125px;
        }

        #enroll_modal input,
        div.modal select {
            width: 560px;
        }
    </style>
    <script src="res/js/enrollment-1.4.js"></script>
    <script src="res/js/user.js"></script>
</head>

<body>
    <?php include("header.php"); ?>
    <main>
        <nav class="tools">
            <i id="addUser" title="Add Existing User" class="fa-solid fa-user-plus"></i>
            <i id="upload" title="Uplad Replacement List" class="fas fa-upload"></i>
        </nav>

        <?php include("areas.php"); ?>
        <div id="content">
            <?php if ($msg) : ?>
                <p class="error"><?= $msg ?></p>
            <?php endif; ?>

            <?php function showList($type, $list, $showNoneMsg = false)
            { ?>
                <?php if ($showNoneMsg && !$list) : ?>
                    <h2>No <?= $type ?>(s) Yet</h2>
                <?php elseif (count($list) == 0 && !$showNoneMsg) : ?>
                    <!-- do nothing -->
                <?php else : ?>
                    <h2><?= count($list) ?> <?= $type ?>(s)</h2>
                    <table>
                        <tr>
                            <th>ID</th>
                            <th>KnowAs</th>
                            <th>Family Name</th>
                            <th>Email</th>
                            <th>Group</th>
                            <th></th>
                            <th></th>
                        </tr>
                        <?php foreach ($list as $person) : ?>
                            <tr>
                                <td class="center studentID">
                                    <a title="Edit Student details" href="../../user/<?= $person["id"] ?>">
                                        <?= $person["studentID"] ?>
                                    </a>
                                </td>
                                <td class="name">
                                    <a title="Edit Student details" href="../../user/<?= $person["id"] ?>">
                                        <?= $person["knownAs"] ?>
                                    </a>
                                </td>
                                <td>
                                    <a title="Edit Student details" href="../../user/<?= $person["id"] ?>">
                                        <?= $person["lastname"] ?>
                                    </a>
                                </td>
                                <td>
                                    <a title="Edit Student details" href="../../user/<?= $person["id"] ?>">
                                        <?= $person["email"] ?>
                                    </a>
                                </td>
                                <td class="center group">
                                    <?= $person["group"] ?>
                                </td>
                                <td class="center">
                                    <i class="fa-solid fa-gear config" title="Configure Enrollment" data-uid="<?= $person['id'] ?>" data-auth="<?= $person['auth'] ?>" data-eid="<?= $person['eid'] ?>"></i>
                                </td>
                                <td class="center" title="Video Views">
                                    <a href="W1D1/views/<?= $person['id'] ?>">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php endif; ?>
            <?php } // end showList 
            ?>
            <?php showList("Instructor", $instructors, true); ?>
            <?php showList("Assistant", $assistants, false); ?>
            <?php showList("Student", $students, true); ?>
            <?php showList("Observer", $observers, false); ?>

        </div>
    </main>
    <div id="overlay">
        <i id="close-overlay" class="fas fa-times-circle"></i>
        <div id="upload_modal" class="modal hide">
            <h3>Upload Replacement</h3>
            <p>Expected format is an infosys class list as .csv</p>
            <form action="" method="post" enctype="multipart/form-data" id="upload_form">
                <input type="hidden" name="offering_id" value="<?= $offering_id ?>" />
                <input type="file" id="list_file" name="list" />
                <div class="btn"><button>Upload Replacement</button></div>
            </form>
        </div>
        <div id="enroll_modal" class="modal hide">
            <h3>Enroll User</h3>
            <p>Only email and auth are needed for previously enrolled users</a></p>
            <form action="enroll" method="post" id="enroll_form">
                <input type="hidden" name="offering_id" value="<?= $offering_id ?>" />
                <div>
                    <label>Email</label>
                    <input type="text" name="email" id="emailField" />
                </div>
                <div>
                    <label>Auth:</label>
                    <select name="auth">
                        <option>observer</option>
                        <option selected>student</option>
                        <option>assistant</option>
                        <option>instructor</option>
                    </select>
                </div>
                <div>
                    <label>Given Name(s):</label>
                    <input type="text" name="first" id="first" placeholder="Including middle name as shown on passport / drivers license" /> <br />
                </div>
                <div>
                    <label>Family Name(s):</label>
                    <input type="text" name="last" placeholder="As on passport / drivers license" /> <br />
                </div>
                <div>
                    <label>Known As:</label>
                    <input type="text" name="knownAs" id="knownAs" placeholder="As used in conversation" /> <br />
                </div>
                <div>
                    <label>Password:</label>
                    <input type="password" name="pass" /> <br />
                </div>
                <div>
                    <label>MIU Student ID:</label>
                    <input type="text" name="studentID" placeholder="If available" /> <br />
                </div>
                <div>
                    <label>Teams Name:</label>
                    <input type="text" name="teamsName" placeholder="If available" /> <br />
                </div>
                <div class="btn">
                    <button type="submit">Enroll</button>
                </div>
            </form>
        </div>
        <div id="configure_modal" class="modal hide">
            <h3>Configure Enrollment</h3>
            <p id="configure_for"></p>
            <i id="remove_icon" class="fa-solid fa-trash-can"></i>
            <form action="config_enroll" method="post" id="config_form">
                <input type="hidden" name="offering_id" value="<?= $offering_id ?>" />
                <input type="hidden" name="user_id" value="" id="config_id" />
                <div>
                    <label>Auth:</label>
                    <select name="auth" id="config_auth">
                        <option value="observer">observer</option>
                        <option value="student">student</option>
                        <option value="assistant">assistant</option>
                        <option value="instructor">instructor</option>
                    </select>
                </div>
                <label>Group:</label>
                <input name="group" type="text" id="config_group" />
                <div class="btn">
                    <button type="submit">Update</button>
                </div>
            </form>
        </div>
    </div> <!-- overlay -->
    <form id="removeStudent" action="unenroll" method="post">
        <input type="hidden" name="offering_id" value="<?= $offering_id ?>" />
        <input type="hidden" name="eid" value="" id="remove_eid" />
    </form>
</body>

</html>
