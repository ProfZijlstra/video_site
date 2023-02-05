<!DOCTYPE html>
<html>
    <head>
        <title><?= $block ?> Settings</title>
        <meta charset="utf-8" />
        <meta name=viewport content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="res/css/font-awesome-all.min.css">
        <link rel="stylesheet" href="res/css/common-1.1.css">
        <link rel="stylesheet" href="res/css/adm.css">
        <style>
            #content h2 {
                margin-bottom: 10px;
            }
            #settings {
                display: grid;
                grid-template-columns: 50px auto;
                row-gap: 10px;
                column-gap: 20px;
            }
            #settings input[type=number] {
                width: 40px;
            }
        </style>
        <script>
window.addEventListener("load", () => {
    function sendUpdate() {
        const id = document.getElementById("offering_id").value;
        const block = document.getElementById("block").value;
        const start = document.getElementById("start").value;
        const daysPerLesson = document.getElementById("daysPerLesson").value;
        const lessonsPerPart = document.getElementById("lessonsPerPart").value;
        const lessonParts = document.getElementById("lessonParts").value;
        const hasQuiz = document.getElementById("hasQuiz").checked;
        const hasLab = document.getElementById("hasLab").checked;

        const body = `id=${id}&block=${encodeURIComponent(block)}` 
                    + `&start=${encodeURIComponent(start)}`
                    + `&daysPerLesson=${daysPerLesson}`
                    + `&lessonsPerPart=${lessonsPerPart}`
                    + `&lessonParts=${lessonParts}&hasQuiz=`
                    + (hasQuiz ? 1 : 0) + `&hasLab=` + (hasLab ? 1 : 0);
        fetch("settings", {
            method : "POST",
            body : body,
            headers : {'Content-Type' : 'application/x-www-form-urlencoded'},
        });
    }
    const inputs = document.querySelectorAll("input");
    for (const input of inputs) {
        input.onchange = sendUpdate;
    }
});            
        </script>
    </head>
    <body>
        <?php include("header.php"); ?>
        <main>
            <nav class="areas">
                <div title="Back">
                    <a href="../<?= $block ?>/">
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>
                </div>
            </nav>

            <div id="content">
                <h2><?= strtoupper($course) ?> Offering</h2>
                <div id="settings">
                    <div>
                        Block
                    </div>
                    <div>
                        <input type="hidden" id="offering_id" value="<?= $offering['id'] ?>" />
                        <input id="block" value="<?= $block ?>" />
                    </div>
                    <div>
                        Start
                    </div>
                    <div>
                        <input type="date" id="start" value="<?= substr($offering['start'], 0, 10) ?>" />
                    </div>
                    <div>
                        <input type="number" id="daysPerLesson" value="<?= $offering['daysPerLesson']?>" />
                    </div>
                    <div>
                        Days Per Lesson
                    </div>
                    <div>
                        <input type="number" id="lessonsPerPart" value="<?= $offering['lessonsPerPart']?>" />
                    </div>
                    <div>
                        Lessons Per Part (columns in overview)
                    </div>
                    <div>
                        <input type="number" id="lessonParts" value="<?= $offering['lessonParts']?>" />
                    </div>
                    <div>
                        Lesson Parts (rows in overview)
                    </div>
                    <div>
                        <input type="checkbox" id="hasQuiz" <?= $offering['hasQuiz'] ? "checked" : "" ?> />
                    </div>
                    <div>
                        Quizzes Enabled for this Offering
                    </div>
                    <div>
                        <input type="checkbox" id="hasLab" <?= $offering['hasLab'] ? "checked" : "" ?> />
                    </div>
                    <div>
                        Labs Enabled for this Offering
                    </div>
                </div>
            </div>
        </main>
    </body>
</html>
