<!DOCTYPE html>
<html>

<head>
    <title><?= $block ?> Enrollment</title>
    <meta charset="utf-8" />
    <meta name=viewport content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="res/css/lib/font-awesome-all.min.css">
    <link rel="stylesheet" href="res/css/common-1.2.css">
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
        <nav class="areas">
            <div title="Videos"><a href="../<?= $block ?>/"><i class="fas fa-film"></a></i></div>
            <?php if ($offering['hasQuiz']) : ?>
                <div title="Quizzes"><a href="quiz"><i class="fas fa-vial"></i></a></div>
            <?php endif; ?>
            <?php if ($offering['hasLab']) : ?>
                <div title="Labs"><a href="lab"><i class="fas fa-flask"></i></a></div>
            <?php endif; ?>
            <div title="Attendance"><a href="attendance"><i class="fas fa-user-check"></i></a></div>
            <div title="Enrollment" class="active"><i class="fas fa-user-friends"></i></div>
            <div title="Back to My Courses">
                <a href="../../">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
            </div>
        </nav>
        <div id="content">
            <?php if ($msg) : ?>
                <p class="error"><?= $msg ?></p>
            <?php endif; ?>

            <?php if (!$instructors) : ?>
                <h2>No Instructor(s) Yet</h2>
            <?php else : ?>
                <h2><?= count($instructors) ?> Instructor(s)</h2>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>KnowAs</th>
                        <th>Given</th>
                        <th>Family</th>
                        <th>Email</th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <?php if ($offering['usesFlowcharts']) : ?>
                            <th></th>
                        <?php endif; ?>
                    </tr>
                    <?php foreach ($instructors as $instructor) : ?>
                        <tr>
                            <td class="center studentID"><?= $instructor["studentID"] ?></td>
                            <td class="name"><?= $instructor["knownAs"] ?></td>
                            <td><?= $instructor["firstname"] ?></td>
                            <td><?= $instructor["lastname"] ?></td>
                            <td><?= $instructor["email"] ?></td>
                            <td class="center">
                                <a title="Edit Student" href="../../user/<?= $instructor["id"] ?>">
                                    <i class="fa-solid fa-pencil"></i>
                                </a>
                            </td>
                            <td class="center"><i class="fa-solid fa-gear" title="Configure Enrollment" data-uid="<?= $instructor['id'] ?>" data-auth="instructor" data-eid="<?= $instructor['eid'] ?>"></i>
                            </td>
                            <td class="center" title="Video Views">
                                <a href="W1D1/views/<?= $instructor['id'] ?>"><i class="fa-solid fa-eye"></i></a>
                            </td>
                            <?php if ($offering['usesFlowcharts']) : ?>
                                <td class="center" title="Flowcharts">
                                    <a title="Flowcharts" target="_blank" href="/flowcharts/projects/<?= $instructor["id"] ?>">
                                        <i class="fa-regular fa-chart-bar"></i>
                                    </a>
                                </td>
                            <?php endif; ?>

                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>

            <?php if ($assistants) : ?>
                <h2><?= count($assistants) ?> Assistant(s)</h2>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>KnowAs</th>
                        <th>Given</th>
                        <th>Family</th>
                        <th>Email</th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <?php if ($offering['usesFlowcharts']) : ?>
                            <th></th>
                        <?php endif; ?>
                    </tr>
                    <?php foreach ($assistants as $assistant) : ?>
                        <tr>
                            <td class="center studentID"><?= $assistant["studentID"] ?></td>
                            <td class="name"><?= $assistant["knownAs"] ?></td>
                            <td><?= $assistant["firstname"] ?></td>
                            <td><?= $assistant["lastname"] ?></td>
                            <td><?= $assistant["email"] ?></td>
                            <td class="center">
                                <a title="Edit Student" href="../../user/<?= $assistant["id"] ?>">
                                    <i class="fa-solid fa-pencil"></i>
                                </a>
                            </td>
                            <td class="center"><i class="fa-solid fa-gear" title="Configure Enrollment" data-uid="<?= $assistant['id'] ?>" data-auth="assistant" data-eid="<?= $assistant['eid'] ?>"></i>
                            </td>
                            <td class="center" title="Video Views">
                                <a href="W1D1/views/<?= $assistant['id'] ?>"><i class="fa-solid fa-eye"></i></a>
                            </td>

                            <?php if ($offering['usesFlowcharts']) : ?>
                                <td class="center" title="Flowcharts">
                                    <a title="Flowcharts" target="_blank" href="/flowcharts/projects/<?= $assistant["id"] ?>">
                                        <i class="fa-regular fa-chart-bar"></i>
                                    </a>
                                </td>
                            <?php endif; ?>

                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>



            <?php if (!$students) : ?>
                <h2>No Student(s) Yet</h2>
            <?php else : ?>
                <h2><?= count($students) ?> Student(s)</h2>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>KnowAs</th>
                        <th>Given</th>
                        <th>Family</th>
                        <th>Email</th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <?php if ($offering['usesFlowcharts']) : ?>
                            <th></th>
                        <?php endif; ?>
                    </tr>
                    <?php foreach ($students as $student) : ?>
                        <tr>
                            <td class="center studentID">
                                <?= $student["studentID"] ?>
                            </td>
                            <td class="name"><?= $student["knownAs"] ?></td>
                            <td><?= $student["firstname"] ?></td>
                            <td><?= $student["lastname"] ?></td>
                            <td><?= $student["email"] ?></td>
                            <td class="center">
                                <a title="Edit Student" href="../../user/<?= $student["id"] ?>">
                                    <i class="fa-solid fa-pencil"></i>
                                </a>
                            </td>
                            <td class="center"><i class="fa-solid fa-gear" title="Configure Enrollment" data-uid="<?= $student['id'] ?>" data-auth="student" data-eid="<?= $student['eid'] ?>"></i>
                            </td>
                            <td class="center" title="Video Views">
                                <a href="W1D1/views/<?= $student['id'] ?>"><i class="fa-solid fa-eye"></i></a>
                            </td>
                            <?php if ($offering['usesFlowcharts']) : ?>
                                <td class="center" title="Flowcharts">
                                    <a title="Flowcharts" target="_blank" href="/flowcharts/projects/<?= $student["id"] ?>">
                                        <i class="fa-regular fa-chart-bar"></i>
                                    </a>
                                </td>
                            <?php endif; ?>

                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>

            <?php if ($observers) : ?>
                <h2><?= count($observers) ?> Observer(s)</h2>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>KnowAs</th>
                        <th>Given</th>
                        <th>Family</th>
                        <th>Email</th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <?php if ($offering['usesFlowcharts']) : ?>
                            <th></th>
                        <?php endif; ?>
                    </tr>
                    <?php foreach ($observers as $student) : ?>
                        <?php if ($student['auth'] == 'observer') : ?>
                            <tr>
                                <td class="center studentID">
                                    <?= $student["studentID"] ?>
                                </td>
                                <td class="name"><?= $student["knownAs"] ?></td>
                                <td><?= $student["firstname"] ?></td>
                                <td><?= $student["lastname"] ?></td>
                                <td><?= $student["email"] ?></td>
                                <td class="center">
                                    <a title="Edit Student" href="../../user/<?= $student["id"] ?>">
                                        <i class="fa-solid fa-pencil"></i>
                                    </a>
                                </td>
                                <td class="center"><i class="fa-solid fa-gear" title="Configure Enrollment" data-uid="<?= $student['id'] ?>" data-auth="observer" data-eid="<?= $student['eid'] ?>"></i>
                                </td>
                                <td class="center" title="Video Views">
                                    <a href="W1D1/views/<?= $student['id'] ?>"><i class="fa-solid fa-eye"></i></a>
                                </td>
                                <?php if ($offering['usesFlowcharts']) : ?>
                                    <td class="center" title="Flowcharts">
                                        <a title="Flowcharts" target="_blank" href="/flowcharts/projects/<?= $student["id"] ?>">
                                            <i class="fa-regular fa-chart-bar"></i>
                                        </a>
                                    </td>
                                <?php endif; ?>

                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </table>
            <?php endif; // observers 
            ?>

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
