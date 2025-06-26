<!DOCTYPE html> <?php global $MY_BASE ?>
<html>

<head>
    <title><?= $block ?> Enrollment</title>
    <meta charset="utf-8" />
    <meta name=viewport content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/lib/font-awesome-all.min.css">
    <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/common-1.3.css">
    <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/adm-1.0.css">
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
            top: 80px;
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
    <script src="<?= $MY_BASE ?>/res/js/enrollment-1.4.js"></script>
    <script src="<?= $MY_BASE ?>/res/js/user.js"></script>
</head>

<body>
    <?php include 'header.php'; ?>
    <main>
        <nav class="tools">
            <i id="addUser" title="Add Existing User" class="fa-solid fa-user-plus"></i>
            <i id="upload" title="Uplad Replacement List" class="fas fa-upload"></i>
        </nav>

        <?php include 'areas.php'; ?>
        <div id="content">
            <?php if ($msg) { ?>
                <p class="error"><?= $msg ?></p>
            <?php } ?>

            <?php function showList($type, $list, $showNoneMsg = false)
            { ?>
                <?php if ($showNoneMsg && ! $list) { ?>
                    <h2>No <?= $type ?>(s) Yet</h2>
                <?php } elseif (count($list) == 0 && ! $showNoneMsg) { ?>
                    <!-- do nothing -->
                <?php } else { ?>
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
                        <?php foreach ($list as $person) { ?>
                            <tr>
                                <td class="center studentID">
                                    <a title="Edit Student details" href="../../user/<?= $person['id'] ?>">
                                        <?= $person['studentID'] ?>
                                    </a>
                                </td>
                                <td class="name">
                                    <a title="Edit Student details" href="../../user/<?= $person['id'] ?>">
                                        <?= $person['knownAs'] ?>
                                    </a>
                                </td>
                                <td>
                                    <a title="Edit Student details" href="../../user/<?= $person['id'] ?>">
                                        <?= $person['lastname'] ?>
                                    </a>
                                </td>
                                <td>
                                    <a title="Edit Student details" href="../../user/<?= $person['id'] ?>">
                                        <?= $person['email'] ?>
                                    </a>
                                </td>
                                <td class="center group">
                                    <?= $person['group'] ?>
                                </td>
                                <td class="center">
                                    <i class="fa-solid fa-gear config" title="Configure Enrollment" data-uid="<?= $person['id'] ?>" data-auth="<?= $person['auth'] ?>" data-eid="<?= $person['eid'] ?>"></i>
                                </td>
                                <td class="center" title="Attendance">
                                    <a href="attendance/chart?user_id=<?= $person['id'] ?>">
                                        <i class="fa-solid fa-user-check"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    </table>
                <?php } ?>
            <?php } // end showList
?>
            <?php showList('Instructor', $instructors, true); ?>
            <?php showList('Assistant', $assistants, false); ?>
            <?php showList('Student', $students, true); ?>
            <?php showList('Observer', $observers, false); ?>

        </div>
    </main>

        <dialog id="uploadModal" class="modal">
            <i id="closeUploadDialog" class="fas fa-times-circle close"></i>
            <h3>Upload Replacement</h3>
            <p>Expected format is an infosys / harmony class list as .csv</p>
            <form action="" method="post" enctype="multipart/form-data" id="upload_form">
                <input type="hidden" name="offering_id" value="<?= $offering_id ?>" />
                <input type="file" id="list_file" name="list" autofocus />
                <div class="btn"><button>Upload Replacement</button></div>
            </form>
        </dialog>

        <dialog id="enrollModal" class="modal">
            <i id="closeEnrollDialog" class="fas fa-times-circle close"></i>
            <h3>Enroll User</h3>
            <p>Only email and auth are needed for previously enrolled users</a></p>
            <form action="enroll" method="post" id="enroll_form">
                <input type="hidden" name="offering_id" value="<?= $offering_id ?>" />
                <div>
                    <label>Email</label>
                    <input type="text" name="email" id="emailField" autofocus />
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
        </dialog>

        <dialog id="configureModal" class="modal">
            <i id="closeConfigureDialog" class="fas fa-times-circle close"></i>
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
                <input name="group" type="text" id="config_group" autofocus />
                <div class="btn">
                    <button type="submit">Update</button>
                </div>
            </form>
        </dialog>

        <form id="removeStudent" action="unenroll" method="post">
            <input type="hidden" name="offering_id" value="<?= $offering_id ?>" />
            <input type="hidden" name="eid" value="" id="remove_eid" />
        </form>
</body>

</html>
