<!DOCTYPE html>
<html>
    <head>
        <title><?= $block ?> Enrollment</title>
        <meta charset="utf-8" />
        <meta name=viewport content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="res/css/font-awesome-all.min.css">
        <link rel="stylesheet" href="res/css/common-1.1.css">
        <link rel="stylesheet" href="res/css/adm.css">
        <style>
            #content {
                width: 1000px;
            }
            #content table {
                cursor: pointer;
            }
            td.center {
                text-align: center;
            }
            p.error {
                color: red;
            }
        </style>
        <script src="res/js/users.js"></script>
        <script src="res/js/enrollment.js"></script>
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
                <?php if ($offering['hasQuiz']): ?>
                <div title="Quizzes"><a href="quiz"><i class="fas fa-vial"></i></a></div>
                <?php endif; ?>
                <?php if ($offering['hasLab']): ?>
                <div title="Labs"><i class="fas fa-flask"></i></div>
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
            <?php if ($error) : ?>
                <p class="error"><?= $error ?></p>
            <?php endif; ?>

            <?php if (!$instructors): ?>
                <h2>No Instructor(s) Yet</h2>
            <?php else: ?>
            <h2>Instructor(s)</h2>
            <table>
                <tr>
                    <th>ID</th>
                    <th>KnowAs</th>
                    <th>Given</th>
                    <th>Family</th>
                    <th>Email</th>
                    <th>Del</th>
                </tr>
                <?php foreach ($instructors as $instructor): ?>
                <tr>
                    <td class="center studentID"><?= $instructor["studentID"] ?></td>
                    <td class="name"><?= $instructor["knownAs"] ?></td>
                    <td>
                        <a href="../../user/<?= $instructor["id"]?>">
                            <?= $instructor["firstname"] ?>
                        </a>
                    </td>
                    <td><?= $instructor["lastname"] ?></td>
                    <td><?= $instructor["email"] ?></td>
                    <td class="center"><i class="fa-solid fa-trash-can" title="Unenroll" data-uid="<?= $instructor['id'] ?>"></i></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>

            <?php if (!$students): ?>
                <h2>No Student(s) Yet</h2>
            <?php else: ?>
            <h2>Student(s)</h2>
            <table>
                <tr>
                    <th>ID</th>
                    <th>KnowAs</th>
                    <th>Given</th>
                    <th>Family</th>
                    <th>Email</th>
                    <th><i class="fa-solid fa-handshake-angle" title="Assistant"></i></th>
                    <th>Del</th>
                </tr>
                <?php foreach ($students as $student): ?>
                <tr>
                    <td class="center studentID">
                        <?= $student["studentID"] ?>
                    </td>
                    <td class="name"><?= $student["knownAs"] ?></td>
                    <td>
                        <a href="../../user/<?= $student["id"]?>">
                            <?= $student["firstname"] ?>
                        </a>
                    </td>
                    <td><?= $student["lastname"] ?></td>
                    <td><?= $student["email"] ?></td>
                    <td><input type="checkbox" /><!-- TODO implement --> </td>
                    <td class="center"><i class="fa-solid fa-trash-can" title="Unenroll" data-uid="<?= $student['id'] ?>"></i></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>

            <?php if ($observers): ?>
            <h2>Observer(s)</h2>
            <table>
                <tr>
                    <th>ID</th>
                    <th>KnowAs</th>
                    <th>Given</th>
                    <th>Family</th>
                    <th>Email</th>
                    <th>Del</th>
                </tr>
                <?php foreach ($enrollment as $student): ?>
                    <?php if($student['auth'] == 'observer' ): ?>
                <tr>
                    <td class="center studentID">
                        <?= $student["studentID"] ?>
                    </td>
                    <td class="name"><?= $student["knownAs"] ?></td>
                    <td>
                        <a href="../../user/<?= $student["id"]?>">
                            <?= $student["firstname"] ?>
                        </a>
                    </td>
                    <td><?= $student["lastname"] ?></td>
                    <td><?= $student["email"] ?></td>
                    <td class="center"><i class="fa-solid fa-trash-can" title="Unenroll" data-uid="<?= $student['id'] ?>"></i></td>
                </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </table>
            <?php endif; // observers ?>
            
            </div>
        </main>
        <div id="overlay">
            <i id="close-overlay" class="fas fa-times-circle"></i>
            <div id="upload_modal" class="modal hide">
                <h3>Upload Replacement</h3>
                <p>Expected format is an infosys class list as .csv</p>
                <form action="" method="post" enctype="multipart/form-data" id="upload_form">
                    <input type="hidden" name="offering_id" value="<?= $offering_id ?>" id="offering_id"/>
                    <input type="file" id="list_file" name="list" />
                    <div class="btn"><button>Upload Replacement</button></div>
                </form>
            </div>
            <div id="enroll_modal" class="modal hide">
                <h3>Enroll User</h3>
                <p>The student already has to have an account <a href="/videos/user">(be a user)</a></p>
                <form action="enroll" method="post" id="enroll_form">
                    <input type="hidden" name="offering_id" value="<?= $offering_id ?>" id="offering_id"/>
                    <div>
                        <label>User ID:</label>
                        <input type="text" name="user_id" id="enrollID" /> 
                    </div>
                    <div>
                        <label>Auth:</label>
                        <select name="auth">
                            <option>observer</option>
                            <option>student</option>
                            <option>instructor</option>
                        </select>
                    </div>
                    <div class="btn">
                        <button>Enroll</button>
                    </div>
                </form>
            </div>
        </div>
        <form id="removeStudent" action="unenroll" method="post">
            <input type="hidden" name="offering_id" value="<?= $offering_id ?>" />
            <input type="hidden" name="uid" value="" id="removeUid" />
        </form>
    </body>
</html>
