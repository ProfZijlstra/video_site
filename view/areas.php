        <nav class="areas">
            <div title="Videos" class="<?= $area == "course" ? "active" : ""?>">
                <a href="../<?= $block ?>/"><i class="fas fa-film"></i></a>
            </div>
            <?php if ($offering['hasQuiz']) : ?>
            <div title="Quizzes" class="<?= $area == "quiz" ? "active" : ""?>">
                <a href="quiz"><i class="fas fa-vial"></i></a>
            </div>
            <?php endif; ?>
            <?php if ($offering['hasLab']) : ?>
            <div title="Labs" class="<?= $area == "lab" ? "active" : ""?>">
                <a href="lab"><i class="fas fa-flask"></i></a>
            </div>
            <?php endif; ?>
            <?php if (hasMinAuth('assistant')) : ?>
            <div title="Attendance" class="<?= $area == "attendance" ? "active" : ""?>">
                <a href="attendance"><i class="fas fa-user-check"></i></a>
            </div>
            <?php endif; ?>
            <?php if (hasMinAuth('instructor')) : ?>
            <div title="Enrolled" class="<?= $area == "enrollment" ? "active" : ""?>">
                <a href="enrolled"><i class="fas fa-user-friends"></i></a>
            </div>
            <?php endif; ?>
            <div title="Back to My Courses">
                <a href="../../">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
            </div>
        </nav>
