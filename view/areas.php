        <nav class="areas">
            <?php $course = strtolower($course); ?>
            <?php if (hasMinAuth('student')) { ?>
            <div title="Files" class="<?= $area == 'file' ? 'active' : ''?>">
                <a href="<?= "{$MY_BASE}/{$course}/{$block}" ?>/file"><i class="fa-solid fa-hard-drive"></i></a>
            </div>
            <?php } ?>
            <?php if (hasMinAuth('student')) { ?>
            <div title="Statistics" class="<?= $area == 'stat' ? 'active' : ''?>">
                <a href="<?= "{$MY_BASE}/{$course}/{$block}" ?>/stat"><i class="fa-solid fa-chart-column"></i></a>
            </div>
            <?php } ?>
            <?php if (hasMinAuth('instructor')) { ?>
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
