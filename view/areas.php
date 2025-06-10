        <nav class="areas">
            <?php $course = strtolower($course); ?>
            <div title="Lessons" class="<?= $area == 'course' ? 'active' : ''?>">
                <a href="<?= "{$MY_BASE}/{$course}/{$block}" ?>/"><i class="fa-solid fa-graduation-cap"></i></a>
            </div>
            <?php if (hasMinAuth('student') && $offering['hasQuiz']) { ?>
            <div title="Quizzes" class="<?= $area == 'quiz' ? 'active' : ''?>">
                <a href="<?= "{$MY_BASE}/{$course}/{$block}" ?>/quiz"><i class="fas fa-vial"></i></a>
            </div>
            <?php } ?>
            <?php if (hasMinAuth('student') && $offering['hasLab']) { ?>
            <div title="Labs" class="<?= $area == 'lab' ? 'active' : ''?>">
                <a href="<?= "{$MY_BASE}/{$course}/{$block}" ?>/lab"><i class="fas fa-flask"></i></a>
            </div>
            <?php } ?>
            <?php if (hasMinAuth('student')) { ?>
            <div title="Files" class="<?= $area == 'file' ? 'active' : ''?>">
                <a href="<?= "{$MY_BASE}/{$course}/{$block}" ?>/file"><i class="fa-solid fa-hard-drive"></i></a>
            </div>
            <?php } ?>
            <?php if (hasMinAuth('student')) { ?>
            <div title="Attendance" class="<?= $area == 'attendance' ? 'active' : ''?>">
                <a href="<?= "{$MY_BASE}/{$course}/{$block}" ?>/attendance"><i class="fas fa-user-check"></i></a>
            </div>
            <?php } ?>
            <?php if (hasMinAuth('instructor')) { ?>
            <div title="Enrolled" class="<?= $area == 'enrollment' ? 'active' : ''?>">
                <a href="<?= "{$MY_BASE}/{$course}/{$block}" ?>/enrolled"><i class="fas fa-user-friends"></i></a>
            </div>
            <?php } ?>
        </nav>
