<!DOCTYPE html>
<html>

<head>
    <title><?= $block ?> Quizzes</title>
    <meta charset="utf-8" />
    <meta name=viewport content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="res/css/font-awesome-all.min.css">
    <link rel="stylesheet" href="res/css/common-1.1.css">
    <link rel="stylesheet" href="res/css/offering-1.1.css">
    <link rel="stylesheet" href="res/css/adm.css">
    <style>
        div#days {
            grid-template-columns: <?php for ($i = 0; $i < $offering['lessonsPerPart']; $i++): ?>auto <?php endfor; ?>;
            width: <?= 9 * $offering['lessonsPerPart'] ?>vw;
        }
        div#days div.data {
            cursor: default;
        }
        div.data a.invisible {
            color: gray;
        }
        div.data i.fa-plus-square {
            cursor: pointer;
            position: absolute;
            top: 10px;
            right: 10px;
        }
        div#days div.data a.edit {
            width: auto;
        }
        div.modal {
            width: 425px;
        }
        div.modal input[type="time"], div.modal input[type="date"] {
            width: 150px;
        }
        div.modal input#name {
            width: 310px;
        }

    </style>
    <script>
window.addEventListener("load", () => {    
    // hide overlay and any/all modal(s)
    function hide() {
        overlay.classList.remove("visible");
        const modals = document.querySelectorAll(".modal");
        for (const modal of modals) {
            modal.classList.add("hide");
        }
    }
    document.getElementById("close-overlay").onclick = hide;
    document.getElementById("overlay").onclick = function (evt) {
        if (evt.target == this) {
            hide();
        }
    };

    // show add quiz modal
    function showModal(evt) {
        const day_id = this.parentNode.dataset.day_id;
        const date = this.parentNode.dataset.date;
        document.getElementById('day_id').value = day_id;
        document.getElementById('startdate').value = date;
        document.getElementById('stopdate').value = date;
        overlay.classList.add("visible");
        document.getElementById("add_quiz_modal").classList.remove("hide");
        evt.stopPropagation();
        document.getElementById('name').focus();
    };
    const adds = document.querySelectorAll("div.data i.fa-plus-square");
    for (const add of adds) {
        add.onclick = showModal;
    }
});
    </script>
</head>

<body>
    <?php include("header.php"); ?>
    <main>
        <nav class="areas">
            <div title="Videos"><a href="../<?= $block ?>/"><i class="fas fa-film"></a></i></div>
            <div title="Quizzes" class="active"><i class="fas fa-vial"></i></div>
            <?php if ($offering['hasLab']): ?>
                <div title="Labs"><i class="fas fa-flask"></i></div>
            <?php endif; ?>
            <?php if ($_user_type === 'admin') : ?>
                <div title="Attendance"><a href="attendance"><i class="fas fa-user-check"></i></a></div>
                <div title="Enrolled"><a href="enrolled"><i class="fas fa-user-friends"></i></a></div>
            <?php endif; ?>
            <div title="Back to My Courses">
                <a href="../../">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
            </div>
        </nav>

        <div id="days">
            <?php for ($w = 1; $w <= $offering['lessonParts']; $w++): ?>
                <?php for ($d = 1; $d <= $offering['lessonsPerPart']; $d++): ?>
                    <?php $date = $start + ($w - 1)*60*60*24*$offering['daysPerLesson']*$offering['lessonsPerPart'] + ($d - 1)*60*60*24*$offering["daysPerLesson"]; ?>

                    <div class="data <?= $w == 1 ? "w1" : "" ?> <?= $d == 1 ? "d1 " : "" ?><?= $date < $now ? "done" : "" ?> <?= date("z", $date) == date("z", $now)? "curr" : ""?>"
                        id="<?= "W{$w}D{$d}" ?>"
                        data-day="<?= "W{$w}D{$d}" ?>" 
                        data-day_id="<?= $days["W{$w}D{$d}"]["id"] ?>"
                        data-date="<?= date("Y-m-d", $date) ?>">

                        <?php if ($_user_type == "admin"): ?>
                            <i title="Add Quiz" class="far fa-plus-square"></i>
                        <?php endif; ?>

                        <?php foreach ($days["W{$w}D{$d}"]['quizzes'] as $quiz): ?>
                            <div>
                            <a href="<?= "quiz/". $quiz['id'] ?>" 
                                class="<?= $quiz['visible'] ? 'visible' : 'invisible' ?>"
                                title="<?= $quiz['visible'] ? 'visible' : 'invisible' ?>">
                                <?= $quiz['name'] ?>
                            </a>
                            <?php if ($_user_type == "admin"): ?>
                                <a href="<?= "quiz/". $quiz['id'] . "/grade" ?>">
                                    <i title="Grade Quiz" class="fa-solid fa-magnifying-glass"></i>
                                </a>
                                <a class="edit" href="<?= "quiz/". $quiz['id'] . "/edit" ?>">
                                    <i title="Edit Quiz" class="fa-regular fa-pen-to-square"></i>
                                </a>
                            <?php endif; ?>
                            </div>
                        <?php endforeach; ?>

                        <time>
                            <?= date("M j Y", $date); ?>
                        </time>
                    </div>
                <?php endfor ?>
            <?php endfor ?>
        </div>
    </main>

    <div id="overlay">
        <i id="close-overlay" class="fas fa-times-circle"></i>
        <div id="add_quiz_modal" class="modal hide">
            <h3>Add Quiz</h3>
            <form action="quiz" method="post" id="add_form">
                <input type="hidden" name="day_id" value="<?= $day_id ?>" id="day_id"/>
                <div>
                    <label>From:</label> 
                    <input id="startdate" type="date" name="startdate" value="<?= date("m/d/Y", $date)?>" /> 
                    <input type="time" name="starttime" value="10:00" />
                </div>
                <div>
                    <label>To:</label> 
                    <input id="stopdate" type="date" name="stopdate" value="<?= date("m/d/Y", $date)?>" /> 
                    <input type="time" name="stoptime" value="10:30" />
                </div>
                <div>
                    <label>Name:</label>
                    <input type="text" name="name" id="name" placeholder="name" />
                </div>            
                <div class="btn"><button>Add Quiz</button></div>
            </form>
        </div>
    </div>

</body>

</html>