<!DOCTYPE html>
<html>
    <head>
        <title><?= $course ?> Videos</title>
        <meta charset="utf-8" />
        <meta name=viewport content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="res/css/font-awesome-all.min.css">
        <link rel="stylesheet" href="res/css/common-1.1.css">
		<link rel="stylesheet" href="res/css/offering-1.1.css">
        <script src="res/js/offering.js"></script>
        <?php if ($_user_type === 'admin') : ?>
            <link rel="stylesheet" href="res/css/adm.css">
            <script src="https://unpkg.com/react@17/umd/react.production.min.js" crossorigin></script>
            <script src="https://unpkg.com/react-dom@17/umd/react-dom.production.min.js" crossorigin></script>
            <script src="res/js/info.js"></script>
            <script src="res/js/adm_offering.js"></script>
        <?php endif; ?>
        <style>
        div#days {
            grid-template-columns: <?php for ($i = 0; $i < $offering['lessonsPerPart']; $i++): ?>auto <?php endfor; ?>;
            width: <?= 9 * $offering['lessonsPerPart'] ?>vw;
        }
        </style>
    </head>
    <body>
        <?php include("header.php"); ?>
        <main>
            <?php if ($_user_type === 'admin') : ?>
            <nav class="tools">
                <a href="settings">
                    <i title="Offering Settings" class="fa-solid fa-gear"></i>
                </a>
                <i title="View Info" id="info-btn" class="fas fa-info-circle"></i>
                <i title="Edit Calendar" id="edit" class="far fa-edit"></i>
                <i title="Clone Offering" id="clone" class="far fa-copy"></i>
                <i title="Delete Offering" id="delete" class="far fa-trash-alt"></i>
            </nav>
            <?php endif; ?>

            <nav class="areas">
                <div title="Videos" class="active"><i class="fas fa-film"></i></div>
                <?php if ($offering['hasQuiz']): ?>
                <div title="Quizzes"><a href="quiz"><i class="fas fa-vial"></i></a></div>
                <?php endif; ?>
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
                            data-text="<?= $days["W{$w}D{$d}"]["desc"] ?>">
                        <div class="info"></div>
                        <a href="W<?= $w ?>D<?= $d ?>/">
                            <span class="day"><?= "W{$w}D{$d}" ?></span>
                            <span class="text"><?= $days["W{$w}D{$d}"]["desc"] ?></span>
                        </a>
                        <time><?= date("M j Y", $date);?></time>
                    </div>

                    <?php endfor ?>
                <?php endfor ?>
            </div>
			<div id="total"><div class="info"></div></div>
        </main>
        <?php if ($_user_type === 'admin') : ?>
            <div id="overlay">
                <i id="close-overlay" class="fas fa-times-circle"></i>
                <div id="clone_modal" class="modal">
                    <h2>Clone Offering</h2>
                    <form method="POST" action="clone">
                        <input type="hidden" name="offering_id" value=<?= $offering['id'] ?> />
                        <div class="line">
                            <label>New Block:</label>
                            <input name="block" />
                        </div>
                        <div class="line">
                            <label>Faculty</label>
                            <select name="fac_user_id" id="fac_user_id">
                                <?php foreach($faculty as $user): ?>
                                    <option value="<?= $user['id'] ?>"><?= $user['firstname'] . " " .  $user['lastname'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="line">
                            <label>Start Date:</label>
                            <input type="date" name="start" />
                        </div>
                        <div>
                            <label>Days per Lesson</label>
                            <input type="number" name="daysPerLesson" value="<?= $offering['daysPerLesson'] ?>" />
                            <label>Lessons per Part (cols)</label>
                            <input type="number" name="lessonsPerPart" value="<?= $offering['lessonsPerPart']?>" />
                            <label>Parts (rows)</label>
                            <input type="number" name="lessonParts" value="<?= $offering['lessonParts'] ?>"  />
                        </div>
                        <div class="indent">
                            <label class="checkbox">
                                <input type="checkbox" name="hasQuiz" value="1" <?= $offering['hasQuiz'] ? "checked" : ""?> />
                                Has Quiz
                            </label>
                            <label class="checkbox">
                                <input type="checkbox" name="hasLab" value="1" <?= $offering['hasLab'] ? "checked" : ""?> />
                                Has Lab
                            </label>
                        </div>
                        <div class="submit">
                            <button>Submit</button>
                        </div>
                    </form>
                </div>
                <div id="delete_modal" class="modal">
                    <h2>Delete Offering</h2>
                    <p>
                        Please confirm that you want to delete this offering.
                    </p>
                    <form method="POST" action="delete">
                        <input type="hidden" name="offering_id" value=<?= $offering['id'] ?> />
                        <div class="submit">
                            <button type="button" id="cancel_delete">Cancel</button>
                            <button type="submit" id="ok_delete">OK</button>
                        </div>
                    </form>
                </div>

            </div>
        <?php endif; ?>

    </body>
</html>
