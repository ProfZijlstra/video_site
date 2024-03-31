<!DOCTYPE html>
<html>

<head>
    <title><?= $block ?> Settings</title>
    <meta charset="utf-8" />
    <meta name=viewport content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="res/css/lib/font-awesome-all.min.css">
    <link rel="stylesheet" href="res/css/common-1.2.css">
    <link rel="stylesheet" href="res/css/adm.css">
    <style>
        #content h2,
        #content h3 {
            margin-bottom: 10px;
        }

        .settings {
            display: grid;
            grid-template-columns: 50px auto;
            row-gap: 10px;
            column-gap: 20px;
        }
    </style>
    <script>
        window.addEventListener("load", function() {
            function sendUpdate() {
                const username = document.getElementById("username").value;
                const course_id = document.getElementById("course_id").value;
                const AM_id = document.getElementById("AM_id").value;
                const PM_id = document.getElementById("PM_id").value;
                const SAT_id = document.getElementById("SAT_id").value;

                const body = new URLSearchParams();
                body.append("username", username);
                body.append("course_id", course_id);
                body.append("AM_id", AM_id);
                body.append("PM_id", PM_id);
                body.append("SAT_id", SAT_id);

                fetch("config", {
                        method: "POST",
                        body: body,
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded",
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            alert("Error saving settings");
                        }
                    });
            }
            const inputs = document.querySelectorAll("input");
            inputs.forEach(input => {
                input.addEventListener("change", sendUpdate);
            });
        });
    </script>
</head>

<body>
    <?php include("header.php"); ?>
    <main>
        <nav class="areas">
            <div title="Back">
                <a href="../attendance">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
            </div>
        </nav>

        <div id="content">
            <h3 id="CAMSheader">
                CAMS Integration
            </h3>
            <div id="settings" class="settings">
                <div>
                    <label for="username">User</label>
                </div>
                <div>
                    <input autofocus type="text" name="username" id="username" placeholder="CAMS Username" value="<?= $CAMS['username'] ?>" />
                </div>

                <div>
                    <label for="course_id">Course</label>
                </div>
                <div>
                    <input type="number" min="0" name="course_id" id="course_id" placeholder="CAMS Course ID" value="<?= $CAMS['course_id'] ?>" />
                </div>

                <div>
                    <label for="AM_id">AM id</label>
                </div>
                <div>
                    <input type="number" min="0" name="AM_id" id="AM_id" placeholder="Course AM ID" value="<?= $CAMS['AM_id'] ?>" />
                </div>

                <div>
                    <label for="PM_id">PM id</label>
                </div>
                <div>
                    <input type="number" min="0" name="PM_id" id="PM_id" placeholder="Course PM ID" value="<?= $CAMS['PM_id'] ?>" />
                </div>

                <div>
                    <label for="SAT_id">SAT id</label>
                </div>
                <div>
                    <input type="number" min="0" name="SAT_id" id="SAT_id" placeholder="Course Saturday ID" value="<?= $CAMS['SAT_id'] ?>" />
                </div>
            </div>
        </div>
    </main>
</body>

</html>
