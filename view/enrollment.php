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
        </style>
        <script src="res/js/users.js"></script>
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
                <i title="Uplad Replacement List" class="fas fa-upload"></i>
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
            <div id="content"></div>
        </div>

    </body>
</html>
