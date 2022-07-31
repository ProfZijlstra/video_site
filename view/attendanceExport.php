<?php if ($_user_type === 'admin') : ?>
<!DOCTYPE html>
<html>
    <head>
        <title>Attendance Export</title>
        <meta charset="utf-8" />
        <meta name=viewport content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="res/css/font-awesome-all.min.css">
        <link rel="stylesheet" href="res/css/common.css">
		<link rel="stylesheet" href="res/css/adm.css">
        <style>
            .session input[type=text] {
                width: 665px;
            }
            .session label {
                display: inline-block;
                width: 70px;
                text-align: left;
            }
            .btns {
                text-align: right;
                margin-top: 5px;
            }
            .cbox {
                text-align: center;
            }
        </style>
        <script>
window.addEventListener("load", () => {    
    const stype = document.getElementById('session').dataset.stype;

    const tags = document.getElementById('data').getElementsByTagName('input');
    for (const tag of tags) {
        tag.onchange = doUpdate;
    }

    function doUpdate(evt) {
        const tr = evt.target.parentNode.parentNode;
        const id = tr.dataset.id;
        const inClassFields = tr.getElementsByClassName("inClass");
        const inClass = inClassFields[0].checked;
        const commentFields = tr.getElementsByClassName("comment");
        const comment = commentFields[0].value;
        const update = {
            "id" : id,
            "inClass": inClass,
            "comment": comment
        };
        fetch(`${stype}/${id}`, {
            method : 'POST',
            headers : {'Content-Type' : 'application/json'},
            body : JSON.stringify(update)
        });
    }
});
        </script>
        <script src="res/js/back.js"></script>
    </head>
    <body>
        <?php include("header.php"); ?>
        <main>
            <nav class="back" title="back">
                <i class="fa-solid fa-arrow-left"></i>
            </nav>
            <div id="content">
            <!-- General Session Info here -->
            <div class="session" id="session" data-stype="<?= $stype ?>">
                <h3>Session Details:</h3>
                <div>
                    <label>Status</label>
                    <input disabled type="text" name="title" value="<?= $session["status"] ?>" />
                </div>
                <div>
                    <label>Meetings</label>
                    <input disabled type="text" name="generated" value="<?= $session["generated"] ?>" />
                </div>
                <div>
                    <label>Start</label>
                    <input disabled type="text" name="start" value="<?= $session["start"] ?>" />
                </div>
                <div>
                    <label>Stop</label>
                    <input disabled type="text" name="stop" value="<?= $session["stop"] ?>" />
                </div>
            </div>
            <div class="btns">
                <form method="post" action="<?= $stype ?>">
                    <button>Regenerate Report</button>
                </form>
            </div>


            <h3>Export Data</h3>
            <table id="data">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th>InClass</th>
                    <th>Comments</th>
                </tr>
                <?php foreach ($exports as $export) : ?>
                <tr data-id="<?= $export['id'] ?>">
                    <td><?= $export['studentID']?></td>
                    <td title="<?= $export['knownAs'] ?>">
                        <?= trim($export['lastname']) . ', ' . $export['firstname']?>
                    </td>
                    <td><?= $export['status']?></td>
                    <td class="cbox">
                        <input class="inClass" type="checkbox" <?= $export['inClass'] ? 'checked' : '' ?> />
                    </td>
                    <td>
                        <input class="comment" type="text" value="<?= $export['comment']?>">
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>                
            </div>
        </main>
    </body>
</html>
<?php endif; ?>
