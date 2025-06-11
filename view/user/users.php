<!DOCTYPE html> <?php global $MY_BASE ?>
<html>
    <head>
        <title><?= $title ?></title>
        <meta charset="utf-8" />
        <meta name=viewport content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/lib/font-awesome-all.min.css">
        <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/common-1.3.css">
		<link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/adm-1.0.css">
        <style>
            #add_btn {
                text-align: right;
                position: relative;
                bottom: 25px;
            }
            #content {
                width: 85%;
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
        <script src="<?= $MY_BASE ?>/res/js/users.js"></script>
        <script src="<?= $MY_BASE ?>/res/js/back.js"></script>
    </head>
    <body>
        <?php include("header.php"); ?>
        <main>
            <nav class="back" title="Back">
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
                <th>StudentId</th>
                <th>KnownAs</th>
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
                    <td class="num"><?= $user['studentID']?></td>
                    <td><?= $user['knownAs'] ?></td>
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
