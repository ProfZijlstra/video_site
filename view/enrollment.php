<!DOCTYPE html>
<html>
    <head>
        <title><?= $block ?> Enrollment</title>
        <meta charset="utf-8" />
        <meta name=viewport content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="res/css/font-awesome-all.min.css">
        <link rel="stylesheet" href="res/css/common.css">
        <link rel="stylesheet" href="res/css/adm.css">
        <style>
            #content {
                width: 1000px;
            }
            #content table {
                cursor: pointer;
            }
        </style>
        <script src="res/js/users.js"></script>
        <script src="res/js/enrollment.js"></script>
    </head>
    <body>
        <?php include("header.php"); ?>
        <main>
            <nav class="tools">
                <i id="upload" title="Uplad Replacement List" class="fas fa-upload"></i>
            </nav>
            <nav class="areas">
                <div title="Videos"><a href="../<?= $block ?>/"><i class="fas fa-film"></a></i></div>
                <div title="Labs"><i class="fas fa-flask"></i></div>
                <div title="Quizzes"><i class="fas fa-vial"></i></div>
                <div title="Attendance"><a href="attendance"><i class="fas fa-user-check"></i></a></div>
                <div title="Enrollment" class="active"><i class="fas fa-user-friends"></i></div>
            </nav>
            <div id="content">
            <?php if (!$enrollment): ?>
                <h2>No Enrollment Yet</h2>
            <?php else: ?>
            <table>
                <tr>
                    <th>ID</th>
                    <th>KnowAs</th>
                    <th>Given</th>
                    <th>Family</th>
                    <th>Email</th>
                </tr>
                <?php foreach ($enrollment as $student): ?>
                <tr>
                    <td><?= $student["studentID"] ?></td>
                    <td><?= $student["knownAs"] ?></td>
                    <td>
                        <a href="../../user/<?= $student["id"]?>">
                            <?= $student["firstname"] ?>
                        </a>
                    </td>
                    <td><?= $student["lastname"] ?></td>
                    <td><?= $student["email"] ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            </div>
        </main>
        <div id="overlay">
            <i id="close-overlay" class="fas fa-times-circle"></i>
            <div class="modal">
                <h3>Upload Replacement</h3>
                <p>Expected format is an infosys class list as .csv</p>
                <form action="" method="post" enctype="multipart/form-data" id="upload_form">
                    <input type="hidden" name="offering_id" value="<?= $offering["id"] ?>" />
                    <input type="file" id="list_file" name="list" />
                    <div class="btn"><button>Upload Replacement</button></div>
                </form>
            </div>
        </div>

    </body>
</html>
