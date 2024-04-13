<!DOCTYPE html>
<html>

<head>
    <title>Course Offerings</title>
    <meta charset="utf-8" />
    <meta name=viewport content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="res/css/lib/font-awesome-all.min.css">
    <link rel="stylesheet" href="res/css/common-1.2.css">
    <link rel="stylesheet" href="res/css/adm.css">
    <link rel="stylesheet" href="res/css/courses.css">
    <script>
        window.addEventListener('load', () => {
            const overlay = document.getElementById("overlay");
            document.getElementById('createCourse').addEventListener('click', () => {
                overlay.classList.add("visible");
            });
            document.getElementById('close-overlay').addEventListener('click', () => {
                overlay.classList.remove("visible");
            });
            overlay.addEventListener('click', (evt) => {
                if (evt.target == overlay) {
                    overlay.classList.remove('visible');
                }
            });
        });
    </script>
</head>

<body>
    <?php include("header.php"); ?>
    <main>
            <nav class="tools">
                <?php if ($type == "my"): ?>
                <a href="all">
                    <i class="fa-solid fa-globe" title="All Courses"></i>
                </a>
                <?php elseif ($type == "all"): ?>
                <a href="../videos/">
                    <i class="fa-solid fa-user" title="My Courses"></i>
                </a>
                <?php endif; ?>
				<?php if (hasMinAuth('admin')) : ?>
					<i id="createCourse" title="Create Course" class="fa-regular fa-square-plus"></i>
				<?php endif; ?>
            </nav>
        <div id="content">
            <?php if ($msg) : ?>
                <p class="error"><?= $msg ?></p>
            <?php endif; ?>

            <?php foreach ($offerings as $offering) : ?>
                <div class="offering">
                    <a href="<?= $offering["number"] . "/" . $offering["block"] . "/" ?>">
                        <span class="number"><?= strtoupper($offering["number"]) ?></span>
                        <span class="block"><?= $offering["block"] ?></span>
                        <span class="name"><?= $offering['name'] ?></span>
                        <span class="faculty"><?= $names[$offering['id']] ?></span>
                    </a>
                </div>
            <?php endforeach; ?>

        </div>
    </main>

    <?php if (hasMinAuth('admin')) : ?>
        <div id="overlay">
            <i id="close-overlay" class="fas fa-times-circle"></i>
            <div id="createCourseModal" class="modal">
                <h2>Create Course</h2>
                <p>Note: you generally want to clone an existing offering. Only use this create a new course with a new course number</p>
                <form method="POST" action="createCourse">
                    <div class="line">
                        <label>Course Number</label>
                        <input type="text" name="number" />
                    </div>
                    <div class="line">
                        <label>Course Name</label>
                        <input type="text" name="name" />
                    </div>
                    <hr />
                    <div class="line">
                        <label>First Block:</label>
                        <input name="block" />
                    </div>
                    <div class="line">
                        <label>Faculty</label>
                        <select name="fac_user_id">
                            <?php foreach($faculty as $fac ):?>
                                <option value="<?= $fac['id']?>"><?= $fac['firstname']?> <?= $fac['lastname']?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="line">
                        <label>Start Date:</label>
                        <input type="date" name="date" />
                    </div>
                    <div>
                        <label>Days per Lesson</label>
                        <input type="number" value="1" name="daysPerLesson" />
                        <label>Lessons per Part</label>
                        <input type="number" value="7" name="lessonsPerPart" />
                        <label>Parts</label>
                        <input type="number" value="4" name="lessonParts" />
                    </div>
                    <div class="submit">
                        <button>Submit</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</body>

</html>
