<?php if ($_SESSION['user']['type'] === 'admin') : ?>
<!DOCTYPE html>
<html>
    <head>
        <title><?= $title ?></title>
        <meta charset="utf-8" />
        <meta name=viewport content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="res/css/font-awesome-all.min.css">
        <link rel="stylesheet" href="res/css/common.css">
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
            td {
                cursor: pointer;
            }
            #content th {
                cursor: default;
            }
        </style>
        <script src="res/js/users.js"></script>
        <script src="res/js/back.js"></script>
    </head>
    <body>
        <?php include("header.php"); ?>
        <main>
            <nav class="back" title="back">
                <i class="fa-solid fa-arrow-left"></i>
            </nav>
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
