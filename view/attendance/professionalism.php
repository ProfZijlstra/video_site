<!DOCTYPE html>
<html>
    <head>
        <title>Physical Attendance</title>
        <meta charset="utf-8" />
        <meta name=viewport content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="res/css/lib/font-awesome-all.min.css">
        <link rel="stylesheet" href="res/css/common-1.2.css">
		<link rel="stylesheet" href="res/css/adm.css">
        <style>
            #content {
                width: 1000px;
            }
            td.studentID {
                text-align: center;
            }
        </style>
        <script src="res/js/back.js"></script>
    </head>
    <body>
        <?php include("header.php"); ?>
        <main>
            <nav class="back" title="Back">
                <i class="fa-solid fa-arrow-left"></i>
            </nav>
            <div id="content">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Phys</th>
                    <th>Abs</th>
                    <th>MidMis</th>
                    <th>Late</th>
                    <th>Mins</th>
                    <th>Leave</th>
                    <th>Mins</th>
                    <th>Total</th>
                </tr>
                <?php foreach ($professionals as $student) : ?>
                <tr>
                    <td class="studentID"><?= $student['id']?></td>
                    <td class="name"><?= $student['name']?></td>
                    <td class="num"><?= $student['inClass']?></td>
                    <td class="num"><?= $student['absent']?></td>
                    <td class="num"><?= $student['middleMissing']?></td>
                    <td class="num"><?= $student['late']?></td>
                    <td class="num"><?= $student['minsLate']?></td>
                    <td class="num"><?= $student['leaveEarly']?></td>
                    <td class="num"><?= $student['minsLeave']?></td>
                    <td class="num"><?= $student['total']?></td>
                </tr>
                <?php endforeach; ?>
            </table>                

            </div>
        </main>
    </body>
</html>
