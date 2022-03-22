<?php if ($_SESSION['user']['type'] === 'admin') : ?>
<!DOCTYPE html>
<html>
    <head>
        <title>Physical Attendance</title>
        <meta charset="utf-8" />
        <meta name=viewport content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="res/css/font-awesome-all.min.css">
		<link rel="stylesheet" href="res/css/offering.css">
		<link rel="stylesheet" href="res/css/adm.css">
        <style>
            #plane {
                cursor: pointer;
            }
            form {
                display: inline;
            }
            input#minPhys {
                width: 20px;
            }
            td.studentID,
            #content td.num {
                width: 100px;
                text-align: center;
            }
        </style>
        <script>
window.addEventListener("load", () => {    
    const week = document.getElementById('week').dataset.week;
    document.getElementById('plane').onclick = () => {
        if (confirm('Email International Students below Minimum?')) {
            const minPhys = document.getElementById('minPhys').value;
            fetch(`${week}/email`, {
                method : "POST",
                body : `minPhys=${minPhys}`,
                headers :
                    {'Content-Type' : 'application/x-www-form-urlencoded'},
            }).then(() => {alert("Emails sent")});
        }
    };
});
        </script>
    </head>
    <body>
        <header>
			<div id="controls" data-id="<?= $_SESSION['user']['id'] ?>">
                <a href="/videos/user" title="Users"><i class="fas fa-users"></i></a>
                <a href="logout"><i title="Logout" class="fas fa-power-off"></i></a>
			</div>
            <div id="course">
                <a href=".."><?= strtoupper($course) ?> <?= $block ?></a>
            </div>
            <h1>
                <span class="title" id="week" data-week="<?= $week ?>">
					<?= $week ?> Physical Attendance
				</span>
            </h1>
        </header>
        <main>
            <div id="content">
                <p><strong>Note:</strong> this report is based on attendance-export data. 
                    If the required export reports have not been generated yet 
                    this report will be inacurate.
                </p>

            <h3>
                International Students need: 
                <input type="text" id="minPhys" name="minPhys" value="2" />
                <i title="Email Students Below" id="plane" class="far fa-paper-plane"></i>                    
            </h3>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>inClass</th>
                </tr>
                <?php foreach ($attend as $student) : ?>
                    <?php if (substr($student['studentID'], 0, 2) > 60): ?>
                <tr>
                    <td class="studentID"><?= $student['studentID']?></td>
                    <td class="name"><?= $student['knownAs'] . " " . $student['lastname']?></td>
                    <td class="num"><?= $student['inClass']?></td>
                </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </table>                

            <h3>US Students</h3>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>inClass</th>
                </tr>
                <?php foreach ($attend as $student) : ?>
                    <?php if (substr($student['studentID'], 0, 2) < 20): ?>
                <tr>
                    <td class="studentID"><?= $student['studentID']?></td>
                    <td class="name"><?= $student['knownAs'] . " " . $student['lastname']?></td>
                    <td class="num"><?= $student['inClass']?></td>
                </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </table>                
            </div>
        </main>
    </body>
</html>
<?php endif; ?>
