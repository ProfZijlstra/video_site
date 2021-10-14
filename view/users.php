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
        <script>
            window.addEventListener("load", () => {
                document.querySelector("table").addEventListener("click", (evt) => {
                    const target = evt.target;
                    const link = target.parentNode.querySelector("a");
                    if (link) {
                        link.click();
                    }
                });
            });
        </script>
    </head>
    <body>
        <header>
			<div id="controls" data-id="<?= $_SESSION['user']['id'] ?>">
				<a href="logout"><i class="fas fa-power-off"></i></a>
			</div>
            <h1><?= $title ?></h1>
        </header>
        <main>
            <div id="content">
                <div id="add_btn">
                    <a href="user/add"><button>Add User</button></a>
                </div>

        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>first</th>
                <th>last</th>
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
