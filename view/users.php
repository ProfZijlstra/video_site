<?php if ($_SESSION['user']['type'] === 'admin') : ?>
<!DOCTYPE html>
<html>
    <head>
        <title><?= $title ?></title>
        <meta charset="utf-8" />
        <meta name=viewport content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="res/css/font-awesome-all.min.css">
		<link rel="stylesheet" href="res/css/offering.css">
		<link rel="stylesheet" href="res/css/adm.css">
        <style>
            #add_btn {
                text-align: right;
                position: relative;
                bottom: 25px;
            }
            #content {
                width: 70%;
            }
            td.num {
                text-align: right;
            }
            h1 {
                text-align: center;
            }
            td {
                cursor: pointer;
            }
            #content th {
                cursor: default;
            }
        </style>
        <script src="res/js/users.js"></script>
    </head>
    <body>
        <header>
			<div id="controls" data-id="<?= $_SESSION['user']['id'] ?>">
				<a href="logout"><i title="Logout" class="fas fa-power-off"></i></a>
			</div>
            <h1><?= $title ?></h1>
        </header>
        <main>
            <nav class="tools">
                <a href="user/add"><i title="Add User" class="fas fa-user-plus"></i></a>
            </nav>
            <div id="content">

        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Given</th>
                <th>Family</th>
                <th>email</th>
                <th>created</th>
                <th>accessed</th>
                <th>active</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user) : ?>
                <tr class="user">
                    <td class="num"><a href="user/<?= $user['id'] ?>"><?= $user['id'] ?></a></td>
                    <td><?= $user['firstname'] ?></td>
                    <td><?= $user['lastname'] ?></td>
                    <td><?= $user['email'] ?></td>
                    <td class="num"><?= $user['created'] ?></td>
                    <td class="num"><?= $user['accessed'] ?></td>
                    <td class="num"><?= $user['active'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

            </div>
        </main>
    </body>
</html>
<?php endif; ?>
