<!DOCTYPE html>
<html>
    <head>
        <title><?= $offering['block'] ?> Enrollment</title>
        <meta charset="utf-8" />
        <meta name=viewport content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="res/css/font-awesome-all.min.css">
		<link rel="stylesheet" href="res/css/offering.css">
        <link rel="stylesheet" href="res/css/adm.css">
        <style>
            #content {
                width: 1000px;
            }
            #content table {
                cursor: pointer;
            }
            .modal {
                border: 1px solid black;
                margin-left: auto;
                margin-right: auto;
                width: 700px;
                padding: 1em;
                position: relative;
                top: 100px;
                background-color: white;
            }
            .modal h3 {
                border-bottom: 1px solid black;
            }
            .modal input {
                width: 100%;
                border: 1px solid #ddd;
            }
            .modal .btn {
                margin-top: 10px;
                text-align: right;
            }
        </style>
        <script src="res/js/users.js"></script>
        <script>
            window.addEventListener("load", () => {
                const overlay = document.getElementById("overlay");
                document.getElementById("upload").addEventListener("click", () => {
                    overlay.classList.add("visible");
                });
                function hide() {
                    overlay.classList.remove("visible");
                }
                document.getElementById("close-overlay").onclick = hide;

                document.getElementById("overlay").onclick = function (evt) {
                    if (evt.target == this) {
                        hide();
                    }
                };

                document.getElementById("upload_form").onsubmit = () => {
                    const file = document.getElementById("list_file");
                    if (!file.value) {
                        return false;
                    }
                };
            });


        </script>
    </head>
    <body>
        <header>
			<div id="controls" data-id="<?= $_SESSION['user']['id'] ?>">
                <a href="/videos/user" title="Users"><i class="fas fa-users"></i></a>
				<a href="logout" title="Logout"><i class="fas fa-power-off"></i></a>
			</div>
            <div id="course">
                <?= strtoupper($course) ?>
                <span data-id="<?= $offering['id']?>" id="offering"> <?= $offering['block'] ?> </span>
            </div>
            <h1>
                <span class="title" >
					Enrollment
				</span>
            </h1>
        </header>
        <main>
            <nav class="tools">
                <i id="upload" title="Uplad Replacement List" class="fas fa-upload"></i>
            </nav>
            <nav class="areas">
                <div title="Videos"><a href="../<?= $offering['block'] ?>/"><i class="fas fa-film"></a></i></div>
                <div title="Labs"><i class="fas fa-flask"></i></div>
                <div title="Quizzes"><i class="fas fa-school"></i></div>
                <div title="Attendance"><i class="fas fa-user-check"></i></div>
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